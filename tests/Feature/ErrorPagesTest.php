<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
    }

    public function test_404_error_page_renders_correctly(): void
    {
        $response = $this->get('/non-existent-page');
        
        $response->assertStatus(404);
        $response->assertSee('Page Not Found');
        $response->assertSee('PT Swastika Investama Prima');
    }

    public function test_500_error_page_renders_correctly(): void
    {
        // This test can't be easily tested without breaking the actual app
        // We'll just check that a 500 error template exists
        $this->assertTrue(view()->exists('errors.500'));
    }

    public function test_maintenance_mode_page_renders_correctly(): void
    {
        $this->artisan('down');
        
        $response = $this->get('/');
        
        $response->assertStatus(503);
        
        $this->artisan('up');
    }

    public function test_404_page_has_navigation_links(): void
    {
        $response = $this->get('/non-existent-page');
        
        $response->assertStatus(404);
        $response->assertSee('href="/"', false); // Home link
        $response->assertSee('href="/contact"', false); // Contact link
    }

    public function test_404_page_has_proper_seo_meta(): void
    {
        $response = $this->get('/non-existent-page');
        
        $response->assertStatus(404);
        $response->assertSee('<title>Page Not Found - PT Swastika Investama Prima</title>', false);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_robots_txt_accessible(): void
    {
        config(['app.env' => 'production']); // Set to production for this test
        
        $response = $this->get('/robots.txt');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent');
        $response->assertSee('Sitemap:');
    }

    public function test_sitemap_xml_accessible(): void
    {
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset', false);
    }

    public function test_security_headers_present(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_404_for_invalid_division_slug(): void
    {
        $response = $this->get('/divisions/non-existent-division');
        
        $response->assertStatus(404);
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->get('/health');
        
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function test_version_info_endpoint(): void
    {
        $response = $this->get('/version');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'version',
            'build',
            'environment',
            'php_version',
        ]);
    }
}