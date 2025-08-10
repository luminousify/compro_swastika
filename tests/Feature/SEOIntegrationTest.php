<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Setting;
use App\Services\SEOService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SEOIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up default settings
        Setting::create([
            'data' => [
                'company_name' => 'PT. Daya Swastika Perkasa',
                'company_address' => 'Jl. Industrial No. 123, Jakarta',
                'company_phone' => '+62 21 1234567',
                'company_email' => 'info@dsp.com',
                'default_meta_description' => 'Leading industrial solutions provider',
                'logo_path' => 'images/logo.png',
                'visi' => 'Menjadi penyedia solusi industrial terkemuka',
                'misi' => 'Memberikan layanan terbaik untuk industri',
            ]
        ]);
        
        Config::set('app.url', 'https://dsp.com');
    }

    public function test_sitemap_xml_is_accessible(): void
    {
        Division::factory()->count(3)->create();
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
    }

    public function test_robots_txt_is_accessible(): void
    {
        $response = $this->get('/robots.txt');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *');
    }

    public function test_robots_txt_blocks_in_staging(): void
    {
        Config::set('app.env', 'staging');
        
        $response = $this->get('/robots.txt');
        
        $response->assertStatus(200);
        $response->assertSee('Disallow: /');
        $response->assertDontSee('Sitemap:');
    }

    public function test_robots_txt_allows_in_production(): void
    {
        Config::set('app.env', 'production');
        
        $response = $this->get('/robots.txt');
        
        $response->assertStatus(200);
        $response->assertSee('Allow: /');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Sitemap: https://dsp.com/sitemap.xml');
    }

    public function test_staging_pages_have_noindex_header(): void
    {
        Config::set('app.env', 'staging');
        
        $response = $this->get('/');
        
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_production_pages_dont_have_noindex_header(): void
    {
        Config::set('app.env', 'production');
        
        $response = $this->get('/');
        
        $response->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_sitemap_includes_all_divisions(): void
    {
        $divisions = [
            Division::factory()->create(['slug' => 'manufacturing']),
            Division::factory()->create(['slug' => 'engineering']),
            Division::factory()->create(['slug' => 'technology']),
        ];
        
        $response = $this->get('/sitemap.xml');
        
        foreach ($divisions as $division) {
            $response->assertSee('<loc>https://dsp.com/line-of-business/' . $division->slug . '</loc>', false);
        }
    }

    public function test_sitemap_includes_lastmod_for_divisions(): void
    {
        $division = Division::factory()->create([
            'slug' => 'test-division',
            'updated_at' => '2024-03-15 10:30:00',
        ]);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertSee('<lastmod>2024-03-15</lastmod>', false);
    }

    public function test_home_page_has_proper_meta_tags(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('<meta name="description" content="', false);
        $response->assertSee('<meta name="keywords" content="', false);
        $response->assertSee('<link rel="canonical" href="https://dsp.com"', false);
        $response->assertSee('<meta property="og:title" content="', false);
        $response->assertSee('<meta property="og:description" content="', false);
        $response->assertSee('<meta property="og:image" content="', false);
    }

    public function test_division_page_has_entity_specific_og_image(): void
    {
        $division = Division::factory()->create([
            'slug' => 'manufacturing',
            'name' => 'Manufacturing Division',
            'hero_image_path' => 'divisions/manufacturing-hero.jpg',
        ]);
        
        $response = $this->get('/line-of-business/manufacturing');
        
        $response->assertStatus(200);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('manufacturing-hero.jpg', false);
    }

    public function test_json_ld_structured_data_on_contact_page(): void
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertSee('<script type="application/ld+json">', false);
        $response->assertSee('"@type":"LocalBusiness"', false);
        $response->assertSee('"streetAddress":"Jl. Industrial No. 123, Jakarta"', false);
    }

    public function test_breadcrumb_schema_on_nested_pages(): void
    {
        $division = Division::factory()->create([
            'slug' => 'manufacturing',
            'name' => 'Manufacturing',
        ]);
        
        $response = $this->get('/line-of-business/manufacturing');
        
        $response->assertStatus(200);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        $response->assertSee('"position":1', false);
        $response->assertSee('"position":2', false);
    }

    public function test_canonical_url_strips_query_parameters(): void
    {
        $response = $this->get('/?utm_source=google&utm_campaign=test');
        
        $response->assertStatus(200);
        $response->assertSee('<link rel="canonical" href="https://dsp.com"', false);
        $response->assertDontSee('utm_source', false);
    }

    public function test_meta_titles_respect_length_constraints(): void
    {
        $response = $this->get('/');
        
        $content = $response->getContent();
        
        // Extract title from meta tag
        preg_match('/<title>(.*?)<\/title>/', $content, $matches);
        
        if (!empty($matches[1])) {
            $titleLength = strlen($matches[1]);
            $this->assertGreaterThanOrEqual(50, $titleLength);
            $this->assertLessThanOrEqual(60, $titleLength);
        }
    }

    public function test_meta_descriptions_respect_length_constraints(): void
    {
        $response = $this->get('/');
        
        $content = $response->getContent();
        
        // Extract description from meta tag
        preg_match('/<meta name="description" content="(.*?)"/', $content, $matches);
        
        if (!empty($matches[1])) {
            $descLength = strlen($matches[1]);
            $this->assertGreaterThanOrEqual(120, $descLength);
            $this->assertLessThanOrEqual(160, $descLength);
        }
    }

    public function test_twitter_card_tags_are_present(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('name="twitter:title"', false);
        $response->assertSee('name="twitter:description"', false);
        $response->assertSee('name="twitter:image"', false);
    }

    public function test_website_search_schema_on_home(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"potentialAction"', false);
        $response->assertSee('"@type":"SearchAction"', false);
    }

    public function test_division_slugs_are_validated(): void
    {
        $seoService = new SEOService();
        
        // Valid slugs
        $this->assertTrue($seoService->isValidSlug('manufacturing'));
        $this->assertTrue($seoService->isValidSlug('food-beverage'));
        $this->assertTrue($seoService->isValidSlug('division-123'));
        
        // Invalid slugs
        $this->assertFalse($seoService->isValidSlug('Manufacturing')); // uppercase
        $this->assertFalse($seoService->isValidSlug('division_name')); // underscore
        $this->assertFalse($seoService->isValidSlug('division name')); // space
        $this->assertFalse($seoService->isValidSlug('division@123')); // special char
    }

    public function test_sitemap_regenerates_when_content_changes(): void
    {
        // Initial sitemap
        $response1 = $this->get('/sitemap.xml');
        $response1->assertDontSee('new-division');
        
        // Add new division
        Division::factory()->create(['slug' => 'new-division']);
        
        // Clear any caching
        cache()->forget('sitemap:xml');
        
        // New sitemap should include the new division
        $response2 = $this->get('/sitemap.xml');
        $response2->assertSee('new-division');
    }
}