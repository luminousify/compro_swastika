<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Division;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Technology;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_sales_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        
        $response->assertRedirect('/login');
    }

    public function test_dashboard_displays_content_counts(): void
    {
        // Create test data
        $divisions = Division::factory()->count(5)->create();
        
        // Create products, technologies, and machines using existing divisions
        foreach ($divisions as $division) {
            Product::factory()->count(2)->create(['division_id' => $division->id]);
            Technology::factory()->count(1)->create(['division_id' => $division->id]);
            Machine::factory()->count(1)->create(['division_id' => $division->id]);
        }
        
        // Create additional products for remaining counts
        Technology::factory()->count(3)->create(['division_id' => $divisions->first()->id]);
        Machine::factory()->count(1)->create(['division_id' => $divisions->first()->id]);
        
        // Create media using existing divisions
        foreach ($divisions as $index => $division) {
            Media::factory()->count(3)->create([
                'mediable_type' => Division::class,
                'mediable_id' => $division->id,
            ]);
        }
        
        Milestone::factory()->count(12)->create();
        Client::factory()->count(9)->create();
        ContactMessage::factory()->count(3)->create(['handled' => false]);
        ContactMessage::factory()->count(2)->create(['handled' => true]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(5, $stats['divisions']);
        $this->assertEquals(24, $stats['products']); // 10 + 8 + 6 (products + technologies + machines)
        $this->assertEquals(15, $stats['media']);
        $this->assertEquals(12, $stats['milestones']);
        $this->assertEquals(9, $stats['clients']);
        $this->assertEquals(3, $stats['unhandled_messages']);
    }

    public function test_dashboard_displays_recent_contact_messages(): void
    {
        // Create older messages
        ContactMessage::factory()->count(3)->create([
            'created_at' => now()->subDays(10),
        ]);
        
        // Create recent messages
        $recentMessages = ContactMessage::factory()->count(5)->create([
            'handled' => false,
            'created_at' => now(),
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('recentMessages');
        
        $messages = $response->viewData('recentMessages');
        $this->assertCount(5, $messages);
        $this->assertEquals($recentMessages->pluck('id')->sort()->values()->toArray(), 
                           $messages->pluck('id')->sort()->values()->toArray());
    }

    public function test_dashboard_shows_system_status(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('systemStatus');
        
        $status = $response->viewData('systemStatus');
        $this->assertArrayHasKey('php_version', $status);
        $this->assertArrayHasKey('laravel_version', $status);
        $this->assertArrayHasKey('database_connection', $status);
        $this->assertArrayHasKey('cache_driver', $status);
        $this->assertArrayHasKey('storage_link', $status);
    }

    public function test_dashboard_displays_quick_actions_based_on_role(): void
    {
        // Test for admin
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('quickActions');
        
        $adminActions = $response->viewData('quickActions');
        $this->assertContains('admin.settings.edit', array_column($adminActions, 'route'));
        // Users route is null for now, will be added in Task 9
        
        // Test for sales
        $response = $this->actingAs($this->sales)->get('/admin/dashboard');
        $response->assertStatus(200);
        
        $salesActions = $response->viewData('quickActions');
        $this->assertNotContains('admin.settings.edit', array_column($salesActions, 'route'));
        // Users route is null for now, will be added in Task 9
    }

    public function test_dashboard_shows_activity_timeline(): void
    {
        // Create some recent activity
        $division = Division::factory()->create();
        $product = Product::factory()->create();
        $client = Client::factory()->create();
        
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('recentActivity');
        
        $activity = $response->viewData('recentActivity');
        $this->assertNotEmpty($activity);
    }

    public function test_dashboard_has_responsive_layout(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('sm:grid-cols-2');
        $response->assertSee('lg:grid-cols-3');
        $response->assertSee('lg:grid-cols-2');
    }

    public function test_dashboard_includes_skip_to_content_link(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Skip to content');
        $response->assertSee('#main-content');
    }

    public function test_dashboard_has_proper_page_title(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('<title>Dashboard - Admin Panel</title>', false);
    }

    public function test_dashboard_shows_greeting_with_user_name(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Welcome back, ' . $this->admin->name);
    }

    public function test_user_with_forced_password_change_redirected(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'force_password_change' => true,
        ]);
        
        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertRedirect('/change-password');
    }

    public function test_dashboard_performance_with_large_dataset(): void
    {
        // Create large dataset
        Division::factory()->count(50)->create();
        ContactMessage::factory()->count(100)->create();
        
        $startTime = microtime(true);
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $endTime = microtime(true);
        
        $response->assertStatus(200);
        
        // Response time should be under 1 second even with large dataset
        $this->assertLessThan(1.0, $endTime - $startTime);
    }
}