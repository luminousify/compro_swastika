<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Division;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Exception;

class MediaValidationTest extends TestCase
{
    use RefreshDatabase;

    private MediaService $mediaService;
    private User $user;
    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mediaService = new MediaService();
        $this->user = User::factory()->create();
        $this->division = Division::factory()->create();
        
        $this->actingAs($this->user);
        Storage::fake('public');
    }

    public function test_it_validates_hero_image_minimum_width()
    {
        $file = UploadedFile::fake()->image('small.jpg', 800, 450);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hero and slider images must be at least 1200px wide');
        
        $this->mediaService->uploadImage($file, $this->division, 'hero');
    }

    public function test_it_validates_hero_image_aspect_ratio()
    {
        // Create image with wrong aspect ratio (4:3 instead of 16:9)
        $file = UploadedFile::fake()->image('wrong-ratio.jpg', 1600, 1200);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hero and slider images must have a 16:9 aspect ratio');
        
        $this->mediaService->uploadImage($file, $this->division, 'hero');
    }

    public function test_it_accepts_hero_image_with_correct_aspect_ratio()
    {
        // Create image with correct 16:9 aspect ratio
        $file = UploadedFile::fake()->image('correct-ratio.jpg', 1920, 1080);
        
        $media = $this->mediaService->uploadImage($file, $this->division, 'hero');
        
        $this->assertNotNull($media);
        $this->assertEquals(1920, $media->width);
        $this->assertEquals(1080, $media->height);
    }

    public function test_it_accepts_hero_image_within_aspect_ratio_tolerance()
    {
        // Create image with aspect ratio within 10% tolerance
        $file = UploadedFile::fake()->image('tolerance.jpg', 1920, 1000); // Slightly off 16:9
        
        $media = $this->mediaService->uploadImage($file, $this->division, 'hero');
        
        $this->assertNotNull($media);
    }

    public function test_it_validates_video_file_size()
    {
        // Create a video file that's too large (60MB)
        $file = UploadedFile::fake()->create('large.mp4', 60 * 1024, 'video/mp4');
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Video file size must be less than 50MB');
        
        $this->mediaService->uploadVideo($file, $this->division);
    }

    public function test_it_validates_video_file_type()
    {
        $file = UploadedFile::fake()->create('test.avi', 1024, 'video/avi');
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only MP4 video files are allowed');
        
        $this->mediaService->uploadVideo($file, $this->division);
    }

    public function test_it_can_upload_vimeo_url()
    {
        $vimeoUrl = 'https://vimeo.com/123456789';
        
        $media = $this->mediaService->uploadVideo($vimeoUrl, $this->division);
        
        $this->assertNotNull($media);
        $this->assertEquals($vimeoUrl, $media->path_or_embed);
        $this->assertEquals('Vimeo Video', $media->caption);
    }
}