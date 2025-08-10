<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache key mappings and their invalidation triggers
     */
    private const CACHE_MAPPINGS = [
        'home:v1' => [
            'ttl' => 30, // 30 minutes
            'invalidate_on' => ['media.home_slider', 'clients', 'settings']
        ],
        'divisions:index' => [
            'ttl' => 60, // 60 minutes
            'invalidate_on' => ['divisions', 'products', 'technologies', 'machines', 'media.division']
        ],
        'division:{slug}' => [
            'ttl' => 45, // 45 minutes
            'invalidate_on' => ['division.{slug}', 'products.{slug}', 'technologies.{slug}', 'machines.{slug}', 'media.{slug}']
        ],
        'milestones:all' => [
            'ttl' => 60, // 60 minutes
            'invalidate_on' => ['milestones']
        ],
        'settings:all' => [
            'ttl' => 60, // 60 minutes
            'invalidate_on' => ['settings']
        ],
        'visi-misi:v1' => [
            'ttl' => 60, // 60 minutes
            'invalidate_on' => ['settings']
        ]
    ];

    /**
     * Cache public content with automatic TTL
     */
    public function cachePublicContent(string $key, callable $callback, ?int $minutes = null): mixed
    {
        $ttl = $minutes ?? $this->getTtlForKey($key);
        
        return Cache::remember($key, now()->addMinutes($ttl), $callback);
    }

    /**
     * Get TTL for a specific cache key
     */
    private function getTtlForKey(string $key): int
    {
        foreach (self::CACHE_MAPPINGS as $pattern => $config) {
            if ($this->keyMatchesPattern($key, $pattern)) {
                return $config['ttl'];
            }
        }
        
        return 30; // Default TTL
    }

    /**
     * Check if a key matches a pattern
     */
    private function keyMatchesPattern(string $key, string $pattern): bool
    {
        if (!str_contains($pattern, '{')) {
            return $key === $pattern;
        }
        
        $regex = str_replace(['{slug}', '{id}'], ['[^:]+', '\d+'], $pattern);
        return preg_match('/^' . str_replace(':', '\:', $regex) . '$/', $key);
    }

    /**
     * Invalidate content cache based on content type and identifier
     */
    public function invalidateContentCache(string $contentType, ?string $identifier = null): void
    {
        $keysToInvalidate = $this->getKeysToInvalidate($contentType, $identifier);
        
        foreach ($keysToInvalidate as $key) {
            if (str_contains($key, '{')) {
                $this->invalidatePatternKeys($key, $identifier);
            } else {
                Cache::forget($key);
                Log::info("Cache invalidated: {$key}");
            }
        }
        
        // Trigger sitemap regeneration
        $this->triggerSitemapRegeneration();
    }

    /**
     * Get cache keys that should be invalidated for a content type
     */
    private function getKeysToInvalidate(string $contentType, ?string $identifier = null): array
    {
        $keysToInvalidate = [];
        
        foreach (self::CACHE_MAPPINGS as $cacheKey => $config) {
            foreach ($config['invalidate_on'] as $trigger) {
                if ($this->shouldInvalidateForTrigger($trigger, $contentType, $identifier)) {
                    $keysToInvalidate[] = $cacheKey;
                    break;
                }
            }
        }
        
        return array_unique($keysToInvalidate);
    }

    /**
     * Check if cache should be invalidated for a trigger
     */
    private function shouldInvalidateForTrigger(string $trigger, string $contentType, ?string $identifier = null): bool
    {
        // Handle specific triggers
        if ($trigger === 'media.home_slider' && $contentType === 'media' && $identifier === 'home_slider') {
            return true;
        }
        
        if ($trigger === 'media.division' && $contentType === 'media' && str_starts_with($identifier ?? '', 'division')) {
            return true;
        }
        
        // Handle pattern triggers with identifiers
        if (str_contains($trigger, '{') && $identifier) {
            $pattern = str_replace(['{slug}', '{id}'], [$identifier, $identifier], $trigger);
            return $pattern === $contentType . '.' . $identifier;
        }
        
        // Handle direct content type matches
        return str_starts_with($trigger, $contentType);
    }

    /**
     * Invalidate cache keys matching a pattern
     */
    private function invalidatePatternKeys(string $pattern, ?string $identifier = null): void
    {
        if (!$identifier) {
            return;
        }
        
        $concreteKey = str_replace(['{slug}', '{id}'], [$identifier, $identifier], $pattern);
        Cache::forget($concreteKey);
        Log::info("Pattern cache invalidated: {$concreteKey}");
    }

    /**
     * Warm frequently accessed cache
     */
    public function warmCache(): void
    {
        try {
            // Warm home page cache
            $this->cachePublicContent('home:v1', function () {
                return [
                    'settings' => app(\App\Models\Setting::class)->first()?->data ?? [],
                    'slider_media' => app(\App\Models\Media::class)->where('is_home_slider', true)
                        ->orderBy('order')->get(),
                    'clients' => app(\App\Models\Client::class)->orderBy('order')->limit(12)->get()
                ];
            });
            
            // Warm divisions index cache
            $this->cachePublicContent('divisions:index', function () {
                return app(\App\Models\Division::class)->with('media')->orderBy('order')->get();
            });
            
            Log::info('Cache warmed successfully');
        } catch (\Exception $e) {
            Log::error('Cache warming failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle storage symlink fallback
     */
    public function handleStorageFallback(): bool
    {
        $publicStoragePath = public_path('storage');
        $storagePath = storage_path('app/public');
        
        // Check if symlink exists and is valid
        if (is_link($publicStoragePath) && readlink($publicStoragePath) === $storagePath) {
            return true;
        }
        
        // Try to create symlink
        if (!file_exists($publicStoragePath)) {
            try {
                symlink($storagePath, $publicStoragePath);
                Log::info('Storage symlink created successfully');
                return true;
            } catch (\Exception $e) {
                Log::warning('Symlink creation failed, using file copy fallback: ' . $e->getMessage());
            }
        }
        
        // Fallback to file copying
        return $this->copyStorageFiles();
    }

    /**
     * Copy storage files as fallback
     */
    private function copyStorageFiles(): bool
    {
        try {
            $storagePath = storage_path('app/public');
            $publicStoragePath = public_path('storage');
            
            if (!File::exists($publicStoragePath)) {
                File::makeDirectory($publicStoragePath, 0755, true);
            }
            
            $this->recursiveCopy($storagePath, $publicStoragePath);
            Log::info('Storage files copied successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Storage file copy failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recursively copy files
     */
    private function recursiveCopy(string $source, string $destination): void
    {
        if (!File::exists($source)) {
            return;
        }
        
        if (File::isDirectory($source)) {
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            
            foreach (File::files($source) as $file) {
                File::copy($file->getPathname(), $destination . '/' . $file->getFilename());
            }
            
            foreach (File::directories($source) as $directory) {
                $this->recursiveCopy($directory, $destination . '/' . basename($directory));
            }
        } else {
            File::copy($source, $destination);
        }
    }

    /**
     * Trigger sitemap regeneration
     */
    public function triggerSitemapRegeneration(): void
    {
        try {
            // Clear sitemap cache
            Cache::forget('sitemap:xml');
            
            // Queue sitemap regeneration (or generate immediately for small sites)
            $this->generateSitemap();
            
            Log::info('Sitemap regeneration triggered');
        } catch (\Exception $e) {
            Log::error('Sitemap regeneration failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate sitemap XML
     */
    private function generateSitemap(): void
    {
        $sitemap = Cache::remember('sitemap:xml', now()->addHours(24), function () {
            $urls = [
                ['url' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
                ['url' => '/visi-misi', 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['url' => '/milestones', 'priority' => '0.7', 'changefreq' => 'monthly'],
                ['url' => '/line-of-business', 'priority' => '0.9', 'changefreq' => 'weekly'],
                ['url' => '/contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ];
            
            // Add division URLs
            $divisions = app(\App\Models\Division::class)->select('slug', 'updated_at')->get();
            foreach ($divisions as $division) {
                $urls[] = [
                    'url' => '/line-of-business/' . $division->slug,
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $division->updated_at->format('Y-m-d')
                ];
            }
            
            return $this->buildSitemapXml($urls);
        });
        
        // Write sitemap to public directory
        File::put(public_path('sitemap.xml'), $sitemap);
    }

    /**
     * Build sitemap XML content
     */
    private function buildSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . config('app.url') . $url['url'] . '</loc>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Clear all application caches
     */
    public function clearAllCaches(): void
    {
        Cache::flush();
        Log::info('All caches cleared');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cache_driver' => config('cache.default'),
            'cache_mappings' => count(self::CACHE_MAPPINGS),
            'storage_symlink_status' => $this->checkStorageSymlink(),
            'sitemap_exists' => file_exists(public_path('sitemap.xml')),
        ];
    }

    /**
     * Check storage symlink status
     */
    private function checkStorageSymlink(): string
    {
        $publicStoragePath = public_path('storage');
        
        if (is_link($publicStoragePath)) {
            return 'symlink';
        } elseif (is_dir($publicStoragePath)) {
            return 'directory';
        } else {
            return 'missing';
        }
    }
}