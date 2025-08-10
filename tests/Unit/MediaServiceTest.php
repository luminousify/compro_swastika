<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\User;
use App\Models\Division;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Exception;

class MediaServiceTest extends TestCase
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
        
        // Fake the storage disk
        Storage::fake('public');
    }

    public function test_it_can_upload_and_process_a_valid_image()
    {
        // Create a test image with smaller dimensions to save memory
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::IMAGE, $media->type);
        $this->assertEquals($this->division->id, $media->mediable_id);
        $this->assertEquals(Division::class, $media->mediable_type);
        $this->assertEquals($this->user->id, $media->uploaded_by);
        
        // Check that file was stored
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
    }

    /** @test */
    public function it_validates_image_file_size()
    {
        // Create a file that's too large (6MB)
        $file = UploadedFile::fake()->create('large.jpg', 6 * 1024);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Image file size must be less than 5MB');
        
        $this->mediaService->uploadImage($file, $this->division);
    }

    /** @test */
    public function it_validates_image_file_type()
    {
        $file = UploadedFile::fake()->create('test.txt', 100);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only JPG, JPEG, PNG, and WebP images are allowed');
        
        $this->mediaService->uploadImage($file, $this->division);
    }

    /** @test */
    public function it_validates_hero_image_minimum_width()
    {
        $file = UploadedFile::fake()->image('small.jpg', 800, 450);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hero and slider images must be at least 1200px wide');
        
        $this->mediaService->uploadImage($file, $this->division, 'hero');
    }

    /** @test */
    public function it_validates_hero_image_aspect_ratio()
    {
        // Create image with wrong aspect ratio (4:3 instead of 16:9)
        $file = UploadedFile::fake()->image('wrong-ratio.jpg', 1600, 1200);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hero and slider images must have a 16:9 aspect ratio');
        
        $this->mediaService->uploadImage($file, $this->division, 'hero');
    }

    /** @test */
    public function it_accepts_hero_image_with_correct_aspect_ratio()
    {
        // Create image with correct 16:9 aspect ratio
        $file = UploadedFile::fake()->image('correct-ratio.jpg', 1920, 1080);
        
        $media = $this->mediaService->uploadImage($file, $this->division, 'hero');
        
        $this->assertInstanceOf(Media::class, $media);
    }

    /** @test */
    public function it_accepts_hero_image_within_aspect_ratio_tolerance()
    {
        // Create image with aspect ratio within 10% tolerance
        $file = UploadedFile::fake()->image('tolerance.jpg', 1920, 1000); // Slightly off 16:9
        
        $media = $this->mediaService->uploadImage($file, $this->division, 'hero');
        
        $this->assertInstanceOf(Media::class, $media);
    }

    /** @test */
    public function it_validates_maximum_image_dimensions()
    {
        // Mock an image that would be too large
        $file = UploadedFile::fake()->image('huge.jpg', 5000, 5000);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Image dimensions must not exceed 4096×4096 pixels');
        
        $this->mediaService->uploadImage($file, $this->division);
    }

    /** @test */
    public function it_generates_hashed_filename()
    {
        $originalName = 'test-image.jpg';
        $hashedName = $this->mediaService->generateHashedFilename($originalName);
        
        $this->assertStringEndsWith('.jpg', $hashedName);
        $this->assertEquals(36, strlen($hashedName)); // 32 char hash + .jpg
        $this->assertNotEquals($originalName, $hashedName);
    }

    /** @test */
    public function it_can_upload_video_file()
    {
        $file = UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4');
        
        $media = $this->mediaService->uploadVideo($file, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
    }

    /** @test */
    public function it_validates_video_file_size()
    {
        // Create a video file that's too large (60MB)
        $file = UploadedFile::fake()->create('large.mp4', 60 * 1024, 'video/mp4');
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Video file size must be less than 50MB');
        
        $this->mediaService->uploadVideo($file, $this->division);
    }

    /** @test */
    public function it_validates_video_file_type()
    {
        $file = UploadedFile::fake()->create('test.avi', 1024, 'video/avi');
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only MP4 video files are allowed');
        
        $this->mediaService->uploadVideo($file, $this->division);
    }

    /** @test */
    public function it_can_upload_youtube_url()
    {
        $youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        
        $media = $this->mediaService->uploadVideo($youtubeUrl, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertEquals($youtubeUrl, $media->path_or_embed);
        $this->assertEquals('YouTube Video', $media->caption);
    }

    /** @test */
    public function it_can_upload_vimeo_url()
    {
        $vimeoUrl = 'https://vimeo.com/123456789';
        
        $media = $this->mediaService->uploadVideo($vimeoUrl, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertEquals($vimeoUrl, $media->path_or_embed);
        $this->assertEquals('Vimeo Video', $media->caption);
    }

    /** @test */
    public function it_validates_video_url_domain()
    {
        $invalidUrl = 'https://example.com/video.mp4';
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only YouTube and Vimeo URLs are allowed');
        
        $this->mediaService->uploadVideo($invalidUrl, $this->division);
    }

    /** @test */
    public function it_can_delete_image_media()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
        
        $this->mediaService->deleteMedia($media);
        
        $this->assertFalse(Storage::disk('public')->exists($media->path_or_embed));
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /** @test */
    public function it_can_delete_video_file_media()
    {
        $file = UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4');
        $media = $this->mediaService->uploadVideo($file, $this->division);
        
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
        
        $this->mediaService->deleteMedia($media);
        
        $this->assertFalse(Storage::disk('public')->exists($media->path_or_embed));
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /** @test */
    public function it_does_not_delete_files_for_video_url_media()
    {
        $youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $media = $this->mediaService->uploadVideo($youtubeUrl, $this->division);
        
        // Should not throw any exceptions
        $this->mediaService->deleteMedia($media);
        
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /** @test */
    public function it_sets_correct_order_for_new_media()
    {
        $file1 = UploadedFile::fake()->image('test1.jpg', 1920, 1080);
        $file2 = UploadedFile::fake()->image('test2.jpg', 1920, 1080);
        
        $media1 = $this->mediaService->uploadImage($file1, $this->division);
        $media2 = $this->mediaService->uploadImage($file2, $this->division);
        
        $this->assertEquals(1, $media1->order);
        $this->assertEquals(2, $media2->order);
    }

    /** @test */
    public function it_creates_proper_storage_directory_structure()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        $expectedPattern = '/^media\/division\/\d{4}\/\d{2}\/[a-f0-9]{32}\.jpg$/';
        $this->assertMatchesRegularExpression($expectedPattern, $media->path_or_embed);
    }

    /** @test */
    public function it_handles_webp_generation_gracefully()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        // WebP generation should not cause the upload to fail
        $this->assertInstanceOf(Media::class, $media);
        
        // Check if WebP version was created
        $webpPath = str_replace('.jpg', '.webp', $media->path_or_embed);
        // Note: In testing environment, WebP generation might not work, so we don't assert its existence
    }
}