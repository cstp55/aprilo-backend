<?php

namespace Tests\Feature;

use App\Models\EcommerceConnection;
use App\Models\EcommerceLicense;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
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
}
