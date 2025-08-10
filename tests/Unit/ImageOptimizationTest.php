<?php

namespace Tests\Unit;

use App\Helpers\ImageHelper;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_srcset_for_responsive_images(): void
    {
        $imagePath = 'media/test-image.jpg';
        $srcset = ImageHelper::generateSrcset($imagePath);
        
        $this->assertStringContainsString('media/test-image-768w.jpg 768w', $srcset);
        $this->assertStringContainsString('media/test-image-1280w.jpg 1280w', $srcset);
        $this->assertStringContainsString('media/test-image-1920w.jpg 1920w', $srcset);
    }

    public function test_generates_webp_srcset_when_available(): void
    {
        $imagePath = 'media/test-image.jpg';
        $srcset = ImageHelper::generateWebpSrcset($imagePath);
        
        $this->assertStringContainsString('media/test-image-768w.webp 768w', $srcset);
        $this->assertStringContainsString('media/test-image-1280w.webp 1280w', $srcset);
        $this->assertStringContainsString('media/test-image-1920w.webp 1920w', $srcset);
    }

    public function test_adds_lazy_loading_attributes(): void
    {
        $attributes = ImageHelper::getLazyLoadingAttributes();
        
        $this->assertArrayHasKey('loading', $attributes);
        $this->assertEquals('lazy', $attributes['loading']);
        $this->assertArrayHasKey('decoding', $attributes);
        $this->assertEquals('async', $attributes['decoding']);
    }

    public function test_generates_aspect_ratio_box_css(): void
    {
        $css = ImageHelper::getAspectRatioBoxCss(16, 9);
        
        $this->assertStringContainsString('aspect-ratio: 16/9', $css);
    }

    public function test_media_model_generates_responsive_urls(): void
    {
        $user = \App\Models\User::factory()->create();
        $media = Media::create([
            'mediable_type' => 'App\Models\Division',
            'mediable_id' => 1,
            'type' => 'image',
            'path_or_embed' => 'media/division/test.jpg',
            'width' => 1920,
            'height' => 1080,
            'bytes' => 102400,
            'order' => 1,
            'uploaded_by' => $user->id,
        ]);
        
        $this->assertNotEmpty($media->url);
        $this->assertNotEmpty($media->responsive_srcset);
        $this->assertStringContainsString('768w', $media->responsive_srcset);
        $this->assertStringContainsString('1280w', $media->responsive_srcset);
        $this->assertStringContainsString('1920w', $media->responsive_srcset);
    }

    public function test_media_model_returns_webp_url_when_available(): void
    {
        $user = \App\Models\User::factory()->create();
        $media = Media::create([
            'mediable_type' => 'App\Models\Division',
            'mediable_id' => 1,
            'type' => 'image',
            'path_or_embed' => 'media/division/test.jpg',
            'width' => 1920,
            'height' => 1080,
            'bytes' => 102400,
            'order' => 1,
            'uploaded_by' => $user->id,
        ]);
        
        // Check that WebP URL is generated correctly
        $webpPath = str_replace('.jpg', '.webp', $media->path_or_embed);
        $this->assertStringEndsWith($webpPath, $media->webp_url);
        $this->assertStringContainsString('/storage/', $media->webp_url);
    }

    public function test_calculates_optimal_sizes_attribute(): void
    {
        $sizes = ImageHelper::calculateSizesAttribute('full');
        $this->assertEquals('100vw', $sizes);
        
        $sizes = ImageHelper::calculateSizesAttribute('card');
        $this->assertStringContainsString('(max-width: 640px) 100vw', $sizes);
        $this->assertStringContainsString('(max-width: 1024px) 50vw', $sizes);
        $this->assertStringContainsString('33vw', $sizes);
        
        $sizes = ImageHelper::calculateSizesAttribute('hero');
        $this->assertEquals('100vw', $sizes);
    }

    public function test_preloads_critical_images(): void
    {
        $links = ImageHelper::getPreloadLinks([
            'hero.jpg',
            'logo.png'
        ]);
        
        $this->assertCount(2, $links);
        $this->assertStringContainsString('rel="preload"', $links[0]);
        $this->assertStringContainsString('as="image"', $links[0]);
        $this->assertStringContainsString('hero.jpg', $links[0]);
    }

    public function test_generates_placeholder_for_lazy_loading(): void
    {
        $placeholder = ImageHelper::generatePlaceholder(1920, 1080);
        
        $this->assertStringContainsString('data:image/svg+xml;base64,', $placeholder);
        
        // Decode and check the SVG content
        $base64Part = str_replace('data:image/svg+xml;base64,', '', $placeholder);
        $decodedSvg = base64_decode($base64Part);
        $this->assertStringContainsString('viewBox="0 0 1920 1080"', $decodedSvg);
        $this->assertStringContainsString('width="1920"', $decodedSvg);
        $this->assertStringContainsString('height="1080"', $decodedSvg);
    }

    public function test_validates_aspect_ratio_for_hero_images(): void
    {
        // 16:9 aspect ratio (valid)
        $this->assertTrue(ImageHelper::validateHeroAspectRatio(1920, 1080));
        
        // 16:9 with 10% tolerance (valid)
        $this->assertTrue(ImageHelper::validateHeroAspectRatio(1920, 1000)); // ~1.92 ratio
        
        // Outside 10% tolerance (invalid)
        $this->assertFalse(ImageHelper::validateHeroAspectRatio(1920, 800)); // 2.4 ratio
    }
}