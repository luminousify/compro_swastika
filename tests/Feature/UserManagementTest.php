<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;
    private User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'force_password_change' => false,
        ]);
        
        $this->sales = User::factory()->create([
            'role' => 'sales',
            'force_password_change' => false,
        ]);
        
        $this->targetUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'sales',
        ]);
    }

    public function test_admin_can_access_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    public function test_sales_cannot_access_users_index(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/users');
        
        $response->assertStatus(403);
    }

    public function test_unauthenticated_users_cannot_access_users_section(): void
    {
        $response = $this->get('/admin/users');
        
        $response->assertRedirect('/login');
    }

    public function test_users_index_displays_paginated_users(): void
    {
        // Create 15 users to test pagination
        User::factory()->count(15)->create();
        
        $response = $this->actingAs($this->admin)->get('/admin/users');
        
        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertEquals(12, $users->perPage());
        $this->assertGreaterThan(12, $users->total());
    }

    public function test_users_index_supports_search(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        
        $response = $this->actingAs($this->admin)->get('/admin/users?search=john');
        
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    public function test_admin_can_access_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
    }

    public function test_admin_can_create_new_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'sales',
            'force_password_change' => true,
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/users', $userData);
        
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success', 'User created successfully');
        
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'sales',
            'force_password_change' => true,
        ]);
        
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_user_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', []);
        
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_user_creation_validates_email_uniqueness(): void
    {
        $userData = [
            'name' => 'Duplicate User',
            'email' => $this->targetUser->email, // Use existing email
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'sales',
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/users', $userData);
        
        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_creation_validates_password_requirements(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'weak', // Too short
            'password_confirmation' => 'weak',
            'role' => 'sales',
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/users', $userData);
        
        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_can_access_edit_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/users/{$this->targetUser->id}/edit");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user', $this->targetUser);
    }

    public function test_admin_can_update_user(): void
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ];
        
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->targetUser->id}", $updateData);
        
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success', 'User updated successfully');
        
        $this->targetUser->refresh();
        $this->assertEquals('Updated Name', $this->targetUser->name);
        $this->assertEquals('updated@example.com', $this->targetUser->email);
        $this->assertEquals('admin', $this->targetUser->role->value);
    }

    public function test_admin_can_update_user_with_password(): void
    {
        $updateData = [
            'name' => $this->targetUser->name,
            'email' => $this->targetUser->email,
            'role' => $this->targetUser->role->value,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'force_password_change' => true,
        ];
        
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->targetUser->id}", $updateData);
        
        $response->assertRedirect('/admin/users');
        
        $this->targetUser->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $this->targetUser->password));
        $this->assertTrue($this->targetUser->force_password_change);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->id}");
        
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error', 'You cannot delete your own account');
        
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_other_users(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/users/{$this->targetUser->id}");
        
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success', 'User deleted successfully');
        
        $this->assertDatabaseMissing('users', ['id' => $this->targetUser->id]);
    }

    public function test_cannot_delete_last_admin(): void
    {
        // Delete all admins except the current one
        User::where('role', 'admin')->where('id', '!=', $this->admin->id)->delete();
        
        // Create another user to try to change role and delete admin
        $lastAdmin = $this->admin;
        $response = $this->actingAs($lastAdmin)->delete("/admin/users/{$lastAdmin->id}");
        
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('users', ['id' => $lastAdmin->id]);
    }

    public function test_edit_form_shows_current_values(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/users/{$this->targetUser->id}/edit");
        
        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $response->assertSee($this->targetUser->email);
        $response->assertSee($this->targetUser->role);
    }

    public function test_user_list_shows_user_details(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        
        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $response->assertSee($this->targetUser->email);
        $response->assertSee('Sales'); // Role display
    }

    public function test_user_search_by_email(): void
    {
        User::factory()->create(['email' => 'specific@domain.com']);
        User::factory()->create(['email' => 'another@example.com']);
        
        $response = $this->actingAs($this->admin)->get('/admin/users?search=specific@domain.com');
        
        $response->assertStatus(200);
        $response->assertSee('specific@domain.com');
        $response->assertDontSee('another@example.com');
    }

    public function test_user_search_by_role(): void
    {
        User::factory()->count(3)->create(['role' => 'admin']);
        User::factory()->count(5)->create(['role' => 'sales']);
        
        $response = $this->actingAs($this->admin)->get('/admin/users?role=admin');
        
        $response->assertStatus(200);
        $users = $response->viewData('users');
        
        foreach ($users as $user) {
            $this->assertEquals('admin', $user->role->value);
        }
    }

    public function test_pagination_preserves_search_parameters(): void
    {
        User::factory()->count(20)->create(['role' => 'sales']);
        
        $response = $this->actingAs($this->admin)->get('/admin/users?search=sales&page=2');
        
        $response->assertStatus(200);
        $response->assertSee('search=sales'); // Search param preserved in pagination links
    }

    public function test_create_user_form_has_role_options(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users/create');
        
        $response->assertStatus(200);
        $response->assertSee('Admin');
        $response->assertSee('Sales');
        $response->assertSee('Force Password Change');
    }

    public function test_cannot_update_user_email_to_existing_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        
        $updateData = [
            'name' => $this->targetUser->name,
            'email' => 'existing@example.com',
            'role' => $this->targetUser->role->value,
        ];
        
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->targetUser->id}", $updateData);
        
        $response->assertSessionHasErrors(['email']);
    }

    public function test_sales_cannot_access_any_user_management_routes(): void
    {
        // Test all user management routes
        $routes = [
            ['GET', "/admin/users"],
            ['GET', "/admin/users/create"],
            ['POST', "/admin/users"],
            ['GET', "/admin/users/{$this->targetUser->id}/edit"],
            ['PUT', "/admin/users/{$this->targetUser->id}"],
            ['DELETE', "/admin/users/{$this->targetUser->id}"],
        ];
        
        foreach ($routes as [$method, $uri]) {
            $response = $this->actingAs($this->sales)->call($method, $uri);
            $response->assertStatus(403);
        }
    }
}