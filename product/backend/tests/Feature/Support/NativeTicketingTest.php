<?php

namespace Tests\Feature\Support;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Support\SupportCustomer;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NativeTicketingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $supportUser;
    private Product $supportProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Organization
        $this->organization = Organization::create([
            'name' => 'Acme Cloud IT',
            'status' => 'active',
        ]);

        // 2. Create Product and attach to Organization
        $this->supportProduct = Product::create([
            'name' => 'Aprilo Support',
            'slug' => 'aprilo-support',
            'category' => 'support_saas',
            'product_type' => 'subscription',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->organization->products()->attach($this->supportProduct->id, [
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        // 3. Permissions and Role
        $permView = Permission::create([
            'slug' => 'support.tickets.view',
            'name' => 'View Tickets',
            'category' => 'support',
        ]);

        $permManage = Permission::create([
            'slug' => 'support.tickets.manage',
            'name' => 'Manage Tickets',
            'category' => 'support',
        ]);

        $role = Role::create([
            'organization_id' => $this->organization->id,
            'name' => 'Support Engineer',
            'slug' => 'support_engineer',
            'is_system' => false,
        ]);
        $role->permissions()->attach([$permView->id, $permManage->id]);

        // 4. Support User
        $this->supportUser = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $role->id,
            'name' => 'Sara Support',
            'email' => 'sara@acmecloud.com',
            'password' => bcrypt('secret123'),
            'role' => 'support_engineer',
            'status' => 'active',
        ]);
    }

    public function test_can_create_ticket_with_atomic_numbering_and_lifecycle_event(): void
    {
        Sanctum::actingAs($this->supportUser);

        $response = $this->postJson('/api/support/tickets', [
            'subject' => 'Cannot connect to production database',
            'description' => 'Connection timeout on port 5432 after cluster upgrade.',
            'priority' => 'urgent',
            'channel' => 'portal',
            'customer_name' => 'DevOps Lead',
            'customer_email' => 'devops@client.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('ticket.subject', 'Cannot connect to production database')
            ->assertJsonPath('ticket.priority', 'urgent')
            ->assertJsonPath('ticket.status', 'new')
            ->assertJsonPath('ticket.ticket_number', 'TIC-1001');

        $ticket = SupportTicket::first();
        $this->assertNotNull($ticket);
        $this->assertEquals($this->organization->id, $ticket->organization_id);
        $this->assertEquals('TIC-1001', $ticket->ticket_number);

        // Verify customer created
        $customer = SupportCustomer::where('email', 'devops@client.com')->first();
        $this->assertNotNull($customer);
        $this->assertEquals($ticket->customer_id, $customer->id);

        // Verify created lifecycle event
        $this->assertDatabaseHas('support_ticket_events', [
            'ticket_id' => $ticket->id,
            'event_type' => 'created',
            'actor_id' => $this->supportUser->id,
        ]);
    }

    public function test_subsequent_tickets_increment_atomic_number_per_organization(): void
    {
        Sanctum::actingAs($this->supportUser);

        $res1 = $this->postJson('/api/support/tickets', [
            'subject' => 'First issue',
            'description' => 'First details',
            'customer_name' => 'John Doe',
        ]);
        $res1->assertStatus(201)->assertJsonPath('ticket.ticket_number', 'TIC-1001');

        $res2 = $this->postJson('/api/support/tickets', [
            'subject' => 'Second issue',
            'description' => 'Second details',
            'customer_name' => 'Jane Doe',
        ]);
        $res2->assertStatus(201)->assertJsonPath('ticket.ticket_number', 'TIC-1002');
    }

    public function test_state_transitions_follow_strict_allowed_rules(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'SSL certificate renewal failure',
            'description' => 'Certbot threw an error.',
            'status' => 'new',
            'priority' => 'high',
        ]);

        // Invalid jump from 'new' to 'resolved' directly must fail with 422
        $invalidTransition = $this->postJson("/api/support/tickets/{$ticket->id}/transition", [
            'status' => 'resolved',
        ]);
        $invalidTransition->assertStatus(422)
            ->assertJsonPath('success', false);

        // Valid transition from 'new' to 'open'
        $openTransition = $this->postJson("/api/support/tickets/{$ticket->id}/transition", [
            'status' => 'open',
            'note' => 'Triaged by agent',
        ]);
        $openTransition->assertStatus(200)->assertJsonPath('ticket.status', 'open');

        // Transition from 'open' to 'in_progress'
        $inProgress = $this->postJson("/api/support/tickets/{$ticket->id}/transition", [
            'status' => 'in_progress',
        ]);
        $inProgress->assertStatus(200)->assertJsonPath('ticket.status', 'in_progress');

        // Transition from 'in_progress' to 'resolved' records resolved_at
        $resolved = $this->postJson("/api/support/tickets/{$ticket->id}/transition", [
            'status' => 'resolved',
            'note' => 'Certificate replaced successfully',
        ]);
        $resolved->assertStatus(200)->assertJsonPath('ticket.status', 'resolved');

        $ticket->refresh();
        $this->assertNotNull($ticket->resolved_at);

        // Reopen ticket from 'resolved' back to 'open'
        $reopened = $this->postJson("/api/support/tickets/{$ticket->id}/transition", [
            'status' => 'open',
            'note' => 'Customer reported issue recurred',
        ]);
        $reopened->assertStatus(200)->assertJsonPath('ticket.status', 'open');

        $ticket->refresh();
        $this->assertNotNull($ticket->reopened_at);
        $this->assertNull($ticket->resolved_at);
    }

    public function test_ticket_assignment_records_audit_event_and_updates_status(): void
    {
        Sanctum::actingAs($this->supportUser);

        $agent2 = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Alex Engineer',
            'email' => 'alex@acmecloud.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Cluster disk full',
            'description' => 'Usage reached 98%.',
            'status' => 'new',
            'priority' => 'urgent',
        ]);

        $assignRes = $this->postJson("/api/support/tickets/{$ticket->id}/assign", [
            'assigned_to' => $agent2->id,
            'department' => 'DevOps',
            'note' => 'Assigning urgent disk escalation to Alex',
        ]);

        $assignRes->assertStatus(200)
            ->assertJsonPath('ticket.assigned_to', $agent2->id)
            ->assertJsonPath('ticket.department', 'DevOps')
            ->assertJsonPath('ticket.status', 'open'); // Automatically opened on assignment

        $this->assertDatabaseHas('support_ticket_events', [
            'ticket_id' => $ticket->id,
            'event_type' => 'assigned',
            'actor_id' => $this->supportUser->id,
        ]);
    }

    public function test_internal_notes_and_customer_comments_separation(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Billing inquiry',
            'description' => 'Customer questioned unexpected charge.',
            'status' => 'open',
        ]);

        // 1. Post Internal Note
        $noteRes = $this->postJson("/api/support/tickets/{$ticket->id}/comments", [
            'body' => 'Checked Stripe logs: charge is legitimate recurring subscription charge.',
            'is_internal' => true,
        ]);

        $noteRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('comment.is_internal', true);

        $this->assertDatabaseHas('support_ticket_events', [
            'ticket_id' => $ticket->id,
            'event_type' => 'note_added',
        ]);

        // 2. Post Customer Reply
        $replyRes = $this->postJson("/api/support/tickets/{$ticket->id}/comments", [
            'body' => 'Hello! We reviewed your account and confirmed this is your standard monthly renewal invoice.',
            'is_internal' => false,
        ]);

        $replyRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('comment.is_internal', false);

        $ticket->refresh();
        $this->assertNotNull($ticket->first_response_at);
        $this->assertEquals('pending_customer', $ticket->status);

        // 3. Verify public view filters out internal note
        $publicCommentsRes = $this->getJson("/api/support/tickets/{$ticket->id}/comments?include_internal=0");
        $publicCommentsRes->assertStatus(200)
            ->assertJsonCount(1, 'comments')
            ->assertJsonPath('comments.0.is_internal', false);
    }

    public function test_cross_tenant_isolation_prevents_unauthorized_access(): void
    {
        // Create second tenant
        $otherOrg = Organization::create([
            'name' => 'Competitor Corp',
            'status' => 'active',
        ]);

        $otherTicket = SupportTicket::create([
            'organization_id' => $otherOrg->id,
            'ticket_number' => 'TIC-9999',
            'ticket_sequence' => 9999,
            'subject' => 'Private ticket from competitor',
            'description' => 'Secret information',
            'status' => 'open',
        ]);

        // Acme user attempts to view Competitor's ticket
        Sanctum::actingAs($this->supportUser);

        $response = $this->getJson("/api/support/tickets/{$otherTicket->id}");
        $response->assertStatus(403);

        // Acme user attempts to transition Competitor's ticket
        $transitionRes = $this->postJson("/api/support/tickets/{$otherTicket->id}/transition", [
            'status' => 'closed',
        ]);
        $transitionRes->assertStatus(403);
    }

    public function test_shared_support_inbox_queue_summary(): void
    {
        Sanctum::actingAs($this->supportUser);

        // Create tickets in different states
        SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Unassigned Urgent',
            'description' => 'Down alert',
            'status' => 'new',
            'priority' => 'urgent',
            'assigned_to' => null,
        ]);

        SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1002',
            'ticket_sequence' => 1002,
            'subject' => 'Assigned to Sara',
            'description' => 'Bug report',
            'status' => 'in_progress',
            'priority' => 'normal',
            'assigned_to' => $this->supportUser->id,
        ]);

        SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1003',
            'ticket_sequence' => 1003,
            'subject' => 'Resolved issue',
            'description' => 'Resolved item',
            'status' => 'resolved',
            'priority' => 'low',
        ]);

        $summaryRes = $this->getJson('/api/support/inbox/summary');

        $summaryRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.all_open', 2)
            ->assertJsonPath('summary.unassigned', 1)
            ->assertJsonPath('summary.assigned_to_me', 1)
            ->assertJsonPath('summary.urgent', 1)
            ->assertJsonPath('summary.resolved', 1);
    }

    public function test_can_view_and_update_support_sla_configuration(): void
    {
        Sanctum::actingAs($this->supportUser);

        // Grant SLA manage permission
        $permSla = Permission::firstOrCreate(['slug' => 'support.sla.manage'], [
            'name' => 'Manage SLA Tracking',
            'category' => 'support',
        ]);
        $this->supportUser->roleModel->permissions()->attach($permSla->id);

        // 1. Get initial configuration
        $configRes = $this->getJson('/api/support/config');
        $configRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('sla_policies.urgent.response_time_minutes', 60);

        // 2. Update SLA policy and add category
        $updateRes = $this->postJson('/api/support/config', [
            'sla_policies' => [
                [
                    'priority' => 'urgent',
                    'response_time_minutes' => 30, // Expedited to 30 mins
                    'resolution_time_minutes' => 120, // 2 hours
                ],
            ],
            'categories' => [
                [
                    'name' => 'Database Outage',
                    'description' => 'Issues related to replica lag or connectivity.',
                ],
            ],
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. Verify updated config
        $checkRes = $this->getJson('/api/support/config');
        $checkRes->assertStatus(200)
            ->assertJsonPath('sla_policies.urgent.response_time_minutes', 30)
            ->assertJsonPath('sla_policies.urgent.resolution_time_minutes', 120);

        $this->assertDatabaseHas('support_ticket_categories', [
            'organization_id' => $this->organization->id,
            'name' => 'Database Outage',
            'slug' => 'database-outage',
        ]);
    }
}

