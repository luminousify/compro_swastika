<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_sales_can_access_dashboard(): void
    {
        $sales = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $response = $this->actingAs($sales)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_role_middleware_allows_admin(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        // Test with a route that requires admin role
        $response = $this->actingAs($admin)
            ->withMiddleware(['role:admin'])
            ->get('/test-admin-route');

        // Should not get 403 forbidden
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_role_middleware_blocks_sales(): void
    {
        $sales = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        // Create a test route with admin middleware
        \Route::get('/test-admin-route', function () {
            return 'admin only';
        })->middleware(['auth', 'role:admin']);

        $response = $this->actingAs($sales)->get('/test-admin-route');

        $response->assertStatus(403);
    }

    public function test_user_can_access_method_works_correctly(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $sales = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        // Admin should have access to settings and users
        $this->assertTrue($admin->canAccess('settings'));
        $this->assertTrue($admin->canAccess('users'));
        $this->assertTrue($admin->canAccess('divisions'));

        // Sales should not have access to settings and users
        $this->assertFalse($sales->canAccess('settings'));
        $this->assertFalse($sales->canAccess('users'));
        $this->assertTrue($sales->canAccess('divisions'));
    }

    public function test_user_role_helper_methods_work(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $sales = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isSales());

        $this->assertFalse($sales->isAdmin());
        $this->assertTrue($sales->isSales());
    }

    public function test_user_policy_allows_admin_to_manage_users(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $targetUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertTrue($admin->can('view', $targetUser));
        $this->assertTrue($admin->can('create', User::class));
        $this->assertTrue($admin->can('update', $targetUser));
        $this->assertTrue($admin->can('delete', $targetUser));
    }

    public function test_user_policy_prevents_sales_from_managing_users(): void
    {
        $sales = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $targetUser = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertFalse($sales->can('viewAny', User::class));
        $this->assertFalse($sales->can('view', $targetUser));
        $this->assertFalse($sales->can('create', User::class));
        $this->assertFalse($sales->can('update', $targetUser));
        $this->assertFalse($sales->can('delete', $targetUser));
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertFalse($admin->can('delete', $admin));
    }

    public function test_settings_policy_allows_admin(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertTrue($admin->can('manage-settings'));
    }

    public function test_settings_policy_prevents_sales(): void
    {
        $sales = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->assertFalse($sales->can('manage-settings'));
    }
}