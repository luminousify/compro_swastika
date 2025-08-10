<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;

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
    }

    public function test_admin_sees_all_navigation_items(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        
        // Admin should see all menu items
        $response->assertSee('Dashboard');
        $response->assertSee('Settings');
        $response->assertSee('Users');
        $response->assertSee('Divisions');
        $response->assertSee('Media');
        $response->assertSee('Milestones');
        $response->assertSee('Clients');
        $response->assertSee('Contact Messages');
    }

    public function test_sales_sees_limited_navigation_items(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        
        // Sales should see content management items
        $response->assertSee('Dashboard');
        $response->assertSee('Divisions');
        $response->assertSee('Media');
        $response->assertSee('Milestones');
        $response->assertSee('Clients');
        $response->assertSee('Contact Messages');
        
        // Sales should NOT see admin-only items
        $response->assertDontSee('Settings');
        $response->assertDontSee('Users');
    }

    public function test_navigation_has_active_state_indicator(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('bg-indigo-700'); // Active state class
    }

    public function test_navigation_is_responsive(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        // Mobile menu toggle button
        $response->assertSee('id="mobile-menu-button"', false);
        // Desktop navigation
        $response->assertSee('hidden md:block', false);
    }

    public function test_breadcrumb_shows_on_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Home');
        $response->assertSee('Dashboard');
        $response->assertSee('aria-label="Breadcrumb"', false);
    }

    public function test_user_menu_shows_current_user(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee($this->admin->name);
        $response->assertSee($this->admin->email);
    }

    public function test_user_menu_has_logout_option(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Logout');
        $response->assertSee('action="/logout"', false);
    }

    public function test_navigation_has_proper_aria_labels(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('aria-label="Main navigation"', false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_navigation_links_have_correct_routes(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('href="/admin/dashboard"', false);
        // Other routes will be added as we implement them
    }

    public function test_mobile_menu_toggle_works(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('x-data="{ mobileMenuOpen: false }"', false);
        $response->assertSee('@click="mobileMenuOpen = !mobileMenuOpen"', false);
    }

    public function test_navigation_shows_notification_badge(): void
    {
        // Create unhandled messages
        \App\Models\ContactMessage::factory()->count(3)->create(['handled' => false]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Contact Messages');
        $response->assertSee('<span class="badge">3</span>', false);
    }

    public function test_navigation_has_keyboard_accessibility(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        // All interactive elements should be keyboard accessible
        $response->assertSee('tabindex="0"', false);
        // Focus visible states
        $response->assertSee('focus:outline-none focus:ring-2', false);
    }
}