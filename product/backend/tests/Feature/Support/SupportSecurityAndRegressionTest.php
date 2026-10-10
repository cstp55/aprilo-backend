<?php

namespace Tests\Feature\Support;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Support\SupportAiDraft;
use App\Models\Support\SupportCustomer;
use App\Models\Support\SupportOutboxEvent;
use App\Models\Support\SupportSlaPolicy;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketCategory;
use App\Models\Support\SupportTicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportSecurityAndRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $tenantA;
    private Organization $tenantB;
    private User $userTenantA;
    private User $userTenantB;
    private Product $supportProduct;
    private SupportTicketCategory $categoryA;
    private SupportTicketCategory $categoryB;
    private string $testSecret = 'secret_test_key_123';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.whatsapp.app_secret' => $this->testSecret]);

        // 1. Create Support Product
        $this->supportProduct = Product::create([
            'name' => 'Aprilo Support',
            'slug' => 'aprilo-support',
            'category' => 'support_saas',
            'product_type' => 'subscription',
            'status' => 'active',
            'is_active' => true,
        ]);

        // 2. Setup Tenant A
        $this->tenantA = Organization::create([
            'name' => 'Tenant Alpha Corp',
            'status' => 'active',
        ]);
        $this->tenantA->products()->attach($this->supportProduct->id, [
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);
        $settingsA = $this->tenantA->settings ?? $this->tenantA->settings()->firstOrCreate([]);
        $settingsA->update([
            'whatsapp_phone_number_id' => 'PN_ALPHA_123',
            'whatsapp_connected' => true,
        ]);

        // 3. Setup Tenant B
        $this->tenantB = Organization::create([
            'name' => 'Tenant Beta Ltd',
            'status' => 'active',
        ]);
        $this->tenantB->products()->attach($this->supportProduct->id, [
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        // 4. Permissions
        $permView = Permission::firstOrCreate(['slug' => 'support.tickets.view'], ['name' => 'View Tickets', 'category' => 'support']);
        $permManage = Permission::firstOrCreate(['slug' => 'support.tickets.manage'], ['name' => 'Manage Tickets', 'category' => 'support']);
        $permDraft = Permission::firstOrCreate(['slug' => 'support.drafts.approve'], ['name' => 'Approve Drafts', 'category' => 'support']);

        $roleA = Role::create([
            'organization_id' => $this->tenantA->id,
            'name' => 'Support Agent A',
            'slug' => 'support-agent-a',
        ]);
        $roleA->permissions()->attach([$permView->id, $permManage->id, $permDraft->id]);

        $roleB = Role::create([
            'organization_id' => $this->tenantB->id,
            'name' => 'Support Agent B',
            'slug' => 'support-agent-b',
        ]);
        $roleB->permissions()->attach([$permView->id, $permManage->id, $permDraft->id]);

        $this->userTenantA = User::create([
            'organization_id' => $this->tenantA->id,
            'role_id' => $roleA->id,
            'name' => 'Agent Alpha',
            'email' => 'agent@alpha.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->userTenantB = User::create([
            'organization_id' => $this->tenantB->id,
            'role_id' => $roleB->id,
            'name' => 'Agent Beta',
            'email' => 'agent@beta.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->categoryA = SupportTicketCategory::create([
            'organization_id' => $this->tenantA->id,
            'name' => 'Alpha Billing',
            'slug' => 'alpha-billing',
        ]);

        $this->categoryB = SupportTicketCategory::create([
            'organization_id' => $this->tenantB->id,
            'name' => 'Beta Billing',
            'slug' => 'beta-billing',
        ]);
    }

    public function test_cross_tenant_isolation_prevents_viewing_other_organization_tickets(): void
    {
        $customerB = SupportCustomer::create([
            'organization_id' => $this->tenantB->id,
            'name' => 'Beta Customer',
            'email' => 'beta@customer.com',
        ]);

        $ticketB = SupportTicket::create([
            'organization_id' => $this->tenantB->id,
            'customer_id' => $customerB->id,
            'ticket_number' => 'TIC-B001',
            'subject' => 'Beta Secret Ticket',
            'description' => 'Beta secret description',
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'website',
        ]);

        Sanctum::actingAs($this->userTenantA);

        $response = $this->getJson("/api/support/tickets/{$ticketB->id}");
        $response->assertStatus(403);
    }

    public function test_cross_tenant_isolation_prevents_adding_comments_to_other_organization_tickets(): void
    {
        $customerB = SupportCustomer::create([
            'organization_id' => $this->tenantB->id,
            'name' => 'Beta Customer',
            'email' => 'beta@customer.com',
        ]);

        $ticketB = SupportTicket::create([
            'organization_id' => $this->tenantB->id,
            'customer_id' => $customerB->id,
            'ticket_number' => 'TIC-B002',
            'subject' => 'Beta Ticket',
            'description' => 'Beta ticket content',
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'website',
        ]);

        Sanctum::actingAs($this->userTenantA);

        $response = $this->postJson("/api/support/tickets/{$ticketB->id}/comments", [
            'body' => 'Illicit intrusion attempt',
            'is_internal' => true,
        ]);
        $response->assertStatus(403);
    }

    public function test_cross_tenant_isolation_prevents_approving_other_organization_drafts(): void
    {
        $customerB = SupportCustomer::create([
            'organization_id' => $this->tenantB->id,
            'name' => 'Beta Customer',
            'email' => 'beta@customer.com',
        ]);

        $ticketB = SupportTicket::create([
            'organization_id' => $this->tenantB->id,
            'customer_id' => $customerB->id,
            'ticket_number' => 'TIC-B003',
            'subject' => 'Beta Draft Ticket',
            'description' => 'Beta draft description',
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'website',
        ]);

        $draftB = SupportAiDraft::create([
            'organization_id' => $this->tenantB->id,
            'ticket_id' => $ticketB->id,
            'proposed_reply' => 'Confidential resolution instructions for Tenant B.',
            'confidence' => 0.95,
            'status' => 'pending_review',
        ]);

        Sanctum::actingAs($this->userTenantA);

        $response = $this->postJson("/api/support/drafts/{$draftB->id}/approve");
        $response->assertStatus(403);
        $this->assertEquals('pending_review', $draftB->fresh()->status);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/support/tickets');
        $response->assertStatus(401);

        $responseDraft = $this->postJson('/api/support/drafts/1/approve');
        $responseDraft->assertStatus(401);
    }

    public function test_mandatory_safety_invariant_preventing_unapproved_ai_dispatch(): void
    {
        $customerA = SupportCustomer::create([
            'organization_id' => $this->tenantA->id,
            'name' => 'Alpha Customer',
            'email' => 'client@alpha.com',
        ]);

        $ticketA = SupportTicket::create([
            'organization_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'ticket_number' => 'TIC-A001',
            'subject' => 'Safety Invariant Test',
            'description' => 'Testing safety invariant',
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'website',
        ]);

        Sanctum::actingAs($this->userTenantA);

        $genResponse = $this->postJson("/api/support/tickets/{$ticketA->id}/drafts/generate");
        $genResponse->assertStatus(201);

        // Verification: The draft is created with 'pending_review' status
        $this->assertDatabaseHas('support_ai_drafts', [
            'ticket_id' => $ticketA->id,
            'status' => 'pending_review',
        ]);

        // Verification: ZERO customer-facing comments should exist
        $this->assertEquals(0, SupportTicketComment::where('ticket_id', $ticketA->id)->where('is_internal', false)->count());

        // Verification: ZERO outbox events should be queued
        $this->assertEquals(0, SupportOutboxEvent::where('ticket_id', $ticketA->id)->count());
    }

    public function test_internal_notes_are_never_dispatched_via_channel_adapters(): void
    {
        $customerA = SupportCustomer::create([
            'organization_id' => $this->tenantA->id,
            'name' => 'Confidential Customer',
            'email' => 'client@alpha.com',
        ]);

        $ticketA = SupportTicket::create([
            'organization_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'ticket_number' => 'TIC-A002',
            'subject' => 'Internal Note Security',
            'description' => 'Internal investigation note',
            'status' => 'open',
            'priority' => 'normal',
            'channel' => 'email',
        ]);

        Sanctum::actingAs($this->userTenantA);

        $response = $this->postJson("/api/support/tickets/{$ticketA->id}/comments", [
            'body' => 'Sensitive investigation: Customer account is flagged for review.',
            'is_internal' => true,
        ]);
        $response->assertStatus(201);

        // Assert comment was saved as internal
        $this->assertDatabaseHas('support_ticket_comments', [
            'ticket_id' => $ticketA->id,
            'is_internal' => true,
        ]);

        // Assert NO outbox events were generated
        $this->assertEquals(0, SupportOutboxEvent::where('ticket_id', $ticketA->id)->count());
    }

    public function test_end_to_end_multichannel_ticket_lifecycle_with_rag_and_human_approval(): void
    {
        // 1. Inbound webhook from customer via WhatsApp with HMAC signature
        $waPayload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'WABA_001',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => '15551234567',
                                    'phone_number_id' => 'PN_ALPHA_123',
                                ],
                                'contacts' => [
                                    [
                                        'profile' => ['name' => 'Alice TechLead'],
                                        'wa_id' => '19998887777',
                                    ],
                                ],
                                'messages' => [
                                    [
                                        'from' => '19998887777',
                                        'id' => 'wamid.e2e_test_msg_001',
                                        'timestamp' => '1710000000',
                                        'type' => 'text',
                                        'text' => [
                                            'body' => 'Hello, how can I configure webhooks for my organization?',
                                        ],
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($waPayload);
        $signature = 'sha256=' . hash_hmac('sha256', $rawBody, $this->testSecret);

        $webhookResp = $this->call(
            'POST',
            '/api/webhooks/support/whatsapp',
            [],
            [],
            [],
            ['HTTP_X-Hub-Signature-256' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $rawBody
        );
        $webhookResp->assertStatus(200);

        // Verify customer and ticket created
        $customer = SupportCustomer::where('phone', '19998887777')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Alice TechLead', $customer->name);

        $ticket = SupportTicket::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('new', $ticket->status);
        $this->assertEquals('whatsapp', $ticket->channel);

        // 2. Add Knowledge chunk in Tenant A's organization
        $source = KnowledgeSource::create([
            'organization_id' => $customer->organization_id,
            'title' => 'Webhooks Documentation',
            'source_type' => 'manual',
            'status' => 'indexed',
            'uploaded_by' => $this->userTenantA->id,
        ]);
        KnowledgeChunk::create([
            'source_id' => $source->id,
            'organization_id' => $customer->organization_id,
            'chunk_index' => 1,
            'chunk_text' => 'To configure webhooks, navigate to Settings > Integrations and supply your endpoint URL.',
            'section_title' => 'Webhook Setup',
        ]);

        // 3. AI Draft Generation
        Sanctum::actingAs($this->userTenantA);

        $draftResp = $this->postJson("/api/support/tickets/{$ticket->id}/drafts/generate");
        $draftResp->assertStatus(201);
        $draftId = $draftResp->json('draft.id');

        // 4. Human Agent reviews and edits draft via PATCH /api/support/drafts/{id}/edit
        $editResp = $this->patchJson("/api/support/drafts/{$draftId}/edit", [
            'edited_reply' => 'Hello Alice, you can configure webhooks by going to Settings > Integrations. Let us know if you need SSL assistance!',
        ]);
        $editResp->assertStatus(200);

        // 5. Human Agent approves draft
        $approveResp = $this->postJson("/api/support/drafts/{$draftId}/approve");
        $approveResp->assertStatus(200);

        // 6. Verify Customer-Visible Comment Dispatched
        $approvedDraft = SupportAiDraft::find($draftId);
        $this->assertEquals('approved', $approvedDraft->status);
        $this->assertNotNull($approvedDraft->dispatched_comment_id);

        $comment = SupportTicketComment::find($approvedDraft->dispatched_comment_id);
        $this->assertFalse($comment->is_internal);
        $this->assertStringContainsString('Hello Alice', $comment->body);

        // 7. Verify Outbox Event Queued
        $outbox = SupportOutboxEvent::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($outbox);
        $this->assertEquals('whatsapp', $outbox->channel);
        $this->assertEquals('19998887777', $outbox->recipient);
    }

    public function test_admin_support_settings_page_and_sla_update(): void
    {
        $adminUser = User::create([
            'organization_id' => $this->tenantA->id,
            'name' => 'Admin Boss',
            'email' => 'admin@alpha.com',
            'password' => bcrypt('secret123'),
            'role' => 'owner',
        ]);

        $this->actingAs($adminUser);

        // GET admin support settings page
        $pageResp = $this->get('/admin/support/settings');
        $pageResp->assertStatus(200);
        $pageResp->assertViewHas('organization');
        $pageResp->assertViewHas('slaPolicies');

        // POST update support SLA policies
        $postResp = $this->post('/admin/support/settings', [
            'urgent_response_time' => 10,
            'urgent_resolution_time' => 30,
            'high_response_time' => 20,
            'high_resolution_time' => 60,
            'normal_response_time' => 45,
            'normal_resolution_time' => 180,
            'low_response_time' => 60,
            'low_resolution_time' => 360,
        ]);

        $postResp->assertRedirect();
        $this->assertDatabaseHas('support_ticket_sla_policies', [
            'organization_id' => $this->tenantA->id,
            'priority' => 'urgent',
            'response_time_minutes' => 10,
            'resolution_time_minutes' => 30,
        ]);
        $this->assertDatabaseHas('support_ticket_sla_policies', [
            'organization_id' => $this->tenantA->id,
            'priority' => 'high',
            'response_time_minutes' => 20,
            'resolution_time_minutes' => 60,
        ]);
    }
}
