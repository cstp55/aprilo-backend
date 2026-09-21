<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $organization = Organization::firstOrCreate(
            ['name' => 'Demo Company'],
            ['status' => 'active', 'plan' => 'enterprise']
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'organization_id' => $organization->id,
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner->value,
                'status' => 'active',
            ]
        );

        $hr = User::firstOrCreate(
            ['email' => 'hr@example.com'],
            [
                'organization_id' => $organization->id,
                'name' => 'HR Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::HrAdmin->value,
                'status' => 'active',
                'employee_id' => 'EMP-2026-002',
            ]
        );

        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'organization_id' => $organization->id,
                'name' => 'Demo Employee',
                'password' => Hash::make('password'),
                'role' => UserRole::Employee->value,
                'status' => 'active',
                'employee_id' => 'EMP-2026-987',
            ]
        );

        OrganizationSetting::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'minutes_saved_per_resolved_question' => 5,
                'default_escalation_owner' => $owner->id,
                'assistant_status' => 'active',
                'assistant_name' => 'Aprilo Bot',
                'chatbot_color_palette' => '#d22630',
                'chatbot_icon' => 'robot',
                'chatbot_data_source' => 'documents',
                'chatbot_intelligence_level' => 'standard',
                'chatbot_ticket_creation' => 'feedback-based',
                'chatbot_escalation_enabled' => true,
                'connect_teams' => false,
                'connect_skype' => false,
                'connect_whatsapp' => false,
                'connect_mail' => true,
                'hr_desk_email' => 'hr-helpdesk@example.com',
                
                // Advanced connection connection flags
                'teams_connected' => false,
                'skype_connected' => false,
                'whatsapp_connected' => false,
                'mail_connected' => false,
            ]
        );

        // Seed Leave requests
        \App\Models\LeaveRequest::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $employee->id,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
            ],
            [
                'reason' => 'Summer Vacation',
                'status' => 'approved',
                'approved_by' => $hr->id,
            ]
        );

        \App\Models\LeaveRequest::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $employee->id,
                'start_date' => '2026-08-10',
                'end_date' => '2026-08-12',
            ],
            [
                'reason' => 'Family Event',
                'status' => 'pending',
            ]
        );

        // Seed WFH requests
        \App\Models\WfhRequest::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $employee->id,
                'date' => '2026-07-15',
            ],
            [
                'reason' => 'Broadband maintenance at home',
                'status' => 'approved',
                'approved_by' => $hr->id,
            ]
        );

        \App\Models\WfhRequest::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $employee->id,
                'date' => '2026-07-22',
            ],
            [
                'reason' => 'Doctor checkup in afternoon',
                'status' => 'pending',
            ]
        );

        // Seed ID Card
        \App\Models\IdCard::firstOrCreate(
            [
                'user_id' => $employee->id,
            ],
            [
                'organization_id' => $organization->id,
                'card_number' => 'APR-98765-EMP',
                'issue_date' => '2026-01-10',
                'expiry_date' => '2029-01-10',
                'status' => 'active',
            ]
        );
    }
}
