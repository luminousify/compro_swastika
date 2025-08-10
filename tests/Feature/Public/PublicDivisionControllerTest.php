<?php

namespace Tests\Feature\Public;

use App\Models\Division;
use App\Models\Product;
use App\Models\Technology;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDivisionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
    }

    public function test_divisions_index_page_renders_successfully(): void
    {
        $response = $this->get('/divisions');
        
        $response->assertStatus(200);
        $response->assertViewIs('divisions.index');
    }

    public function test_divisions_index_shows_all_divisions(): void
    {
        Division::factory()->create(['name' => 'Manufacturing Division']);
        Division::factory()->create(['name' => 'Technology Division']);
        
        $response = $this->get('/divisions');
        
        $response->assertSee('Manufacturing Division');
        $response->assertSee('Technology Division');
    }

    public function test_division_detail_page_renders_successfully(): void
    {
        $division = Division::factory()->create(['slug' => 'manufacturing']);
        
        $response = $this->get('/divisions/manufacturing');
        
        $response->assertStatus(200);
        $response->assertViewIs('divisions.show');
    }

    public function test_division_detail_shows_division_info(): void
    {
        $division = Division::factory()->create([
            'name' => 'Manufacturing Division',
            'slug' => 'manufacturing',
            'description' => 'We manufacture quality products',
        ]);
        
        $response = $this->get('/divisions/manufacturing');
        
        $response->assertSee('Manufacturing Division');
        $response->assertSee('We manufacture quality products');
    }

    public function test_division_detail_shows_products(): void
    {
        $division = Division::factory()->create(['slug' => 'manufacturing']);
        Product::factory()->create([
            'division_id' => $division->id,
            'name' => 'Product A',
            'description' => 'High quality product',
        ]);
        
        $response = $this->get('/divisions/manufacturing');
        
        $response->assertSee('Product A');
        $response->assertSee('High quality product');
    }

    public function test_division_detail_shows_technologies(): void
    {
        $division = Division::factory()->create(['slug' => 'tech']);
        Technology::factory()->create([
            'division_id' => $division->id,
            'name' => 'AI Technology',
            'description' => 'Advanced AI solutions',
        ]);
        
        $response = $this->get('/divisions/tech');
        
        $response->assertSee('AI Technology');
        $response->assertSee('Advanced AI solutions');
    }

    public function test_division_detail_shows_machines(): void
    {
        $division = Division::factory()->create(['slug' => 'production']);
        Machine::factory()->create([
            'division_id' => $division->id,
            'name' => 'CNC Machine',
            'description' => 'Precision cutting machine',
        ]);
        
        $response = $this->get('/divisions/production');
        
        $response->assertSee('CNC Machine');
        $response->assertSee('Precision cutting machine');
    }

    public function test_division_detail_hides_empty_tabs(): void
    {
        $division = Division::factory()->create(['slug' => 'empty-division']);
        // No products, technologies, or machines
        
        $response = $this->get('/divisions/empty-division');
        
        $response->assertStatus(200);
        // Should not show heading for empty content
        $response->assertDontSee('<h2 class="text-2xl font-semibold mb-4">Products</h2>', false);
        $response->assertDontSee('<h2 class="text-2xl font-semibold mb-4">Technologies</h2>', false);
        $response->assertDontSee('<h2 class="text-2xl font-semibold mb-4">Machines</h2>', false);
    }

    public function test_division_detail_shows_media(): void
    {
        $division = Division::factory()->create(['slug' => 'media-division']);
        Media::factory()->create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'caption' => 'Division photo',
        ]);
        
        $response = $this->get('/divisions/media-division');
        
        $response->assertSee('Division photo');
    }

    public function test_division_not_found_returns_404(): void
    {
        $response = $this->get('/divisions/non-existent');
        
        $response->assertStatus(404);
    }
}