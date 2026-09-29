<?php

namespace Tests\Feature;

use App\Models\EcommerceConnection;
use App\Models\EcommerceLicense;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Product;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Navigation\NavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_all_permissions_implicitly(): void
    {
        $org = Organization::create(['name' => 'Acme Corp']);
        $superRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'is_system' => true,
        ]);

        $superUser = User::create([
            'organization_id' => $org->id,
            'role_id' => $superRole->id,
            'name' => 'Platform Super',
            'email' => 'super@example.com',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);

        $this->assertTrue($superUser->isSuperAdmin());
        $this->assertTrue($superUser->hasPermission('super_admin.dashboard.view'));
        $this->assertTrue($superUser->hasPermission('hr.dashboard.view'));
        $this->assertTrue($superUser->hasPermission('ecommerce.products.sync'));
        $this->assertTrue($superUser->hasPermission('any.arbitrary.permission'));
    }

    public function test_hr_admin_permissions_and_navigation_generation(): void
    {
        $org = Organization::create(['name' => 'HR Corp']);
        $permView = Permission::create([
            'category' => 'hr',
            'slug' => 'knowledge.sources.view',
            'name' => 'View Knowledge',
        ]);
        $permLeaves = Permission::create([
            'category' => 'hr',
            'slug' => 'employee.leaves.manage',
            'name' => 'Manage Leaves',
        ]);

        $hrRole = Role::create([
            'organization_id' => $org->id,
            'name' => 'HR Specialist',
            'slug' => 'hr_specialist',
            'is_system' => false,
        ]);
        $hrRole->permissions()->attach([$permView->id, $permLeaves->id]);

        $hrUser = User::create([
            'organization_id' => $org->id,
            'role_id' => $hrRole->id,
            'name' => 'Hannah HR',
            'email' => 'hannah@example.com',
            'password' => 'secret',
            'role' => 'hr_admin',
        ]);

        $this->assertTrue($hrUser->hasPermission('knowledge.sources.view'));
        $this->assertTrue($hrUser->hasPermission('employee.leaves.manage'));
        $this->assertFalse($hrUser->hasPermission('ecommerce.products.sync'));
        $this->assertFalse($hrUser->isSuperAdmin());

        $navService = new NavigationService();
        $menu = $navService->getSidebarMenu($hrUser);

        $headers = array_column($menu, 'header');
        $this->assertContains('AI & Knowledge', $headers);
        $this->assertContains('HR Operations', $headers);
        $this->assertNotContains('Platform Administration', $headers);
    }

    public function test_ecommerce_license_key_validation_api(): void
    {
        $org = Organization::create(['name' => 'Ecom Corp']);
        $connection = EcommerceConnection::create([
            'organization_id' => $org->id,
            'platform' => 'magento',
            'store_name' => 'Magento Demo',
            'store_url' => 'https://magento.example.com',
            'status' => 'connected',
        ]);

        $license = EcommerceLicense::create([
            'organization_id' => $org->id,
            'connection_id' => $connection->id,
            'license_key' => 'APR-MAG-TEST-KEY-12345',
            'platform' => 'magento',
            'domain' => 'magento.example.com',
            'status' => 'active',
            'max_stores' => 1,
            'expires_at' => now()->addMonths(6),
        ]);

        // 1. Valid Request
        $response = $this->postJson('/api/ecommerce/license/validate', [
            'license_key' => 'APR-MAG-TEST-KEY-12345',
            'domain' => 'magento.example.com',
            'platform' => 'magento',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'license_key' => 'APR-MAG-TEST-KEY-12345',
                'platform' => 'magento',
                'status' => 'active',
            ]);

        // 2. Domain Mismatch
        $badDomainResponse = $this->postJson('/api/ecommerce/license/validate', [
            'license_key' => 'APR-MAG-TEST-KEY-12345',
            'domain' => 'fraudulent-site.com',
        ]);

        $badDomainResponse->assertStatus(403)
            ->assertJson(['valid' => false]);

        // 3. Non-existent Key
        $notFoundResponse = $this->postJson('/api/ecommerce/license/validate', [
            'license_key' => 'INVALID-KEY-00000',
        ]);

        $notFoundResponse->assertStatus(404)
            ->assertJson(['valid' => false]);
    }

    public function test_owner_navigation_only_shows_purchased_product_features(): void
    {
        $aiOrganization = Organization::create(['name' => 'AI Customer']);
        $aiOwner = User::create([
            'organization_id' => $aiOrganization->id,
            'name' => 'AI Owner',
            'email' => 'ai-owner@example.com',
            'password' => 'secret',
            'role' => 'owner',
        ]);
        $aiProduct = Product::create([
            'slug' => 'ai-plan-product',
            'name' => 'Aprilo Support AI',
            'category' => 'ai_support',
            'product_type' => 'subscription',
        ]);
        $aiPlan = Plan::create([
            'product_id' => $aiProduct->id,
            'slug' => 'monthly',
            'name' => 'Monthly',
        ]);
        Subscription::create([
            'organization_id' => $aiOrganization->id,
            'product_id' => $aiProduct->id,
            'plan_id' => $aiPlan->id,
            'razorpay_subscription_id' => 'ai-' . uniqid(),
            'status' => 'active',
        ]);

        $menu = (new NavigationService())->getSidebarMenu($aiOwner);
        $headers = array_column($menu, 'header');
        $this->assertContains('AI & Knowledge', $headers);
        $this->assertNotContains('HR Operations', $headers);
        $this->assertNotContains('E-commerce Ops', $headers);
        $this->assertNotContains('Platform Administration', $headers);

            $this->actingAs($aiOwner)
                ->get('/admin')
                ->assertOk()
                ->assertSee('Aprilo AI Dashboard')
                ->assertDontSee('HR Overview &amp; Analytics')
                ->assertDontSee('HR Staff &amp; Identity');
    }

    public function test_ecommerce_owner_navigation_requires_a_valid_one_time_license(): void
    {
        $organization = Organization::create(['name' => 'Commerce Customer']);
        $owner = User::create([
            'organization_id' => $organization->id,
            'name' => 'Commerce Owner',
            'email' => 'commerce-owner@example.com',
            'password' => 'secret',
            'role' => 'owner',
        ]);

        $menu = (new NavigationService())->getSidebarMenu($owner);
        $this->assertFalse($owner->hasPermission('ecommerce.dashboard.view'));
        $this->assertNotContains('E-commerce Ops', array_column($menu, 'header'));
            $this->actingAs($owner)->get('/admin/leaves')->assertForbidden();
            $this->actingAs($owner)->get('/admin/ecommerce')->assertForbidden();

        $connection = EcommerceConnection::create([
            'organization_id' => $organization->id,
            'platform' => 'magento',
            'store_name' => 'Commerce Store',
            'store_url' => 'https://commerce.example.com',
            'status' => 'connected',
        ]);
        EcommerceLicense::create([
            'organization_id' => $organization->id,
            'connection_id' => $connection->id,
            'license_key' => 'APR-MAG-ONE-TIME-KEY',
            'platform' => 'magento',
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        $menu = (new NavigationService())->getSidebarMenu($owner);
        $headers = array_column($menu, 'header');
        $this->assertTrue($owner->fresh()->hasPermission('ecommerce.dashboard.view'));
        $this->assertContains('E-commerce Ops', $headers);
        $this->assertNotContains('AI & Knowledge', $headers);
        $this->assertNotContains('HR Operations', $headers);
    }

    public function test_super_admin_login_lands_on_platform_dashboard(): void
    {
        $superAdmin = User::create([
            'name' => 'Platform Admin',
            'email' => 'platform-login@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $this->actingAs($superAdmin)
            ->get('/admin/login')
            ->assertRedirect(route('admin.super.dashboard'));
    }
}
