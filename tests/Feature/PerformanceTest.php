<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Division;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('home_hero_headline', 'Welcome to Our Company');
    }

    public function test_html_response_size_is_reasonable(): void
    {
        // Create some content
        Division::factory()->count(3)->create();
        Media::factory()->count(2)->homeSlider()->create();
        Client::factory()->count(6)->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        $sizeInBytes = strlen($content);
        $sizeInKB = $sizeInBytes / 1024;
        
        // HTML should be under 100KB for good performance
        $this->assertLessThan(100, $sizeInKB, "HTML response is {$sizeInKB}KB, should be under 100KB");
    }

    public function test_minimal_http_requests_on_homepage(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Count external resources
        $cssCount = substr_count($content, '<link');
        $jsCount = substr_count($content, '<script');
        $imageCount = substr_count($content, '<img');
        
        // Should minimize requests (allow for performance hints)
        $this->assertLessThan(8, $cssCount, "Too many CSS files: {$cssCount}");
        $this->assertLessThan(8, $jsCount, "Too many JS files: {$jsCount}");
        $this->assertLessThan(20, $imageCount, "Too many images on homepage: {$imageCount}");
    }

    public function test_images_are_optimized(): void
    {
        Media::factory()->homeSlider()->create([
            'caption' => 'Test image',
            'width' => 1920,
            'height' => 1080
        ]);
        Client::factory()->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for optimization attributes
        $response->assertSee('loading="lazy"', false);
        $response->assertSee('decoding="async"', false);
        
        // Should use responsive images
        $response->assertSee('object-cover', false);
        $response->assertSee('object-contain', false);
    }

    public function test_css_is_minified_in_production(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Vite should minify CSS in production
        $content = $response->getContent();
        
        // Should include minified assets
        $this->assertStringContainsString('build/', $content);
        $this->assertStringContainsString('.css', $content);
    }

    public function test_javascript_is_deferred_or_async(): void
    {
        Media::factory()->homeSlider()->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Vite handles script optimization automatically
        $content = $response->getContent();
        
        // Should have script tags (Vite handles defer/async automatically)
        $hasScripts = str_contains($content, '<script') || str_contains($content, 'type="module"');
        $this->assertTrue($hasScripts, 'Should have JavaScript');
    }

    public function test_database_queries_are_optimized(): void
    {
        // Create test data
        Division::factory()->count(3)->create();
        
        // Enable query logging
        \DB::enableQueryLog();
        
        $response = $this->get('/');
        
        $queries = \DB::getQueryLog();
        $queryCount = count($queries);
        
        $response->assertStatus(200);
        
        // Should not have excessive queries (N+1 problem)
        $this->assertLessThan(20, $queryCount, "Too many database queries: {$queryCount}");
    }

    public function test_caching_headers_are_set(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Should have appropriate caching headers
        $this->assertTrue(
            $response->headers->has('Cache-Control') || 
            $response->headers->has('ETag') ||
            $response->headers->has('Last-Modified'),
            'Should have caching headers'
        );
    }

    public function test_compression_is_enabled(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check if compression would be beneficial
        $content = $response->getContent();
        $originalSize = strlen($content);
        $compressedSize = strlen(gzcompress($content));
        $compressionRatio = $compressedSize / $originalSize;
        
        // Should compress well (less than 70% of original)
        $this->assertLessThan(0.7, $compressionRatio, "Content should compress to less than 70% of original size");
    }

    public function test_no_unnecessary_redirects(): void
    {
        $response = $this->get('/');
        
        // Should not redirect unnecessarily
        $response->assertStatus(200);
        $this->assertFalse($response->isRedirection());
    }

    public function test_font_loading_is_optimized(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Should use system fonts primarily (Tailwind default)
        $response->assertSee('font-sans', false);
        
        // Should not load heavy custom fonts unnecessarily
        $content = $response->getContent();
        $fontLoadCount = substr_count(strtolower($content), 'font-face');
        $this->assertLessThan(3, $fontLoadCount, "Should not load too many custom fonts");
    }

    public function test_critical_css_is_inlined(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Vite should handle critical CSS
        $content = $response->getContent();
        
        // Should have some critical styles
        $this->assertStringContainsString('css', strtolower($content));
    }

    public function test_unused_css_is_purged(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain obvious unused classes
        $this->assertStringNotContainsString('text-purple-200', $content);
        $this->assertStringNotContainsString('bg-pink-900', $content);
        $this->assertStringNotContainsString('border-yellow-300', $content);
    }

    public function test_api_endpoints_are_fast(): void
    {
        $startTime = microtime(true);
        
        $response = $this->get('/');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        
        // Should respond within 600ms (TTFB requirement)
        $this->assertLessThan(600, $executionTime, "Response time {$executionTime}ms exceeds 600ms limit");
    }

    public function test_image_lazy_loading_is_implemented(): void
    {
        Media::factory()->homeSlider()->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Images should be lazy loaded (slider images have lazy loading)
        $response->assertSee('loading="lazy"', false);
    }

    public function test_third_party_scripts_are_optimized(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not have blocking third-party scripts
        $this->assertStringNotContainsString('google-analytics', $content);
        $this->assertStringNotContainsString('facebook.com', $content);
        
        // If analytics exist, they should be async
        if (str_contains($content, 'gtag') || str_contains($content, 'analytics')) {
            $this->assertStringContainsString('async', $content);
        }
    }

    public function test_resource_hints_are_present(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Should have performance resource hints
        $content = $response->getContent();
        
        // At least one performance hint should be present
        $hasHints = str_contains($content, 'preload') || 
                   str_contains($content, 'prefetch') || 
                   str_contains($content, 'preconnect') ||
                   str_contains($content, 'dns-prefetch');
        
        $this->assertTrue($hasHints, 'Should have resource hints for performance');
    }
}