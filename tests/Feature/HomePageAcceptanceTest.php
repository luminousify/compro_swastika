<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that UAT instance renders Home page without manual database setup
     */
    public function test_home_page_renders_without_manual_database_setup(): void
    {
        // Run seeders to simulate fresh UAT deployment
        $this->seed();

        // Test that home page loads successfully
        $response = $this->get('/');

        $response->assertStatus(200);

        // Verify essential content is present
        $response->assertSee('PT. Daya Swastika Perkasa');
        $response->assertSee('Solusi Terpadu untuk Industri dan Otomotif');

        // Verify slider content is present
        $response->assertSee('Our Services');
        $response->assertSee('Grundfos Pump Solutions');

        // Verify about section is present
        $response->assertSee('About Us');

        // Verify clients section is present
        $response->assertSee('Our Clients');
        $response->assertSee('PT. Paramount Bed Indonesia');
    }

    /**
     * Test that home page handles empty slider gracefully
     */
    public function test_home_page_handles_empty_slider_gracefully(): void
    {
        // Run only basic seeders without media
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\SettingSeeder::class);
        $this->seed(\Database\Seeders\ClientSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PT. Daya Swastika Perkasa');

        // Should not show slider section when no images
        $response->assertDontSee('Our Services');
    }

    /**
     * Test that home page shows maximum 12 clients
     */
    public function test_home_page_shows_maximum_twelve_clients(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertStatus(200);

        // Count client names in response - should not exceed 12
        $content = $response->getContent();
        $clientCount = substr_count($content, 'class="client"');

        $this->assertLessThanOrEqual(12, $clientCount);
    }
}
