<?php

namespace Tests\Feature;

use App\Models\Escalation;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Question;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SprintFourTeamsEscalationTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminOwner;
    private User $hrRep1;
    private User $hrRep2;
    private User $employee;
    private Organization $organization;
    private OrganizationSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create([
            'name' => 'Teams Test Corp',
            'plan' => 'pro',
            'status' => 'active',
        ]);

        $this->settings = OrganizationSetting::create([
            'organization_id' => $this->organization->id,
            'connect_teams' => true,
            'teams_connected' => true,
            'teams_use_webhook' => false,
            'teams_app_id' => '11111111-1111-1111-1111-111111111111',
            'teams_app_password' => 'bot_password',
            'teams_tenant_id' => '22222222-2222-2222-2222-222222222222',
            'teams_webhook_url' => 'https://smba.trafficmanager.net/apis',
        ]);

        $this->adminOwner = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Owner Admin',
            'email' => 'owner@teamscorp.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Owner->value,
            'status' => 'active',
            'teams_user_id' => 'owner-teams-id',
            'escalation_priority' => 10,
        ]);

        $this->hrRep1 = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'HR Specialist High Priority',
            'email' => 'hr1@teamscorp.com',
            'password' => bcrypt('password'),
            'role' => UserRole::HrAdmin->value,
            'status' => 'active',
            'teams_user_id' => 'hr1-teams-id',
            'escalation_priority' => 1, // highest priority
            'escalation_routing_active' => true,
        ]);

        $this->hrRep2 = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'HR Manager Low Priority',
            'email' => 'hr2@teamscorp.com',
            'password' => bcrypt('password'),
            'role' => UserRole::HrAdmin->value,
            'status' => 'active',
            'teams_user_id' => 'hr2-teams-id',
            'escalation_priority' => 5, // lower priority
            'escalation_routing_active' => true,
        ]);

        $this->employee = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Worker Bee',
            'email' => 'employee@teamscorp.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'status' => 'active',
            'teams_user_id' => 'employee-teams-id',
        ]);
    }

    public function test_can_update_employee_teams_settings(): void
    {
        $this->actingAs($this->adminOwner);

        $this->patch(route('admin.employees.update', $this->hrRep1->id), [
            'teams_user_id' => 'new-teams-id-123',
            'escalation_priority' => 2,
            'escalation_routing_active' => '1',
        ])->assertRedirect(route('admin.employees'));

        $this->hrRep1->refresh();
        $this->assertEquals('new-teams-id-123', $this->hrRep1->teams_user_id);
        $this->assertEquals(2, $this->hrRep1->escalation_priority);
        $this->assertTrue($this->hrRep1->escalation_routing_active);
    }

    public function test_incoming_sensitive_teams_message_escalates_to_highest_priority_hr_rep(): void
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(['access_token' => 'mock_token'], 200),
            'https://smba.trafficmanager.net/apis/v3/conversations' => Http::response(['id' => 'conversation_with_hr1'], 201),
            'https://smba.trafficmanager.net/apis/v3/conversations/*/activities' => Http::response(['id' => 'activity_id'], 201),
        ]);

        // Send a message containing "unionize" or "harassment" which the SensitivityClassifier flags
        $payload = [
            'type' => 'message',
            'id' => 'activity-123',
            'text' => 'There is severe harassment in my department, I need help',
            'serviceUrl' => 'https://smba.trafficmanager.net/apis',
            'conversation' => [
                'id' => 'employee_direct_chat_id',
                'tenantId' => '22222222-2222-2222-2222-222222222222',
            ],
            'from' => [
                'id' => 'employee-teams-id',
                'name' => 'Worker Bee',
            ],
            'recipient' => [
                'id' => 'bot-teams-id',
            ],
        ];

        $response = $this->postJson('/api/teams/messages', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'escalated']);

        // Assert escalation was created and assigned to hrRep1 (Priority 1) instead of hrRep2 (Priority 5)
        $escalation = Escalation::latest()->first();
        $this->assertNotNull($escalation);
        $this->assertEquals($this->hrRep1->id, $escalation->assigned_to);
        $this->assertEquals('open', $escalation->status);

        // Verify proactive message was sent to MS Teams
        Http::assertSent(function ($request) {
            return $request->url() === 'https://smba.trafficmanager.net/apis/v3/conversations' &&
                $request['members'][0]['id'] === 'hr1-teams-id';
        });
    }

    public function test_hr_rep_can_submit_adaptive_card_reply_to_resolve_escalation(): void
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(['access_token' => 'mock_token'], 200),
            'https://smba.trafficmanager.net/apis/v3/conversations/*/activities' => Http::response(['id' => 'activity_id'], 201),
        ]);

        $question = Question::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->employee->id,
            'question_text' => 'Sensitive workplace issues',
            'topic' => 'sensitive',
            'sensitivity_status' => 'sensitive',
            'status' => 'escalated',
        ]);

        $escalation = Escalation::create([
            'organization_id' => $this->organization->id,
            'question_id' => $question->id,
            'user_id' => $this->employee->id,
            'category' => 'sensitive',
            'status' => 'open',
            'assigned_to' => $this->hrRep1->id,
        ]);

        // Mock employee conversation details
        $this->employee->update([
            'teams_conversation_id' => 'employee_direct_chat_id',
            'teams_service_url' => 'https://smba.trafficmanager.net/apis',
        ]);

        $payload = [
            'type' => 'message',
            'id' => 'reply-submit-123',
            'serviceUrl' => 'https://smba.trafficmanager.net/apis',
            'conversation' => [
                'id' => 'hr_direct_chat_id',
                'tenantId' => '22222222-2222-2222-2222-222222222222',
            ],
            'from' => [
                'id' => 'hr1-teams-id',
                'name' => 'HR Specialist',
            ],
            'recipient' => [
                'id' => 'bot-teams-id',
            ],
            'value' => [
                'action' => 'escalation_reply',
                'escalation_id' => $escalation->id,
                'replyText' => 'Please schedule a meeting with me to discuss this confidentially tomorrow at 10 AM.',
            ]
        ];

        $response = $this->postJson('/api/teams/messages', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('attachments.0.content.body.0.text', '✅ Escalation Resolved');

        $escalation->refresh();
        $this->assertEquals('resolved', $escalation->status);
        $this->assertEquals('Please schedule a meeting with me to discuss this confidentially tomorrow at 10 AM.', $escalation->resolution_note);

        // Verify reply was brokered proactively back to the employee's conversation
        Http::assertSent(function ($request) {
            return $request->url() === 'https://smba.trafficmanager.net/apis/v3/conversations/employee_direct_chat_id/activities' &&
                str_contains($request['text'], 'Please schedule a meeting with me');
        });
    }
}
