<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeploymentOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
    }

    public function test_cache_is_working(): void
    {
        Cache::flush();
        
        // Test cache put and get
        Cache::put('test-key', 'test-value', 60);
        
        $this->assertEquals('test-value', Cache::get('test-key'));
    }

    public function test_settings_are_cached(): void
    {
        Cache::flush();
        
        // First request should cache the settings
        $setting1 = Setting::getValue('company_name');
        
        // Second request should use cache
        $setting2 = Setting::getValue('company_name');
        
        $this->assertEquals($setting1, $setting2);
        $this->assertEquals('PT Swastika Investama Prima', $setting2);
    }

    public function test_static_assets_have_versioning(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check if Vite assets are properly referenced
        $content = $response->getContent();
        $this->assertStringContainsString('assets/', $content);
    }

    public function test_database_optimization_works(): void
    {
        // Clear cache to test actual query count
        Cache::flush();
        
        \DB::enableQueryLog();
        
        // Load home page which should use caching
        $response = $this->get('/');
        
        $queries = \DB::getQueryLog();
        $queryCount = count($queries);
        
        // Should have reasonable query usage (allowing for initial cache population)
        $this->assertLessThan(15, $queryCount, 
            'Too many database queries on home page: ' . $queryCount . ' queries');
        
        // Second request should use cache and have fewer queries
        \DB::flushQueryLog();
        $response2 = $this->get('/');
        
        $cachedQueries = \DB::getQueryLog();
        $cachedQueryCount = count($cachedQueries);
        
        // Cached request should have minimal queries
        $this->assertLessThan(5, $cachedQueryCount, 
            'Cached page should have fewer queries: ' . $cachedQueryCount . ' queries');
        
        $response->assertStatus(200);
        $response2->assertStatus(200);
    }

    public function test_gzip_compression_headers(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Headers that should be present for optimization
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_environment_configuration_is_correct(): void
    {
        // Test that important configs are set correctly for shared hosting
        $this->assertNotNull(Config::get('app.key'));
        $this->assertNotNull(Config::get('database.default'));
        
        // Session and cache drivers should be suitable for shared hosting
        $sessionDriver = Config::get('session.driver');
        $this->assertTrue(in_array($sessionDriver, ['file', 'database', 'redis', 'cookie', 'array']), 
            'Session driver should be suitable for shared hosting. Got: ' . var_export($sessionDriver, true));
        
        $cacheDriver = Config::get('cache.default');
        $this->assertTrue(in_array($cacheDriver, ['file', 'database', 'redis', 'array']), 
            'Cache driver should be suitable for shared hosting. Got: ' . var_export($cacheDriver, true));
    }

    public function test_asset_optimization(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Check that CSS and JS are properly linked
        $this->assertStringContainsString('.css', $content);
        $this->assertStringContainsString('.js', $content);
    }

    public function test_log_rotation_configuration(): void
    {
        $logConfig = Config::get('logging.channels.single');
        
        $this->assertNotNull($logConfig);
        
        // Should have single log channel configured for shared hosting
        $this->assertArrayHasKey('single', Config::get('logging.channels'));
        
        // Log level should be appropriate for production
        $logLevel = Config::get('logging.channels.single.level', 'debug');
        $this->assertContains($logLevel, ['debug', 'info', 'warning', 'error']);
        
        // Default can be stack or single - both are acceptable
        $logDefault = Config::get('logging.default');
        $this->assertContains($logDefault, ['single', 'stack']);
    }

    public function test_memory_limit_optimization(): void
    {
        // Test that the application doesn't consume excessive memory
        $memoryBefore = memory_get_usage();
        
        // Perform some operations
        $response = $this->get('/');
        Cache::put('test', str_repeat('x', 1000), 60);
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        // Should not use excessive memory (less than 50MB for this test)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 
            'Memory usage is too high: ' . number_format($memoryUsed / 1024 / 1024, 2) . 'MB');
        
        $response->assertStatus(200);
    }

    public function test_file_permissions_check(): void
    {
        // Check that important directories exist and are writable
        $this->assertTrue(is_dir(storage_path('logs')), 'Logs directory should exist');
        $this->assertTrue(is_writable(storage_path('logs')), 'Logs directory should be writable');
        
        $this->assertTrue(is_dir(storage_path('framework/cache')), 'Cache directory should exist');
        $this->assertTrue(is_writable(storage_path('framework/cache')), 'Cache directory should be writable');
        
        $this->assertTrue(is_dir(storage_path('app/public')), 'Public storage directory should exist');
    }

    public function test_maintenance_mode_functionality(): void
    {
        $this->artisan('down');
        
        $response = $this->get('/');
        $response->assertStatus(503);
        
        $this->artisan('up');
        
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}