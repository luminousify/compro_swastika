<?php

namespace Tests\Feature\Public;

use App\Models\Client;
use App\Models\Division;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic settings
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('home_hero_headline', 'Welcome to Our Company');
        Setting::setValue('home_hero_subheadline', 'Leading provider of industrial solutions');
        Setting::setValue('about_snippet', 'We are a leading company with over 20 years of experience.');
    }

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    public function test_home_page_shows_hero_content(): void
    {
        $response = $this->get('/');
        
        $response->assertSee('Welcome to Our Company');
        $response->assertSee('Leading provider of industrial solutions');
    }

    public function test_home_page_shows_slider_images(): void
    {
        // Create slider images
        $slider1 = Media::factory()->create([
            'mediable_type' => 'home_slider',
            'mediable_id' => 1,
            'flags' => ['home_slider' => true],
            'caption' => 'Slide 1',
        ]);
        
        $slider2 = Media::factory()->create([
            'mediable_type' => 'home_slider',
            'mediable_id' => 1,
            'flags' => ['home_slider' => true],
            'caption' => 'Slide 2',
        ]);
        
        $response = $this->get('/');
        
        $response->assertSee('Slide 1');
        $response->assertSee('Slide 2');
    }

    public function test_home_page_handles_no_slider_images_gracefully(): void
    {
        // No slider images created
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // Should not show slider section when no images
        $response->assertDontSee('class="slider"');
    }

    public function test_home_page_shows_about_snippet(): void
    {
        $response = $this->get('/');
        
        $response->assertSee('We are a leading company with over 20 years of experience.');
    }

    public function test_home_page_shows_maximum_12_clients(): void
    {
        // Create 15 clients
        Client::factory()->count(15)->create();
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Get the displayed clients from view data
        $clients = $response->viewData('clients');
        $this->assertCount(12, $clients);
    }

    public function test_home_page_shows_clients_in_order(): void
    {
        $client1 = Client::factory()->create(['name' => 'Client A', 'order' => 1]);
        $client2 = Client::factory()->create(['name' => 'Client B', 'order' => 2]);
        $client3 = Client::factory()->create(['name' => 'Client C', 'order' => 3]);
        
        $response = $this->get('/');
        
        $content = $response->getContent();
        $posA = strpos($content, 'Client A');
        $posB = strpos($content, 'Client B');
        $posC = strpos($content, 'Client C');
        
        // Client A should appear before B and C
        $this->assertLessThan($posB, $posA);
        $this->assertLessThan($posC, $posB);
    }

    public function test_home_page_shows_divisions(): void
    {
        Division::factory()->create(['name' => 'Manufacturing Division']);
        Division::factory()->create(['name' => 'Technology Division']);
        
        $response = $this->get('/');
        
        $response->assertSee('Manufacturing Division');
        $response->assertSee('Technology Division');
    }

    public function test_home_page_shows_recent_milestones(): void
    {
        Milestone::factory()->create(['year' => 2023, 'text' => 'Expanded to new markets']);
        Milestone::factory()->create(['year' => 2022, 'text' => 'Launched new product line']);
        
        $response = $this->get('/');
        
        $response->assertSee('2023');
        $response->assertSee('2022');
    }

    public function test_home_page_has_seo_meta_tags(): void
    {
        Setting::setValue('meta_description', 'Leading industrial solutions provider in Indonesia');
        
        $response = $this->get('/');
        
        $response->assertSee('<meta name="description" content="Leading industrial solutions provider in Indonesia">', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:description"', false);
    }

    public function test_home_page_uses_caching(): void
    {
        // Just verify the controller uses caching
        $response = $this->get('/');
        $response->assertStatus(200);
        
        // The cache key should exist after first request
        $this->assertTrue(\Cache::has('home:v1'));
    }

    public function test_home_page_shows_contact_cta(): void
    {
        $response = $this->get('/');
        
        $response->assertSee('Contact Us');
        $response->assertSee('href="/contact"', false);
    }

    public function test_home_page_is_responsive(): void
    {
        $response = $this->get('/');
        
        // Check for responsive viewport meta tag
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0">', false);
        
        // The page should be set up for responsive design
        $response->assertStatus(200);
    }
}