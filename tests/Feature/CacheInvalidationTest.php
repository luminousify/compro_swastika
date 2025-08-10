<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_creating_client_invalidates_home_cache(): void
    {
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        Client::create([
            'name' => 'Test Client',
            'logo_path' => 'clients/test.png',
            'order' => 1,
        ]);
        
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_updating_client_invalidates_home_cache(): void
    {
        $client = Client::create([
            'name' => 'Test Client',
            'logo_path' => 'clients/test.png',
            'order' => 1,
        ]);
        
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        $client->update(['name' => 'Updated Client']);
        
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_deleting_client_invalidates_home_cache(): void
    {
        $client = Client::create([
            'name' => 'Test Client',
            'logo_path' => 'clients/test.png',
            'order' => 1,
        ]);
        
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        $client->delete();
        
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_creating_home_slider_media_invalidates_home_cache(): void
    {
        $division = Division::factory()->create();
        $user = \App\Models\User::factory()->create();
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => 'image',
            'path_or_embed' => 'media/test.jpg',
            'is_home_slider' => true,
            'order' => 1,
            'uploaded_by' => $user->id,
        ]);
        
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_updating_settings_invalidates_home_and_settings_cache(): void
    {
        $setting = Setting::create(['data' => ['company_name' => 'Test Company']]);
        
        Cache::put('home:v1', ['test' => 'data'], 60);
        Cache::put('settings:all', ['test' => 'data'], 60);
        Cache::put('visi-misi:v1', ['test' => 'data'], 60);
        
        $setting->update(['data' => ['company_name' => 'Updated Company']]);
        
        $this->assertNull(Cache::get('home:v1'));
        $this->assertNull(Cache::get('settings:all'));
        $this->assertNull(Cache::get('visi-misi:v1'));
    }

    public function test_creating_division_invalidates_divisions_cache(): void
    {
        Cache::put('divisions:index', ['test' => 'data'], 60);
        
        Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'description' => 'Test Description',
            'order' => 1,
        ]);
        
        $this->assertNull(Cache::get('divisions:index'));
    }

    public function test_updating_division_invalidates_division_and_index_cache(): void
    {
        $division = Division::factory()->create(['slug' => 'test-division']);
        
        Cache::put('divisions:index', ['test' => 'data'], 60);
        Cache::put('division:test-division', ['test' => 'data'], 60);
        
        $division->update(['name' => 'Updated Division']);
        
        $this->assertNull(Cache::get('divisions:index'));
        $this->assertNull(Cache::get('division:test-division'));
    }

    public function test_creating_product_invalidates_divisions_cache(): void
    {
        $division = Division::factory()->create();
        Cache::put('divisions:index', ['test' => 'data'], 60);
        Cache::put("division:{$division->slug}", ['test' => 'data'], 60);
        
        Product::create([
            'division_id' => $division->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'order' => 1,
        ]);
        
        $this->assertNull(Cache::get('divisions:index'));
        $this->assertNull(Cache::get("division:{$division->slug}"));
    }

    public function test_creating_technology_invalidates_divisions_cache(): void
    {
        $division = Division::factory()->create();
        Cache::put('divisions:index', ['test' => 'data'], 60);
        Cache::put("division:{$division->slug}", ['test' => 'data'], 60);
        
        Technology::create([
            'division_id' => $division->id,
            'name' => 'Test Technology',
            'description' => 'Test Description',
            'order' => 1,
        ]);
        
        $this->assertNull(Cache::get('divisions:index'));
        $this->assertNull(Cache::get("division:{$division->slug}"));
    }

    public function test_creating_machine_invalidates_divisions_cache(): void
    {
        $division = Division::factory()->create();
        Cache::put('divisions:index', ['test' => 'data'], 60);
        Cache::put("division:{$division->slug}", ['test' => 'data'], 60);
        
        Machine::create([
            'division_id' => $division->id,
            'name' => 'Test Machine',
            'description' => 'Test Description',
            'order' => 1,
        ]);
        
        $this->assertNull(Cache::get('divisions:index'));
        $this->assertNull(Cache::get("division:{$division->slug}"));
    }

    public function test_creating_milestone_invalidates_milestones_cache(): void
    {
        Cache::put('milestones:all', ['test' => 'data'], 60);
        
        Milestone::create([
            'year' => 2024,
            'text' => 'Test Milestone',
            'order' => 1,
        ]);
        
        $this->assertNull(Cache::get('milestones:all'));
    }

    public function test_updating_milestone_invalidates_milestones_cache(): void
    {
        $milestone = Milestone::create([
            'year' => 2024,
            'text' => 'Test Milestone',
            'order' => 1,
        ]);
        
        Cache::put('milestones:all', ['test' => 'data'], 60);
        
        $milestone->update(['text' => 'Updated Milestone']);
        
        $this->assertNull(Cache::get('milestones:all'));
    }

    public function test_deleting_milestone_invalidates_milestones_cache(): void
    {
        $milestone = Milestone::create([
            'year' => 2024,
            'text' => 'Test Milestone',
            'order' => 1,
        ]);
        
        Cache::put('milestones:all', ['test' => 'data'], 60);
        
        $milestone->delete();
        
        $this->assertNull(Cache::get('milestones:all'));
    }

    public function test_sitemap_regenerates_on_division_changes(): void
    {
        if (file_exists(public_path('sitemap.xml'))) {
            unlink(public_path('sitemap.xml'));
        }
        
        Division::create([
            'slug' => 'new-division',
            'name' => 'New Division',
            'description' => 'Test Description',
            'order' => 1,
        ]);
        
        $this->assertTrue(file_exists(public_path('sitemap.xml')));
        
        $content = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringContainsString('/line-of-business/new-division', $content);
    }

    public function test_division_media_invalidates_correct_caches(): void
    {
        $division = Division::factory()->create();
        $user = \App\Models\User::factory()->create();
        
        Cache::put('divisions:index', ['test' => 'data'], 60);
        Cache::put("division:{$division->slug}", ['test' => 'data'], 60);
        
        Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => 'image',
            'path_or_embed' => 'media/test.jpg',
            'is_featured' => true,
            'order' => 1,
            'uploaded_by' => $user->id,
        ]);
        
        $this->assertNull(Cache::get('divisions:index'));
        $this->assertNull(Cache::get("division:{$division->slug}"));
    }
}