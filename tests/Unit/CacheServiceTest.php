<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Division;
use App\Models\Media;
use App\Models\Setting;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService();
        Cache::flush();
    }

    public function test_caches_public_content_with_correct_ttl(): void
    {
        $key = 'test_key';
        $data = ['test' => 'data'];

        $result = $this->cacheService->cachePublicContent($key, function () use ($data) {
            return $data;
        }, 30);

        $this->assertEquals($data, $result);
        $this->assertEquals($data, Cache::get($key));
    }

    public function test_invalidates_home_cache_on_media_home_slider_changes(): void
    {
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        $this->cacheService->invalidateContentCache('media', 'home_slider');
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_invalidates_home_cache_on_clients_changes(): void
    {
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        $this->cacheService->invalidateContentCache('clients');
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_invalidates_home_cache_on_settings_changes(): void
    {
        Cache::put('home:v1', ['test' => 'data'], 60);
        
        $this->cacheService->invalidateContentCache('settings');
        $this->assertNull(Cache::get('home:v1'));
    }

    public function test_invalidates_divisions_index_cache_on_division_changes(): void
    {
        Cache::put('divisions:index', ['test' => 'data'], 60);
        
        $this->cacheService->invalidateContentCache('divisions');
        $this->assertNull(Cache::get('divisions:index'));
    }

    public function test_invalidates_divisions_index_cache_on_related_model_changes(): void
    {
        $types = ['products', 'technologies', 'machines'];
        foreach ($types as $type) {
            Cache::put('divisions:index', ['test' => 'data'], 60);
            $this->cacheService->invalidateContentCache($type);
            $this->assertNull(Cache::get('divisions:index'), "Failed to invalidate cache for {$type}");
        }
    }

    public function test_invalidates_divisions_index_cache_on_division_media_changes(): void
    {
        Cache::put('divisions:index', ['test' => 'data'], 60);
        
        $this->cacheService->invalidateContentCache('media', 'division');
        $this->assertNull(Cache::get('divisions:index'));
    }

    public function test_invalidates_specific_division_cache(): void
    {
        $slug = 'test-division';
        $cacheKey = "division:{$slug}";
        Cache::put($cacheKey, ['test' => 'data'], 60);
        Cache::put('divisions:index', ['test' => 'data'], 60);
        
        $this->cacheService->invalidateContentCache('division', $slug);
        
        $this->assertNull(Cache::get($cacheKey));
        $this->assertNull(Cache::get('divisions:index'));
    }

    public function test_warms_cache_for_frequently_accessed_content(): void
    {
        Setting::create(['data' => ['company_name' => 'Test Company']]);
        Division::factory()->create();
        Client::factory()->count(3)->create();
        
        $this->cacheService->warmCache();
        
        $this->assertNotNull(Cache::get('home:v1'));
        $this->assertNotNull(Cache::get('divisions:index'));
    }

    public function test_triggers_sitemap_regeneration_on_content_change(): void
    {
        if (File::exists(public_path('sitemap.xml'))) {
            File::delete(public_path('sitemap.xml'));
        }
        
        $this->cacheService->invalidateContentCache('divisions');
        
        $this->assertTrue(File::exists(public_path('sitemap.xml')));
    }

    public function test_cache_keys_follow_naming_convention(): void
    {
        $expectedKeys = [
            'home:v1',
            'divisions:index',
            'division:slug-here',
            'sitemap:xml',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertMatchesRegularExpression(
                '/^[a-z\-]+:[a-z0-9\-]+$/',
                $key,
                "Cache key {$key} doesn't follow naming convention"
            );
        }
    }

    public function test_retrieves_cached_content_or_executes_callback(): void
    {
        $key = 'test:callback';
        $callbackExecuted = false;
        
        $result = $this->cacheService->cachePublicContent($key, function () use (&$callbackExecuted) {
            $callbackExecuted = true;
            return 'callback_result';
        }, 60);
        
        $this->assertTrue($callbackExecuted);
        $this->assertEquals('callback_result', $result);
        
        $callbackExecuted = false;
        $result = $this->cacheService->cachePublicContent($key, function () use (&$callbackExecuted) {
            $callbackExecuted = true;
            return 'new_result';
        }, 60);
        
        $this->assertFalse($callbackExecuted);
        $this->assertEquals('callback_result', $result);
    }

    public function test_handles_storage_symlink_fallback(): void
    {
        $result = $this->cacheService->handleStorageFallback();
        $this->assertTrue($result);
    }

    public function test_generates_sitemap_with_correct_structure(): void
    {
        Division::factory()->create(['slug' => 'test-division']);
        
        $this->cacheService->triggerSitemapRegeneration();
        
        $this->assertTrue(File::exists(public_path('sitemap.xml')));
        
        $content = File::get(public_path('sitemap.xml'));
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString('/line-of-business/test-division', $content);
    }

    public function test_clears_all_caches(): void
    {
        Cache::put('test:key1', 'value1', 60);
        Cache::put('test:key2', 'value2', 60);
        
        $this->cacheService->clearAllCaches();
        
        $this->assertNull(Cache::get('test:key1'));
        $this->assertNull(Cache::get('test:key2'));
    }

    public function test_returns_cache_statistics(): void
    {
        $stats = $this->cacheService->getCacheStats();
        
        $this->assertArrayHasKey('cache_driver', $stats);
        $this->assertArrayHasKey('cache_mappings', $stats);
        $this->assertArrayHasKey('storage_symlink_status', $stats);
        $this->assertArrayHasKey('sitemap_exists', $stats);
    }
}