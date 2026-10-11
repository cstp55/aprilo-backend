<?php

namespace Tests\Feature\Support;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Support\SupportCustomer;
use App\Models\Support\SupportPortalSetting;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketCategory;
use App\Models\Support\SupportTicketForm;
use App\Models\Support\SupportTicketFormVersion;
use App\Models\User;
use App\Services\Navigation\NavigationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupportPortalAndFormBuilderTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $superAdmin;
    private User $orgAdmin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ProductsAndPlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->organization = Organization::create([
            'name' => 'Acme Cloud Solutions',
            'slug' => 'acme-cloud',
            'email' => 'support@acmecloud.com',
            'is_active' => true,
        ]);

        $supportProduct = \App\Models\Product::where('slug', 'aprilo-support')->first();
        if ($supportProduct) {
            $this->organization->products()->attach($supportProduct->id, [
                'status' => 'active',
                'expires_at' => now()->addYear(),
            ]);
        }

        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $this->superAdmin = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Super Administrator',
            'email' => 'superadmin@aprilo.test',
            'password' => bcrypt('Secret123!'),
            'role_id' => $superAdminRole->id,
            'is_active' => true,
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->orgAdmin = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Acme Support Director',
            'email' => 'director@acmecloud.com',
            'password' => bcrypt('Secret123!'),
            'role_id' => $ownerRole->id,
            'is_active' => true,
        ]);

        $staffRole = Role::where('slug', 'employee')->first();
        $this->regularUser = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Regular Staff Member',
            'email' => 'staff@acmecloud.com',
            'password' => bcrypt('Secret123!'),
            'role_id' => $staffRole->id,
            'is_active' => true,
        ]);
    }

    public function test_navigation_service_includes_support_settings_for_super_admin(): void
    {
        $service = app(NavigationService::class);
        $menu = $service->getSidebarMenu($this->superAdmin);

        $allRoutes = [];
        $headers = [];
        foreach ($menu as $group) {
            $headers[] = $group['header'];
            foreach ($group['items'] as $item) {
                $allRoutes[] = $item['route'];
            }
        }

        $this->assertContains('admin.support.settings', $allRoutes);
        $this->assertContains('admin.support.forms', $allRoutes);
        $this->assertContains('Platform Administration', $headers);
    }

    public function test_navigation_service_includes_support_menu_for_authorized_org_admin(): void
    {
        $service = app(NavigationService::class);
        $menu = $service->getSidebarMenu($this->orgAdmin);

        $hasSupportHeader = false;
        $supportRoutes = [];

        foreach ($menu as $group) {
            if ($group['header'] === 'Aprilo Support') {
                $hasSupportHeader = true;
                foreach ($group['items'] as $item) {
                    $supportRoutes[] = $item['route'];
                }
            }
        }

        $this->assertTrue($hasSupportHeader, 'Authorized admin should see the Aprilo Support menu section');
        $this->assertContains('admin.support.settings', $supportRoutes);
        $this->assertContains('admin.support.forms', $supportRoutes);
    }

    public function test_navigation_service_excludes_support_menu_for_unauthorized_user(): void
    {
        $service = app(NavigationService::class);
        $menu = $service->getSidebarMenu($this->regularUser);

        $headers = array_column($menu, 'header');
        $this->assertNotContains('Aprilo Support', $headers);
    }

    public function test_admin_can_view_support_portal_settings_page(): void
    {
        $response = $this->actingAs($this->orgAdmin)
            ->get(route('admin.support.settings'));

        $response->assertStatus(200);
        $response->assertSee('Acme Cloud Solutions Support Center');
        $response->assertSee('support.apriloinfotech.com/portal');
        $response->assertSee('Support Categories');
    }

    public function test_admin_can_update_portal_branding_and_configuration(): void
    {
        $response = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.portal.update'), [
                'portal_title' => 'Acme Global Customer Desk',
                'portal_subheading' => '24/7 Enterprise Assistance',
                'portal_slug' => 'acme-global',
                'support_email' => 'help@acmecloud.com',
                'primary_color' => '#10B981',
                'secondary_color' => '#1E293B',
                'logo_url' => 'https://example.com/logo.png',
                'allow_anonymous_tickets' => '1',
                'require_email_verification' => '1',
                'enable_customer_portal_auth' => '1',
                'is_portal_active' => '1',
            ]);

        $response->assertRedirect(route('admin.support.settings'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('support_portal_settings', [
            'organization_id' => $this->organization->id,
            'portal_title' => 'Acme Global Customer Desk',
            'portal_slug' => 'acme-global',
            'primary_color' => '#10B981',
            'require_email_verification' => 1,
            'is_portal_active' => 1,
        ]);
    }

    public function test_portal_slug_must_be_unique_across_organizations(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Other Corp',
            'slug' => 'other-corp',
            'email' => 'other@corp.com',
            'is_active' => true,
        ]);

        SupportPortalSetting::create([
            'organization_id' => $otherOrg->id,
            'portal_slug' => 'taken-slug',
            'portal_title' => 'Other Portal',
        ]);

        $response = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.portal.update'), [
                'portal_title' => 'Acme Global Desk',
                'portal_slug' => 'taken-slug', // duplicate slug
                'primary_color' => '#10B981',
            ]);

        $response->assertSessionHasErrors(['portal_slug']);
    }

    public function test_admin_can_create_and_delete_support_category(): void
    {
        $response = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.categories.store'), [
                'name' => 'Cloud Infrastructure',
                'department' => 'DevOps & SRE',
                'description' => 'Server downtime, SSL, and network incidents',
            ]);

        $response->assertRedirect(route('admin.support.settings'));
        $this->assertDatabaseHas('support_ticket_categories', [
            'organization_id' => $this->organization->id,
            'name' => 'Cloud Infrastructure',
            'department' => 'DevOps & SRE',
        ]);

        $category = SupportTicketCategory::where('organization_id', $this->organization->id)->first();

        $delResponse = $this->actingAs($this->orgAdmin)
            ->delete(route('admin.support.categories.delete', $category->id));

        $delResponse->assertRedirect(route('admin.support.settings'));
        $this->assertDatabaseMissing('support_ticket_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_view_forms_list_and_create_form(): void
    {
        $response = $this->actingAs($this->orgAdmin)
            ->get(route('admin.support.forms'));

        $response->assertStatus(200);
        $response->assertSee('Dynamic Ticket Forms');

        $schema = [
            [
                'key' => 'customer_name',
                'label' => 'Full Name',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Enter your full name',
            ],
            [
                'key' => 'customer_email',
                'label' => 'Email Address',
                'type' => 'email',
                'required' => true,
                'placeholder' => 'you@company.com',
            ],
            [
                'key' => 'issue_type',
                'label' => 'Issue Category',
                'type' => 'select',
                'required' => true,
                'options' => ['Billing', 'Technical Infrastructure'],
            ],
            [
                'key' => 'server_id',
                'label' => 'Server Identifier',
                'type' => 'text',
                'required' => true,
                'condition' => [
                    'field' => 'issue_type',
                    'operator' => 'equals',
                    'value' => 'Technical Infrastructure',
                ],
            ],
        ];

        $storeResponse = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.forms.store'), [
                'name' => 'Infrastructure Incident Report',
                'description' => 'For reporting cloud outages and technical incidents',
                'status' => 'draft',
                'schema' => json_encode($schema),
            ]);

        $storeResponse->assertRedirect(route('admin.support.forms'));
        $this->assertDatabaseHas('support_ticket_forms', [
            'organization_id' => $this->organization->id,
            'name' => 'Infrastructure Incident Report',
            'status' => 'draft',
            'version' => 1,
        ]);
    }

    public function test_admin_can_duplicate_publish_and_archive_form(): void
    {
        $form = SupportTicketForm::create([
            'organization_id' => $this->organization->id,
            'name' => 'VIP Escalation Form',
            'slug' => 'vip-escalation-form',
            'status' => 'draft',
            'version' => 1,
            'schema' => SupportTicketForm::getDefaultStarterSchema(),
        ]);

        // 1. Duplicate
        $dupResponse = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.forms.duplicate', $form->id));

        $dupResponse->assertRedirect(route('admin.support.forms'));
        $this->assertDatabaseHas('support_ticket_forms', [
            'organization_id' => $this->organization->id,
            'name' => 'VIP Escalation Form (Copy)',
            'status' => 'draft',
        ]);

        // 2. Publish
        $pubResponse = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.forms.publish', $form->id));

        $pubResponse->assertRedirect(route('admin.support.forms'));
        $form->refresh();
        $this->assertEquals('published', $form->status);
        $this->assertEquals(2, $form->version);

        // Version history snapshot created
        $this->assertDatabaseHas('support_ticket_form_versions', [
            'form_id' => $form->id,
            'version' => 2,
        ]);

        // 3. Archive
        $archResponse = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.forms.archive', $form->id));

        $archResponse->assertRedirect(route('admin.support.forms'));
        $form->refresh();
        $this->assertEquals('archived', $form->status);
    }

    public function test_admin_can_preview_form(): void
    {
        $form = SupportTicketForm::create([
            'organization_id' => $this->organization->id,
            'name' => 'Interactive Preview Form',
            'slug' => 'interactive-preview-form',
            'status' => 'published',
            'version' => 1,
            'schema' => SupportTicketForm::getDefaultStarterSchema(),
        ]);

        $response = $this->actingAs($this->orgAdmin)
            ->get(route('admin.support.forms.preview', $form->id));

        $response->assertStatus(200);
        $response->assertSee('Interactive Preview Form');
        $response->assertSee('Desktop');
        $response->assertSee('Mobile');
    }

    public function test_api_intake_submits_ticket_with_deterministic_conditional_logic(): void
    {
        $schema = [
            [
                'key' => 'customer_name',
                'label' => 'Full Name',
                'type' => 'text',
                'required' => true,
            ],
            [
                'key' => 'customer_email',
                'label' => 'Email Address',
                'type' => 'email',
                'required' => true,
            ],
            [
                'key' => 'subject',
                'label' => 'Subject',
                'type' => 'text',
                'required' => true,
            ],
            [
                'key' => 'issue_category',
                'label' => 'Issue Category',
                'type' => 'select',
                'required' => true,
                'options' => ['Billing', 'Server/Infrastructure'],
            ],
            [
                'key' => 'server_identifier',
                'label' => 'Server Identifier',
                'type' => 'text',
                'required' => true,
                'condition' => [
                    'field' => 'issue_category',
                    'operator' => 'equals',
                    'value' => 'Server/Infrastructure',
                ],
            ],
        ];

        $form = SupportTicketForm::create([
            'organization_id' => $this->organization->id,
            'name' => 'Public Intake Test Form',
            'slug' => 'public-intake-test',
            'status' => 'published',
            'version' => 1,
            'schema' => $schema,
        ]);

        // Case A: issue_category is Billing, so server_identifier is conditionally hidden.
        // It is omitted, but should succeed without required validation error.
        $payloadBilling = [
            'customer_name' => 'Alice Smith',
            'customer_email' => 'alice@company.com',
            'subject' => 'Invoice discrepancy query',
            'issue_category' => 'Billing',
            // server_identifier omitted because hidden
        ];

        $resA = $this->postJson("/api/support/public/forms/{$form->id}/submit", $payloadBilling);

        $resA->assertStatus(201);
        $resA->assertJsonPath('success', true);
        $ticketId = $resA->json('ticket.id');
        $ticketNum = $resA->json('ticket.ticket_number');

        $this->assertStringStartsWith('TIC-', $ticketNum);
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticketId,
            'subject' => 'Invoice discrepancy query',
            'channel' => 'website',
        ]);

        // Case B: issue_category is Server/Infrastructure, so server_identifier is visible and required.
        // Omission must return validation error 422.
        $payloadServerMissing = [
            'customer_name' => 'Bob Engineer',
            'customer_email' => 'bob@company.com',
            'subject' => 'Primary database unresponsive',
            'issue_category' => 'Server/Infrastructure',
            // server_identifier is missing
        ];

        $resB = $this->postJson("/api/support/public/forms/{$form->id}/submit", $payloadServerMissing);
        $resB->assertStatus(422);
        $resB->assertJsonValidationErrors(['server_identifier']);

        // Case C: Provide required server_identifier for Server/Infrastructure.
        $payloadServerValid = [
            'customer_name' => 'Bob Engineer',
            'customer_email' => 'bob@company.com',
            'subject' => 'Primary database unresponsive',
            'issue_category' => 'Server/Infrastructure',
            'server_identifier' => 'srv-prod-db-01',
        ];

        $resC = $this->postJson("/api/support/public/forms/{$form->id}/submit", $payloadServerValid);
        $resC->assertStatus(201);
        $resC->assertJsonPath('success', true);
    }

    public function test_cannot_tamper_with_other_organization_form(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Competitor Corp',
            'slug' => 'competitor-corp',
            'email' => 'admin@competitor.com',
            'is_active' => true,
        ]);

        $otherForm = SupportTicketForm::create([
            'organization_id' => $otherOrg->id,
            'name' => 'Competitor Internal Form',
            'slug' => 'competitor-internal-form',
            'status' => 'draft',
            'version' => 1,
            'schema' => SupportTicketForm::getDefaultStarterSchema(),
        ]);

        // Acme admin attempts to edit competitor's form
        $response = $this->actingAs($this->orgAdmin)
            ->get(route('admin.support.forms.edit', $otherForm->id));

        $response->assertStatus(403);

        // Acme admin attempts to publish competitor's form
        $pubResponse = $this->actingAs($this->orgAdmin)
            ->post(route('admin.support.forms.publish', $otherForm->id));

        $pubResponse->assertStatus(403);
    }

    public function test_super_admin_sees_all_organizations_in_select_field_and_prompts_to_select(): void
    {
        $orgB = Organization::create([
            'name' => 'Beta Global Industries',
            'slug' => 'beta-global',
            'email' => 'admin@betaglobal.com',
            'is_active' => true,
        ]);

        // Super admin visits without organization_id
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.support.settings'));

        $response->assertStatus(200);
        $response->assertSee('Please Select an Organization');
        $response->assertSee('Select Organization');
        // Super admin sees all organizations in the select field
        $response->assertSee($this->organization->name);
        $response->assertSee($orgB->name);
    }

    public function test_super_admin_can_switch_organization_and_view_settings(): void
    {
        $orgB = Organization::create([
            'name' => 'Beta Global Industries',
            'slug' => 'beta-global',
            'email' => 'admin@betaglobal.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.support.settings', ['organization_id' => $orgB->id]));

        $response->assertStatus(200);
        $response->assertSee('Active Organization: Beta Global Industries');
        $response->assertSee('Beta Global Industries Support Center');
        $response->assertDontSee('Please Select an Organization');
    }

    public function test_owner_can_see_only_their_organization_in_select_field(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Secret Enterprise LLC',
            'slug' => 'secret-enterprise',
            'email' => 'admin@secret.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->orgAdmin)
            ->get(route('admin.support.settings'));

        $response->assertStatus(200);
        // Owner sees only their own organization in the select field
        $response->assertSee($this->organization->name);
        $response->assertDontSee('Secret Enterprise LLC');
    }

    public function test_owner_cannot_access_other_organization_support_settings(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Restricted Org Inc',
            'slug' => 'restricted-org',
            'email' => 'admin@restricted.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->orgAdmin)
            ->get(route('admin.support.settings', ['organization_id' => $otherOrg->id]));

        $response->assertStatus(403);
    }
}
