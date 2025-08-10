<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    private static array $responsiveSizes = [768, 1280, 1920];
    
    public static function generateSrcset(string $imagePath): string
    {
        $srcset = [];
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $pathWithoutExt = str_replace('.' . $extension, '', $imagePath);
        
        foreach (self::$responsiveSizes as $width) {
            $srcset[] = "{$pathWithoutExt}-{$width}w.{$extension} {$width}w";
        }
        
        return implode(', ', $srcset);
    }
    
    public static function generateWebpSrcset(string $imagePath): string
    {
        $srcset = [];
        $pathWithoutExt = preg_replace('/\.[^.]+$/', '', $imagePath);
        
        foreach (self::$responsiveSizes as $width) {
            $srcset[] = "{$pathWithoutExt}-{$width}w.webp {$width}w";
        }
        
        return implode(', ', $srcset);
    }
    
    public static function getLazyLoadingAttributes(): array
    {
        return [
            'loading' => 'lazy',
            'decoding' => 'async',
        ];
    }
    
    public static function getAspectRatioBoxCss(int $width, int $height): string
    {
        return "aspect-ratio: {$width}/{$height}; width: 100%; height: auto;";
    }
    
    public static function calculateSizesAttribute(string $type): string
    {
        return match ($type) {
            'hero', 'full' => '100vw',
            'card' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
            'thumbnail' => '(max-width: 640px) 50vw, 25vw',
            'client-logo' => '(max-width: 640px) 33vw, 16vw',
            default => '100vw',
        };
    }
    
    public static function getPreloadLinks(array $images): array
    {
        $links = [];
        foreach ($images as $image) {
            $links[] = sprintf(
                '<link rel="preload" as="image" href="%s" fetchpriority="high">',
                $image
            );
        }
        return $links;
    }
    
    public static function generatePlaceholder(int $width, int $height): string
    {
        $svg = <<<SVG
        <svg width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f3f4f6"/>
        </svg>
        SVG;
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    public static function validateHeroAspectRatio(int $width, int $height): bool
    {
        $targetRatio = 16 / 9; // 1.777...
        $actualRatio = $width / $height;
        $tolerance = 0.1; // 10% tolerance
        
        $minRatio = $targetRatio * (1 - $tolerance);
        $maxRatio = $targetRatio * (1 + $tolerance);
        
        return $actualRatio >= $minRatio && $actualRatio <= $maxRatio;
    }
    
    public static function getImageDimensions(string $path): ?array
    {
        if (!File::exists($path) && !Storage::exists($path)) {
            return null;
        }
        
        $fullPath = File::exists($path) ? $path : Storage::path($path);
        
        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) {
            return null;
        }
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'] ?? null,
        ];
    }
    
    public static function hasWebpVersion(string $imagePath): bool
    {
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $imagePath);
        return Storage::disk('public')->exists($webpPath);
    }
    
    public static function getResponsiveImageTag(
        string $src, 
        string $alt, 
        string $type = 'full',
        array $additionalAttributes = []
    ): string {
        $attributes = array_merge(
            self::getLazyLoadingAttributes(),
            [
                'src' => $src,
                'alt' => $alt,
                'srcset' => self::generateSrcset($src),
                'sizes' => self::calculateSizesAttribute($type),
            ],
            $additionalAttributes
        );
        
        // Add WebP source if available
        if (self::hasWebpVersion($src)) {
            $attributes['data-webp-srcset'] = self::generateWebpSrcset($src);
        }
        
        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
        }
        
        return sprintf('<img%s>', $attributeString);
    }
    
    public static function getPictureTag(
        string $src,
        string $alt,
        string $type = 'full',
        array $additionalAttributes = []
    ): string {
        $html = '<picture>';
        
        // Add WebP source if available
        if (self::hasWebpVersion($src)) {
            $html .= sprintf(
                '<source type="image/webp" srcset="%s" sizes="%s">',
                self::generateWebpSrcset($src),
                self::calculateSizesAttribute($type)
            );
        }
        
        // Add original format source
        $html .= sprintf(
            '<source srcset="%s" sizes="%s">',
            self::generateSrcset($src),
            self::calculateSizesAttribute($type)
        );
        
        // Add img fallback
        $attributes = array_merge(
            self::getLazyLoadingAttributes(),
            [
                'src' => $src,
                'alt' => $alt,
            ],
            $additionalAttributes
        );
        
        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
        }
        
        $html .= sprintf('<img%s>', $attributeString);
        $html .= '</picture>';
        
        return $html;
    }
}