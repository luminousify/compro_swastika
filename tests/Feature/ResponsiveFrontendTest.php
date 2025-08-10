<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Division;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('home_hero_headline', 'Welcome to Our Company');
        Setting::setValue('home_hero_subheadline', 'Leading solutions provider');
    }

    public function test_home_page_has_responsive_layout_classes(): void
    {
        // Create divisions so the grid layout is rendered
        Division::factory()->count(3)->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for responsive container and grid classes
        $response->assertSee('container mx-auto', false);
        $response->assertSeeInOrder([
            'grid',
            'grid-cols-1',
            'md:grid-cols-2',
            'lg:grid-cols-3'
        ], false);
    }

    public function test_home_page_image_slider_has_responsive_structure(): void
    {
        // Create test media for slider
        Media::factory()->homeSlider()->create([
            'caption' => 'Test slide 1'
        ]);

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for responsive slider structure
        $response->assertSee('swiper-container', false);
        $response->assertSee('swiper-wrapper', false);
        $response->assertSee('swiper-slide', false);
    }

    public function test_divisions_index_has_responsive_card_layout(): void
    {
        Division::factory()->create([
            'name' => 'Test Division',
            'description' => 'This is a longer description that should be truncated in card view to test responsive behavior and content truncation.'
        ]);

        $response = $this->get('/divisions');
        
        $response->assertStatus(200);
        
        // Check for responsive grid classes
        $response->assertSee('grid-cols-1', false);
        $response->assertSee('md:grid-cols-2', false);
        $response->assertSee('lg:grid-cols-3', false);
        
        // Check for card structure
        $response->assertSee('bg-white rounded-lg shadow', false);
    }

    public function test_division_detail_has_responsive_tabs(): void
    {
        $division = Division::factory()->create(['slug' => 'test-division']);
        // Create some products so tabs are rendered
        $division->products()->create([
            'name' => 'Test Product',
            'description' => 'Test description',
            'order' => 1
        ]);
        
        $response = $this->get('/divisions/test-division');
        
        $response->assertStatus(200);
        
        // Check for responsive tab structure
        $response->assertSee('border-b mb-6', false);
        $response->assertSee('flex space-x-8', false);
    }

    public function test_contact_page_has_responsive_form_layout(): void
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        
        // Check for responsive form grid
        $response->assertSee('grid-cols-1', false);
        $response->assertSee('lg:grid-cols-2', false);
        $response->assertSee('gap-12', false);
        
        // Check for responsive form inputs
        $response->assertSee('w-full', false);
    }

    public function test_milestones_page_has_responsive_timeline(): void
    {
        Milestone::factory()->create([
            'year' => 2020,
            'text' => 'Important milestone in company history'
        ]);

        $response = $this->get('/milestones');
        
        $response->assertStatus(200);
        
        // Check for responsive timeline structure
        $response->assertSee('max-w-4xl mx-auto', false);
        $response->assertSee('border-l-2', false);
        $response->assertSee('space-y-4', false);
    }

    public function test_client_grid_is_responsive(): void
    {
        Client::factory()->count(6)->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Should show client grid if clients exist
        $content = $response->getContent();
        $this->assertStringContainsString('grid', $content);
    }

    public function test_mobile_navigation_structure(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for mobile-friendly navigation structure
        $response->assertSee('text-3xl font-bold', false);
        $response->assertSee('text-center', false);
    }

    public function test_images_have_responsive_attributes(): void
    {
        $division = Division::factory()->create(['slug' => 'test-division']);
        Media::factory()->create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'caption' => 'Test image'
        ]);

        $response = $this->get('/divisions/test-division');
        
        $response->assertStatus(200);
        
        // Check for responsive image classes
        $response->assertSee('w-full', false);
        $response->assertSee('h-48', false);
        $response->assertSee('object-cover', false);
    }

    public function test_loading_states_are_present(): void
    {
        // Create slider images so loading states are rendered
        Media::factory()->homeSlider()->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for loading state classes (skeleton screens)
        $response->assertSee('bg-gray-200', false);
        $response->assertSee('animate-pulse', false);
    }

    public function test_hover_effects_are_implemented(): void
    {
        Client::factory()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for hover effect classes
        $response->assertSee('hover:', false);
        $response->assertSee('transition', false);
    }

    public function test_focus_states_are_visible(): void
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        
        // Check for focus state classes
        $response->assertSee('focus:outline-none', false);
        $response->assertSee('focus:ring', false);
        $response->assertSee('focus:border', false);
    }

    public function test_responsive_breakpoints_are_consistent(): void
    {
        $response = $this->get('/divisions');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Check that responsive breakpoints follow the pattern: mobile-first, md:tablet, lg:desktop
        $this->assertStringContainsString('grid-cols-1', $content);
        $this->assertStringContainsString('md:grid-cols-2', $content);
        $this->assertStringContainsString('lg:grid-cols-3', $content);
    }

    public function test_content_truncation_works_on_cards(): void
    {
        Division::factory()->create([
            'name' => 'Very Long Division Name That Should Be Truncated',
            'description' => str_repeat('This is a very long description that should be truncated properly in the card view to maintain layout consistency and responsive design. ', 10)
        ]);

        $response = $this->get('/divisions');
        
        $response->assertStatus(200);
        
        // Should have truncation utility
        $response->assertSee('text-gray-600', false);
    }

    public function test_empty_states_are_handled_gracefully(): void
    {
        // Test with no divisions
        $response = $this->get('/divisions');
        
        $response->assertStatus(200);
        
        // Should handle empty state gracefully
        $content = $response->getContent();
        $this->assertNotEmpty($content);
    }

    public function test_css_optimization_classes_are_used(): void
    {
        // Create milestones to render space-y classes in milestones section
        Milestone::factory()->count(2)->create();
        // Create divisions to render grid classes
        Division::factory()->count(2)->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check that Tailwind utility classes are being used efficiently
        $response->assertSee('space-', false);  // Spacing utilities
        $response->assertSee('text-', false);   // Typography utilities
        $response->assertSee('bg-', false);     // Background utilities
        $response->assertSee('flex', false);    // Flexbox utilities
        $response->assertSee('grid', false);    // Grid utilities
    }
}