<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class SEOService
{
    private const TITLE_MIN_LENGTH = 50;
    private const TITLE_MAX_LENGTH = 60;
    private const DESCRIPTION_MIN_LENGTH = 120;
    private const DESCRIPTION_MAX_LENGTH = 160;
    
    private array $pageDefaults = [
        'home' => [
            'title_suffix' => 'Beranda',
            'priority' => '1.0',
            'changefreq' => 'weekly',
        ],
        'visi-misi' => [
            'title_suffix' => 'Visi & Misi',
            'priority' => '0.8',
            'changefreq' => 'monthly',
        ],
        'milestones' => [
            'title_suffix' => 'Milestones',
            'priority' => '0.7',
            'changefreq' => 'monthly',
        ],
        'line-of-business' => [
            'title_suffix' => 'Line of Business',
            'priority' => '0.9',
            'changefreq' => 'weekly',
        ],
        'contact' => [
            'title_suffix' => 'Hubungi Kami',
            'priority' => '0.8',
            'changefreq' => 'monthly',
        ],
    ];

    /**
     * Generate meta tags for a page
     */
    public function generateMetaTags(string $page, array $data = []): array
    {
        $settings = $this->getSettings();
        $companyName = $settings['company_name'] ?? 'PT. Daya Swastika Perkasa';
        
        // Generate title
        $title = $data['title'] ?? $this->generateDefaultTitle($page, $companyName);
        $title = $this->truncateText($title, self::TITLE_MAX_LENGTH, self::TITLE_MIN_LENGTH);
        
        // Generate description
        $description = $data['description'] ?? $this->generateDefaultDescription($page, $settings);
        $description = $this->truncateText($description, self::DESCRIPTION_MAX_LENGTH, self::DESCRIPTION_MIN_LENGTH);
        
        // Generate keywords
        $keywords = $data['keywords'] ?? $this->generateKeywords($title . ' ' . $description);
        
        // Generate canonical URL
        $canonical = $this->getCanonicalUrl($page === 'home' ? '/' : $page);
        
        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'robots' => $this->getRobotsMetaContent(),
            'author' => $companyName,
            'viewport' => 'width=device-width, initial-scale=1.0',
            'charset' => 'UTF-8',
            'language' => 'id',
        ];
    }

    /**
     * Generate Open Graph tags
     */
    public function generateOpenGraphTags(string $page, array $data = []): array
    {
        $settings = $this->getSettings();
        $baseUrl = Config::get('app.url');
        
        // Determine image
        $image = $this->determineOpenGraphImage($page, $data);
        
        // Get title and description
        $title = $data['title'] ?? $this->generateDefaultTitle($page, $settings['company_name'] ?? '');
        $description = $data['description'] ?? $this->generateDefaultDescription($page, $settings);
        
        $ogTags = [
            'og:title' => $title,
            'og:description' => $this->truncateText($description, self::DESCRIPTION_MAX_LENGTH),
            'og:image' => $this->getFullImageUrl($image),
            'og:url' => $this->getCanonicalUrl($page === 'home' ? '/' : $page),
            'og:type' => $this->determineOgType($page),
            'og:site_name' => $settings['company_name'] ?? 'PT. Daya Swastika Perkasa',
            'og:locale' => 'id_ID',
        ];
        
        // Add Twitter Card tags
        $ogTags['twitter:card'] = 'summary_large_image';
        $ogTags['twitter:title'] = $ogTags['og:title'];
        $ogTags['twitter:description'] = $ogTags['og:description'];
        $ogTags['twitter:image'] = $ogTags['og:image'];
        
        return $ogTags;
    }

    /**
     * Generate JSON-LD structured data
     */
    public function generateJsonLD(string $page, array $data = []): array
    {
        $settings = $this->getSettings();
        $baseUrl = Config::get('app.url');
        
        $jsonLd = [
            '@context' => 'https://schema.org',
        ];
        
        // Determine schema type based on page and data
        if ($page === 'contact' || (!empty($data['includeLocalBusiness']) && !empty($settings['company_address']))) {
            $jsonLd['@type'] = 'LocalBusiness';
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings['company_address'] ?? '',
                'addressLocality' => 'Jakarta',
                'addressRegion' => 'DKI Jakarta',
                'addressCountry' => 'Indonesia',
            ];
        } else {
            $jsonLd['@type'] = 'Organization';
        }
        
        // Common organization properties
        $jsonLd['name'] = $settings['company_name'] ?? 'PT. Daya Swastika Perkasa';
        $jsonLd['url'] = $baseUrl;
        
        if (!empty($settings['logo_path'])) {
            $jsonLd['logo'] = $this->getFullImageUrl($settings['logo_path']);
        }
        
        if (!empty($settings['company_phone'])) {
            $jsonLd['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => $settings['company_phone'],
                'contactType' => 'customer-service',
                'areaServed' => 'ID',
                'availableLanguage' => 'Indonesian',
            ];
        }
        
        if (!empty($settings['company_email'])) {
            $jsonLd['email'] = $settings['company_email'];
        }
        
        // Add social media links if available
        $socialLinks = [];
        foreach (['facebook', 'instagram', 'linkedin', 'youtube'] as $platform) {
            if (!empty($settings["social_{$platform}"])) {
                $socialLinks[] = $settings["social_{$platform}"];
            }
        }
        
        if (!empty($socialLinks)) {
            $jsonLd['sameAs'] = $socialLinks;
        }
        
        return $jsonLd;
    }

    /**
     * Generate breadcrumb schema
     */
    public function generateBreadcrumbSchema(array $breadcrumbs): array
    {
        $baseUrl = Config::get('app.url');
        
        $items = [];
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $baseUrl . $breadcrumb['url'],
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Generate website search schema
     */
    public function generateWebsiteSearchSchema(): array
    {
        $baseUrl = Config::get('app.url');
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => $baseUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $baseUrl . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Generate sitemap XML
     */
    public function generateSitemap(): string
    {
        $baseUrl = Config::get('app.url');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add static pages
        $staticPages = [
            '/' => ['priority' => '1.0', 'changefreq' => 'weekly'],
            '/visi-misi' => ['priority' => '0.8', 'changefreq' => 'monthly'],
            '/milestones' => ['priority' => '0.7', 'changefreq' => 'monthly'],
            '/divisions' => ['priority' => '0.9', 'changefreq' => 'weekly'],
            '/contact' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $url => $config) {
            $xml .= $this->generateSitemapEntry(
                $baseUrl . $url,
                $config['priority'],
                $config['changefreq']
            );
        }
        
        // Add division pages
        $divisions = Division::select('slug', 'updated_at')->get();
        foreach ($divisions as $division) {
            $xml .= $this->generateSitemapEntry(
                $baseUrl . '/divisions/' . $division->slug,
                '0.8',
                'weekly',
                $division->updated_at->format('Y-m-d')
            );
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate a single sitemap entry
     */
    private function generateSitemapEntry(string $loc, string $priority, string $changefreq, ?string $lastmod = null): string
    {
        $entry = '  <url>' . "\n";
        $entry .= '    <loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>' . "\n";
        $entry .= '    <priority>' . $priority . '</priority>' . "\n";
        $entry .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        
        if ($lastmod) {
            $entry .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        }
        
        $entry .= '  </url>' . "\n";
        
        return $entry;
    }

    /**
     * Get robot headers for staging protection
     */
    public function getRobotHeaders(): array
    {
        if (Config::get('app.env') !== 'production') {
            return [
                'X-Robots-Tag' => 'noindex, nofollow',
            ];
        }
        
        return [];
    }

    /**
     * Get robots meta content
     */
    private function getRobotsMetaContent(): string
    {
        if (Config::get('app.env') !== 'production') {
            return 'noindex, nofollow';
        }
        
        return 'index, follow';
    }

    /**
     * Generate robots.txt content
     */
    public function generateRobotsTxt(): string
    {
        $content = "User-agent: *\n";
        
        if (Config::get('app.env') !== 'production') {
            $content .= "Disallow: /\n";
        } else {
            $content .= "Allow: /\n";
            $content .= "Disallow: /admin\n";
            $content .= "Disallow: /api\n";
            $content .= "Disallow: /storage\n";
            $content .= "Disallow: /*.pdf$\n";
            $content .= "\n";
            $content .= "Sitemap: " . Config::get('app.url') . "/sitemap.xml\n";
        }
        
        return $content;
    }

    /**
     * Get canonical URL
     */
    public function getCanonicalUrl(string $path, bool $keepQueryParams = false): string
    {
        $baseUrl = rtrim(Config::get('app.url'), '/');
        $path = ltrim($path, '/');
        
        // Remove query parameters unless explicitly kept
        if (!$keepQueryParams && str_contains($path, '?')) {
            $path = explode('?', $path)[0];
        }
        
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }

    /**
     * Validate slug format
     */
    public function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }

    /**
     * Truncate text intelligently
     */
    public function truncateText(string $text, int $maxLength, int $minLength = 0): string
    {
        if (strlen($text) <= $maxLength) {
            // Pad if below minimum
            if ($minLength > 0 && strlen($text) < $minLength) {
                return $text;
            }
            return $text;
        }
        
        // Find the last space before max length
        $truncated = substr($text, 0, $maxLength);
        $lastSpace = strrpos($truncated, ' ');
        
        if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return rtrim($truncated, ' .,;:!?') . '...';
    }

    /**
     * Generate keywords from content
     */
    public function generateKeywords(string $content): string
    {
        // Remove HTML tags and special characters
        $content = strip_tags($content);
        $content = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $content);
        
        // Convert to lowercase and split into words
        $words = str_word_count(strtolower($content), 1);
        
        // Remove common stop words
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'and', 'a', 'an', 'as', 'are', 'was', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need', 'to', 'of', 'in', 'for', 'with', 'by', 'from', 'about', 'into', 'than', 'that', 'this', 'these', 'those'];
        $words = array_diff($words, $stopWords);
        
        // Count word frequency
        $wordCounts = array_count_values($words);
        arsort($wordCounts);
        
        // Get top keywords
        $keywords = array_slice(array_keys($wordCounts), 0, 10);
        
        // Add some default keywords
        $defaultKeywords = ['daya swastika perkasa', 'dsp', 'industrial', 'indonesia'];
        $keywords = array_unique(array_merge($keywords, $defaultKeywords));
        
        return implode(', ', array_slice($keywords, 0, 15));
    }

    /**
     * Get settings from cache or database
     */
    private function getSettings(): array
    {
        return Cache::remember('seo:settings', 3600, function () {
            $setting = Setting::first();
            return $setting ? $setting->data : [];
        });
    }

    /**
     * Generate default title for a page
     */
    private function generateDefaultTitle(string $page, string $companyName): string
    {
        $suffix = $this->pageDefaults[$page]['title_suffix'] ?? ucfirst($page);
        return $suffix . ' - ' . $companyName;
    }

    /**
     * Generate default description for a page
     */
    private function generateDefaultDescription(string $page, array $settings): string
    {
        $default = $settings['default_meta_description'] ?? 'Leading industrial solutions provider in Indonesia';
        
        $descriptions = [
            'home' => $default,
            'visi-misi' => 'Visi dan misi ' . ($settings['company_name'] ?? 'PT. Daya Swastika Perkasa') . ' sebagai penyedia solusi industrial terkemuka di Indonesia',
            'milestones' => 'Pencapaian dan milestone penting dalam perjalanan ' . ($settings['company_name'] ?? 'PT. Daya Swastika Perkasa'),
            'line-of-business' => 'Divisi dan lini bisnis ' . ($settings['company_name'] ?? 'PT. Daya Swastika Perkasa') . ' meliputi manufacturing, engineering, dan teknologi',
            'contact' => 'Hubungi ' . ($settings['company_name'] ?? 'PT. Daya Swastika Perkasa') . ' untuk solusi industrial terbaik',
        ];
        
        return $descriptions[$page] ?? $default;
    }

    /**
     * Determine Open Graph image
     */
    private function determineOpenGraphImage(string $page, array $data): string
    {
        // Check for entity-specific image (e.g., division)
        if (!empty($data['entity']) && $data['entity'] instanceof Division) {
            if (!empty($data['entity']->hero_image_path)) {
                return $data['entity']->hero_image_path;
            }
        }
        
        // Check for provided image
        if (!empty($data['image'])) {
            return $data['image'];
        }
        
        // Fall back to company logo
        $settings = $this->getSettings();
        return $settings['logo_path'] ?? 'images/logo.png';
    }

    /**
     * Get full image URL
     */
    private function getFullImageUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        
        $baseUrl = rtrim(Config::get('app.url'), '/');
        $path = ltrim($path, '/');
        
        // Add storage prefix if not present
        if (!str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }
        
        return $baseUrl . '/' . $path;
    }

    /**
     * Determine Open Graph type
     */
    private function determineOgType(string $page): string
    {
        $types = [
            'home' => 'website',
            'contact' => 'business.business',
            'line-of-business' => 'website',
            'division' => 'article',
        ];
        
        return $types[$page] ?? 'website';
    }
}