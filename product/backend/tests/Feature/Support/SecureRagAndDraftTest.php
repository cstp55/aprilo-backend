<?php

namespace Tests\Feature\Support;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Support\SupportAiDraft;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecureRagAndDraftTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $supportUser;
    private Product $supportProduct;
    private KnowledgeSource $knowledgeSource;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Organization
        $this->organization = Organization::create([
            'name' => 'Nexus Cloud Systems',
            'status' => 'active',
        ]);

        // 2. Create Aprilo Support Product and attach to Organization
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

        // 3. Permissions & Role
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
            'name' => 'Liam Lead Support',
            'email' => 'liam@nexuscloud.io',
            'password' => bcrypt('password123'),
            'role' => 'support_engineer',
            'status' => 'active',
        ]);

        // 5. Seed Knowledge Base Source and Chunks for RAG retrieval
        $this->knowledgeSource = KnowledgeSource::create([
            'organization_id' => $this->organization->id,
            'title' => 'Nexus API Troubleshooting Runbook',
            'source_type' => 'runbook',
            'status' => 'indexed',
            'access_scope' => 'all_employees',
            'uploaded_by' => $this->supportUser->id,
        ]);

        KnowledgeChunk::create([
            'organization_id' => $this->organization->id,
            'source_id' => $this->knowledgeSource->id,
            'chunk_index' => 1,
            'chunk_text' => 'When encountering HTTP 504 Gateway Timeout during webhook dispatch, verify that the worker node security group permits TCP egress on port 443 and that DNS TTL is set below 60 seconds.',
            'section_title' => '504 Gateway Timeout Resolution',
        ]);
    }

    public function test_ai_draft_is_proposed_with_rag_citations_and_saved_as_pending_review(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Receiving HTTP 504 Gateway Timeout on webhooks',
            'description' => 'Our webhook dispatch pipeline reports frequent 504 Gateway Timeout errors.',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $response = $this->postJson("/api/support/tickets/{$ticket->id}/drafts/generate");

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('draft.status', 'pending_review')
            ->assertJsonPath('draft.is_stale', false);

        $draft = SupportAiDraft::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($draft);
        $this->assertEquals('pending_review', $draft->status);
        $this->assertNotEmpty($draft->proposed_reply);

        // Verify citations persisted
        $this->assertGreaterThanOrEqual(1, $draft->sources()->count());
        $firstSource = $draft->sources()->first();
        $this->assertEquals($this->knowledgeSource->id, $firstSource->source_id);
        $this->assertStringContainsString('504 Gateway Timeout', $firstSource->citation_excerpt);
    }

    public function test_mandatory_safety_invariant_draft_is_never_sent_without_human_approval(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Memory leak in worker daemon',
            'description' => 'Node memory reaches 99% after 12 hours.',
            'status' => 'open',
        ]);

        // Generate draft
        $this->postJson("/api/support/tickets/{$ticket->id}/drafts/generate");

        // Safety Invariant: Check that NO customer-visible comment was created automatically
        $publicComments = SupportTicketComment::where('ticket_id', $ticket->id)
            ->where('is_internal', false)
            ->count();

        $this->assertEquals(0, $publicComments, 'Safety Invariant violated: AI Draft was published without human approval!');
    }

    public function test_human_can_edit_and_approve_draft_which_dispatches_customer_comment(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Webhook timeout issue',
            'description' => 'Webhooks are timing out with 504 errors.',
            'status' => 'open',
        ]);

        $draft = SupportAiDraft::create([
            'organization_id' => $this->organization->id,
            'ticket_id' => $ticket->id,
            'proposed_reply' => 'Original AI proposed reply before agent review.',
            'status' => 'pending_review',
        ]);

        // 1. Agent edits the draft
        $editRes = $this->patchJson("/api/support/drafts/{$draft->id}/edit", [
            'edited_reply' => 'Customized Human Agent reply: Please review your egress security group rules on port 443.',
        ]);
        $editRes->assertStatus(200)
            ->assertJsonPath('draft.edited_reply', 'Customized Human Agent reply: Please review your egress security group rules on port 443.');

        // 2. Agent approves and dispatches the draft
        $approveRes = $this->postJson("/api/support/drafts/{$draft->id}/approve", [
            'channel' => 'portal',
        ]);

        $approveRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('draft.status', 'approved')
            ->assertJsonPath('comment.is_internal', false)
            ->assertJsonPath('comment.body', 'Customized Human Agent reply: Please review your egress security group rules on port 443.');

        // Verify draft record updated
        $draft->refresh();
        $this->assertEquals('approved', $draft->status);
        $this->assertEquals($this->supportUser->id, $draft->reviewed_by);
        $this->assertNotNull($draft->dispatched_at);
        $this->assertEquals('delivered', $draft->dispatch_status);

        // Verify customer comment exists
        $comment = SupportTicketComment::where('ticket_id', $ticket->id)->where('is_internal', false)->first();
        $this->assertNotNull($comment);
        $this->assertEquals($draft->dispatched_comment_id, $comment->id);

        // Verify lifecycle event recorded
        $this->assertDatabaseHas('support_ticket_events', [
            'ticket_id' => $ticket->id,
            'event_type' => 'draft_approved_and_dispatched',
            'actor_id' => $this->supportUser->id,
        ]);

        // Verify ticket status advanced to pending_customer
        $ticket->refresh();
        $this->assertEquals('pending_customer', $ticket->status);
    }

    public function test_human_can_reject_draft_with_reason(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Complex cluster migration query',
            'description' => 'Need advice on live multi-region migration.',
            'status' => 'open',
        ]);

        $draft = SupportAiDraft::create([
            'organization_id' => $this->organization->id,
            'ticket_id' => $ticket->id,
            'proposed_reply' => 'Proposed reply which is inaccurate for multi-region setup.',
            'status' => 'pending_review',
        ]);

        $rejectRes = $this->postJson("/api/support/drafts/{$draft->id}/reject", [
            'rejection_reason' => 'Draft is too generic; requires senior architecture review.',
        ]);

        $rejectRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('draft.status', 'rejected')
            ->assertJsonPath('draft.rejection_reason', 'Draft is too generic; requires senior architecture review.');

        // Zero customer comments created
        $this->assertEquals(0, SupportTicketComment::count());

        // Event logged
        $this->assertDatabaseHas('support_ticket_events', [
            'ticket_id' => $ticket->id,
            'event_type' => 'draft_rejected',
        ]);
    }

    public function test_cannot_approve_already_approved_draft(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'Test subject',
            'description' => 'Test description',
            'status' => 'open',
        ]);

        $draft = SupportAiDraft::create([
            'organization_id' => $this->organization->id,
            'ticket_id' => $ticket->id,
            'proposed_reply' => 'Approved response.',
            'status' => 'approved', // Already approved
        ]);

        $res = $this->postJson("/api/support/drafts/{$draft->id}/approve");
        $res->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_cross_tenant_draft_isolation_prevents_unauthorized_access(): void
    {
        // Second tenant
        $otherOrg = Organization::create([
            'name' => 'Foreign Corp',
            'status' => 'active',
        ]);

        $otherTicket = SupportTicket::create([
            'organization_id' => $otherOrg->id,
            'ticket_number' => 'TIC-8888',
            'ticket_sequence' => 8888,
            'subject' => 'Secret ticket',
            'description' => 'Secret issue',
            'status' => 'open',
        ]);

        $otherDraft = SupportAiDraft::create([
            'organization_id' => $otherOrg->id,
            'ticket_id' => $otherTicket->id,
            'proposed_reply' => 'Confidential reply.',
            'status' => 'pending_review',
        ]);

        Sanctum::actingAs($this->supportUser);

        // Attempt to generate draft for another tenant's ticket
        $generateRes = $this->postJson("/api/support/tickets/{$otherTicket->id}/drafts/generate");
        $generateRes->assertStatus(403);

        // Attempt to view draft from another tenant
        $viewRes = $this->getJson("/api/support/drafts/{$otherDraft->id}");
        $viewRes->assertStatus(403);

        // Attempt to approve draft from another tenant
        $approveRes = $this->postJson("/api/support/drafts/{$otherDraft->id}/approve");
        $approveRes->assertStatus(403);
    }

    public function test_agent_can_submit_feedback_on_draft(): void
    {
        Sanctum::actingAs($this->supportUser);

        $ticket = SupportTicket::create([
            'organization_id' => $this->organization->id,
            'ticket_number' => 'TIC-1001',
            'ticket_sequence' => 1001,
            'subject' => 'SSL Renewal Error',
            'description' => 'Details',
            'status' => 'open',
        ]);

        $draft = SupportAiDraft::create([
            'organization_id' => $this->organization->id,
            'ticket_id' => $ticket->id,
            'proposed_reply' => 'Run certbot renew --force.',
            'status' => 'approved',
        ]);

        $feedbackRes = $this->postJson("/api/support/drafts/{$draft->id}/feedback", [
            'rating' => 1,
            'feedback_text' => 'High quality suggestion with exact command.',
        ]);

        $feedbackRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('feedback.rating', 1);

        $this->assertDatabaseHas('support_ai_feedback', [
            'draft_id' => $draft->id,
            'user_id' => $this->supportUser->id,
            'rating' => 1,
        ]);
    }
}
