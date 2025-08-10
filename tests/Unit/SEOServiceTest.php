<?php

namespace Tests\Unit;

use App\Models\Division;
use App\Models\Setting;
use App\Services\SEOService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SEOServiceTest extends TestCase
{
    use RefreshDatabase;

    private SEOService $seoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seoService = new SEOService();
        
        // Set up default settings
        Setting::create([
            'data' => [
                'company_name' => 'PT. Daya Swastika Perkasa',
                'company_address' => 'Jl. Test No. 123, Jakarta',
                'company_phone' => '+62 21 1234567',
                'company_email' => 'info@dsp.com',
                'default_meta_description' => 'Leading industrial solutions provider in Indonesia',
                'logo_path' => 'images/logo.png',
            ]
        ]);
    }

    /**
     * Meta Tag Generation Tests
     */
    public function test_generates_meta_tags_with_correct_length_constraints(): void
    {
        $tags = $this->seoService->generateMetaTags('home', [
            'title' => 'PT. Daya Swastika Perkasa - Industrial Solutions Provider in Indonesia and Southeast Asia Region',
            'description' => 'PT. Daya Swastika Perkasa is a leading industrial solutions provider offering comprehensive engineering services, machinery, and technology solutions for various industries across Indonesia and Southeast Asia. We specialize in manufacturing, automation, and industrial equipment supply.',
        ]);

        // Title should be truncated to 50-60 chars
        $this->assertLessThanOrEqual(60, strlen($tags['title']));
        $this->assertGreaterThanOrEqual(50, strlen($tags['title']));
        
        // Description should be truncated to 120-160 chars
        $this->assertLessThanOrEqual(160, strlen($tags['description']));
        $this->assertGreaterThanOrEqual(120, strlen($tags['description']));
        
        // Should preserve meaningful content when truncating
        $this->assertStringContainsString('PT. Daya Swastika Perkasa', $tags['title']);
    }

    public function test_generates_default_meta_tags_when_no_data_provided(): void
    {
        $tags = $this->seoService->generateMetaTags('home');

        $this->assertArrayHasKey('title', $tags);
        $this->assertArrayHasKey('description', $tags);
        $this->assertArrayHasKey('keywords', $tags);
        $this->assertNotEmpty($tags['title']);
        $this->assertNotEmpty($tags['description']);
    }

    public function test_generates_page_specific_meta_tags(): void
    {
        $pages = [
            'home' => 'Beranda',
            'visi-misi' => 'Visi & Misi',
            'milestones' => 'Milestones',
            'line-of-business' => 'Line of Business',
            'contact' => 'Hubungi Kami',
        ];

        foreach ($pages as $page => $expectedInTitle) {
            $tags = $this->seoService->generateMetaTags($page);
            $this->assertStringContainsString($expectedInTitle, $tags['title']);
        }
    }

    public function test_includes_canonical_url(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        $tags = $this->seoService->generateMetaTags('home');
        
        $this->assertArrayHasKey('canonical', $tags);
        $this->assertEquals('https://dsp.com', $tags['canonical']);
        
        $tags = $this->seoService->generateMetaTags('visi-misi');
        $this->assertEquals('https://dsp.com/visi-misi', $tags['canonical']);
    }

    /**
     * Open Graph Tests
     */
    public function test_generates_open_graph_tags(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        $ogTags = $this->seoService->generateOpenGraphTags('home', [
            'title' => 'PT. Daya Swastika Perkasa',
            'description' => 'Leading industrial solutions provider',
            'image' => 'images/hero.jpg',
        ]);

        $this->assertArrayHasKey('og:title', $ogTags);
        $this->assertArrayHasKey('og:description', $ogTags);
        $this->assertArrayHasKey('og:image', $ogTags);
        $this->assertArrayHasKey('og:url', $ogTags);
        $this->assertArrayHasKey('og:type', $ogTags);
        $this->assertArrayHasKey('og:site_name', $ogTags);
        
        $this->assertEquals('PT. Daya Swastika Perkasa', $ogTags['og:title']);
        $this->assertEquals('https://dsp.com/storage/images/hero.jpg', $ogTags['og:image']);
    }

    public function test_uses_division_hero_image_for_open_graph(): void
    {
        $division = Division::factory()->create([
            'slug' => 'manufacturing',
            'name' => 'Manufacturing Division',
            'hero_image_path' => 'divisions/manufacturing-hero.jpg',
        ]);

        $ogTags = $this->seoService->generateOpenGraphTags('division', [
            'entity' => $division,
            'title' => 'Manufacturing Division',
        ]);

        $this->assertStringContainsString('manufacturing-hero.jpg', $ogTags['og:image']);
        $this->assertEquals('Manufacturing Division', $ogTags['og:title']);
    }

    public function test_falls_back_to_company_logo_when_no_image(): void
    {
        $ogTags = $this->seoService->generateOpenGraphTags('contact');

        $this->assertStringContainsString('logo.png', $ogTags['og:image']);
    }

    public function test_includes_twitter_card_tags(): void
    {
        $ogTags = $this->seoService->generateOpenGraphTags('home', [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'image' => 'test.jpg',
        ]);

        $this->assertArrayHasKey('twitter:card', $ogTags);
        $this->assertArrayHasKey('twitter:title', $ogTags);
        $this->assertArrayHasKey('twitter:description', $ogTags);
        $this->assertArrayHasKey('twitter:image', $ogTags);
        
        $this->assertEquals('summary_large_image', $ogTags['twitter:card']);
    }

    /**
     * JSON-LD Structured Data Tests
     */
    public function test_generates_organization_schema(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        // For non-contact pages, it should be Organization
        $jsonLd = $this->seoService->generateJsonLD('home');
        
        $this->assertIsArray($jsonLd);
        $this->assertEquals('Organization', $jsonLd['@type']);
        $this->assertEquals('PT. Daya Swastika Perkasa', $jsonLd['name']);
        $this->assertArrayHasKey('url', $jsonLd);
        $this->assertArrayHasKey('logo', $jsonLd);
        $this->assertArrayHasKey('contactPoint', $jsonLd);
        
        $contactPoint = $jsonLd['contactPoint'];
        $this->assertEquals('ContactPoint', $contactPoint['@type']);
        $this->assertEquals('+62 21 1234567', $contactPoint['telephone']);
        $this->assertEquals('customer-service', $contactPoint['contactType']);
    }

    public function test_generates_local_business_schema_when_address_present(): void
    {
        $jsonLd = $this->seoService->generateJsonLD('contact', [
            'includeLocalBusiness' => true,
        ]);

        $this->assertEquals('LocalBusiness', $jsonLd['@type']);
        $this->assertArrayHasKey('address', $jsonLd);
        
        $address = $jsonLd['address'];
        $this->assertEquals('PostalAddress', $address['@type']);
        $this->assertEquals('Jl. Test No. 123, Jakarta', $address['streetAddress']);
        $this->assertEquals('Indonesia', $address['addressCountry']);
    }

    public function test_generates_breadcrumb_schema(): void
    {
        $breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Line of Business', 'url' => '/line-of-business'],
            ['name' => 'Manufacturing', 'url' => '/line-of-business/manufacturing'],
        ];

        $jsonLd = $this->seoService->generateBreadcrumbSchema($breadcrumbs);

        $this->assertEquals('BreadcrumbList', $jsonLd['@type']);
        $this->assertCount(3, $jsonLd['itemListElement']);
        
        $firstItem = $jsonLd['itemListElement'][0];
        $this->assertEquals('ListItem', $firstItem['@type']);
        $this->assertEquals(1, $firstItem['position']);
        $this->assertEquals('Beranda', $firstItem['name']);
    }

    public function test_generates_website_search_schema(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        $jsonLd = $this->seoService->generateWebsiteSearchSchema();

        $this->assertEquals('WebSite', $jsonLd['@type']);
        $this->assertArrayHasKey('potentialAction', $jsonLd);
        
        $searchAction = $jsonLd['potentialAction'];
        $this->assertEquals('SearchAction', $searchAction['@type']);
        $this->assertEquals('https://dsp.com/search?q={search_term_string}', $searchAction['target']);
    }

    /**
     * Sitemap Generation Tests
     */
    public function test_generates_sitemap_with_all_routes(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        // Create divisions with unique slugs
        Division::factory()->create(['slug' => 'division-1']);
        Division::factory()->create(['slug' => 'division-2']);
        Division::factory()->create(['slug' => 'division-3']);

        $sitemap = $this->seoService->generateSitemap();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $sitemap);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $sitemap);
        
        // Check static pages
        $this->assertStringContainsString('<loc>https://dsp.com/</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://dsp.com/visi-misi</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://dsp.com/milestones</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://dsp.com/line-of-business</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://dsp.com/contact</loc>', $sitemap);
        
        // Check priority values
        $this->assertStringContainsString('<priority>1.0</priority>', $sitemap); // Home
        $this->assertStringContainsString('<priority>0.9</priority>', $sitemap); // Line of Business
        $this->assertStringContainsString('<priority>0.8</priority>', $sitemap); // Other pages
    }

    public function test_includes_division_urls_in_sitemap(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        $division = Division::factory()->create([
            'slug' => 'manufacturing',
            'updated_at' => '2024-01-15 10:00:00',
        ]);

        $sitemap = $this->seoService->generateSitemap();

        $this->assertStringContainsString('<loc>https://dsp.com/line-of-business/manufacturing</loc>', $sitemap);
        $this->assertStringContainsString('<lastmod>2024-01-15</lastmod>', $sitemap);
        $this->assertStringContainsString('<changefreq>weekly</changefreq>', $sitemap);
    }

    public function test_escapes_special_characters_in_sitemap(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        Division::factory()->create([
            'slug' => 'food-beverage',
            'name' => 'Food & Beverage Division',
        ]);

        $sitemap = $this->seoService->generateSitemap();

        // URLs should be properly encoded
        $this->assertStringContainsString('<loc>https://dsp.com/line-of-business/food-beverage</loc>', $sitemap);
        $this->assertStringNotContainsString('&', $sitemap); // Should be escaped as &amp; if in content
    }

    /**
     * Staging Protection Tests
     */
    public function test_adds_noindex_header_in_staging(): void
    {
        Config::set('app.env', 'staging');
        
        $headers = $this->seoService->getRobotHeaders();
        
        $this->assertArrayHasKey('X-Robots-Tag', $headers);
        $this->assertEquals('noindex, nofollow', $headers['X-Robots-Tag']);
    }

    public function test_no_robot_headers_in_production(): void
    {
        Config::set('app.env', 'production');
        
        $headers = $this->seoService->getRobotHeaders();
        
        $this->assertEmpty($headers);
    }

    public function test_generates_staging_robots_txt(): void
    {
        Config::set('app.env', 'staging');
        
        $robotsTxt = $this->seoService->generateRobotsTxt();
        
        $this->assertStringContainsString('User-agent: *', $robotsTxt);
        $this->assertStringContainsString('Disallow: /', $robotsTxt);
    }

    public function test_generates_production_robots_txt(): void
    {
        Config::set('app.env', 'production');
        Config::set('app.url', 'https://dsp.com');
        
        $robotsTxt = $this->seoService->generateRobotsTxt();
        
        $this->assertStringContainsString('User-agent: *', $robotsTxt);
        $this->assertStringContainsString('Allow: /', $robotsTxt);
        $this->assertStringContainsString('Sitemap: https://dsp.com/sitemap.xml', $robotsTxt);
        $this->assertStringContainsString('Disallow: /admin', $robotsTxt);
        $this->assertStringContainsString('Disallow: /api', $robotsTxt);
    }

    /**
     * Canonical URL Tests
     */
    public function test_generates_canonical_urls_correctly(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        $this->assertEquals('https://dsp.com', $this->seoService->getCanonicalUrl('/'));
        $this->assertEquals('https://dsp.com/visi-misi', $this->seoService->getCanonicalUrl('visi-misi'));
        $this->assertEquals('https://dsp.com/line-of-business/manufacturing', 
            $this->seoService->getCanonicalUrl('line-of-business/manufacturing'));
    }

    public function test_handles_query_parameters_in_canonical(): void
    {
        Config::set('app.url', 'https://dsp.com');
        
        // Should strip query parameters by default
        $this->assertEquals('https://dsp.com/products', 
            $this->seoService->getCanonicalUrl('products?page=2&sort=name'));
        
        // Unless explicitly told to keep them
        $this->assertEquals('https://dsp.com/search?q=test', 
            $this->seoService->getCanonicalUrl('search?q=test', true));
    }

    public function test_validates_slug_format(): void
    {
        $this->assertTrue($this->seoService->isValidSlug('valid-slug'));
        $this->assertTrue($this->seoService->isValidSlug('slug-with-123-numbers'));
        
        $this->assertFalse($this->seoService->isValidSlug('Invalid Slug'));
        $this->assertFalse($this->seoService->isValidSlug('slug_with_underscores'));
        $this->assertFalse($this->seoService->isValidSlug('UPPERCASE-SLUG'));
        $this->assertFalse($this->seoService->isValidSlug('slug-with-special-@#$'));
    }

    /**
     * Helper Method Tests
     */
    public function test_truncates_text_intelligently(): void
    {
        $text = 'This is a very long text that needs to be truncated at a specific length without breaking words';
        
        $truncated = $this->seoService->truncateText($text, 50);
        
        $this->assertLessThanOrEqual(50, strlen($truncated));
        $this->assertStringEndsWith('...', $truncated);
        // Should not break in the middle of a word
        $this->assertStringNotContainsString('truncat ', $truncated);
    }

    public function test_generates_keywords_from_content(): void
    {
        $content = 'PT. Daya Swastika Perkasa provides industrial solutions, machinery, and engineering services';
        
        $keywords = $this->seoService->generateKeywords($content);
        
        $this->assertStringContainsString('daya swastika perkasa', $keywords);
        $this->assertStringContainsString('industrial', $keywords);
        $this->assertStringContainsString('machinery', $keywords);
        $this->assertStringContainsString('engineering', $keywords);
    }
}