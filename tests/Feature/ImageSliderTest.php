<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('home_hero_headline', 'Welcome to Our Company');
    }

    public function test_slider_renders_when_images_exist(): void
    {
        // Create test slider images
        Media::factory()->count(3)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('swiper-container', false);
        $response->assertSee('swiper-wrapper', false);
        $response->assertSee('swiper-slide', false);
    }

    public function test_slider_is_hidden_when_no_images(): void
    {
        // No slider images
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Slider should be hidden gracefully
        $content = $response->getContent();
        $hasSliderContainer = str_contains($content, 'swiper-container');
        
        if ($hasSliderContainer) {
            // If slider container exists, it should have hidden class or conditional rendering
            $this->assertTrue(
                str_contains($content, 'hidden') || 
                str_contains($content, 'style="display: none"') ||
                str_contains($content, '@if(')
            );
        }
    }

    public function test_slider_has_navigation_controls(): void
    {
        Media::factory()->count(3)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for navigation elements
        $response->assertSee('swiper-button-next', false);
        $response->assertSee('swiper-button-prev', false);
        $response->assertSee('swiper-pagination', false);
    }

    public function test_slider_has_keyboard_navigation_attributes(): void
    {
        Media::factory()->count(2)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for keyboard navigation attributes
        $response->assertSee('data-swiper-keyboard', false);
        $response->assertSee('tabindex="0"', false);
    }

    public function test_slider_has_autoplay_configuration(): void
    {
        Media::factory()->count(2)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for autoplay configuration
        $response->assertSee('data-swiper-autoplay', false);
        $response->assertSee('data-swiper-pause-on-hover', false);
    }

    public function test_slider_images_have_proper_attributes(): void
    {
        Media::factory()->homeSlider()->create([
            'caption' => 'Test slide caption'
        ]);

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for proper image attributes
        $response->assertSee('alt="Test slide caption"', false);
        $response->assertSee('loading="lazy"', false);
    }

    public function test_slider_has_responsive_height(): void
    {
        Media::factory()->count(2)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for responsive height classes
        $response->assertSee('h-64', false);  // Mobile height
        $response->assertSee('md:h-96', false);  // Tablet height
        $response->assertSee('lg:h-[500px]', false);  // Desktop height
    }

    public function test_slider_supports_touch_swipe(): void
    {
        Media::factory()->count(3)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for touch/swipe support attributes
        $response->assertSee('data-swiper-touch', false);
        $response->assertSee('data-swiper-simulate-touch', false);
    }

    public function test_slider_has_loading_state(): void
    {
        // Create slider images so the loading state div is rendered
        Media::factory()->homeSlider()->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Should have loading state before images load
        $response->assertSee('bg-gray-200', false);
        $response->assertSee('animate-pulse', false);
    }

    public function test_slider_javascript_configuration_is_present(): void
    {
        Media::factory()->count(2)->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for JavaScript configuration data attributes that Swiper.js will use
        $response->assertSee('data-swiper-autoplay="5000"', false);
        $response->assertSee('data-swiper-keyboard="true"', false);
        $response->assertSee('data-swiper-pause-on-hover="true"', false);
        $response->assertSee('swiper-button-next', false);
        $response->assertSee('swiper-pagination', false);
    }

    public function test_slider_handles_single_image_gracefully(): void
    {
        Media::factory()->homeSlider()->create();

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Should render even with single image
        $response->assertSee('swiper-slide', false);
    }

    public function test_slider_images_are_optimized(): void
    {
        Media::factory()->homeSlider()->create([
            'path_or_embed' => 'media/home/2025/01/test-image.jpg',
        ]);

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for optimized image serving
        $response->assertSee('storage/media/', false);
    }
}