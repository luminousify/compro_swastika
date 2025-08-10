<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Models\Division;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $salesUser;
    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->salesUser = User::factory()->create(['role' => UserRole::SALES]);
        $this->division = Division::factory()->create();
        
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_upload_image_through_service()
    {
        $this->actingAs($this->adminUser);
        
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::IMAGE, $media->type);
        $this->assertEquals($this->adminUser->id, $media->uploaded_by);
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
    }

    /** @test */
    public function sales_can_upload_image_through_service()
    {
        $this->actingAs($this->salesUser);
        
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($this->salesUser->id, $media->uploaded_by);
    }

    /** @test */
    public function uploaded_image_has_correct_metadata()
    {
        $this->actingAs($this->adminUser);
        
        $file = UploadedFile::fake()->image('test-image.jpg', 1920, 1080);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        
        $this->assertEquals('test-image.jpg', $media->caption);
        $this->assertEquals(1920, $media->width);
        $this->assertEquals(1080, $media->height);
        $this->assertGreaterThan(0, $media->bytes);
        $this->assertEquals(1, $media->order);
    }

    /** @test */
    public function multiple_images_have_correct_order()
    {
        $this->actingAs($this->adminUser);
        
        $mediaService = new MediaService();
        
        $file1 = UploadedFile::fake()->image('first.jpg', 1920, 1080);
        $file2 = UploadedFile::fake()->image('second.jpg', 1920, 1080);
        $file3 = UploadedFile::fake()->image('third.jpg', 1920, 1080);
        
        $media1 = $mediaService->uploadImage($file1, $this->division);
        $media2 = $mediaService->uploadImage($file2, $this->division);
        $media3 = $mediaService->uploadImage($file3, $this->division);
        
        $this->assertEquals(1, $media1->order);
        $this->assertEquals(2, $media2->order);
        $this->assertEquals(3, $media3->order);
    }

    /** @test */
    public function hero_image_validation_works()
    {
        $this->actingAs($this->adminUser);
        
        $mediaService = new MediaService();
        
        // Test minimum width validation
        $smallFile = UploadedFile::fake()->image('small.jpg', 800, 450);
        
        try {
            $mediaService->uploadImage($smallFile, $this->division, 'hero');
            $this->fail('Expected exception for small hero image');
        } catch (\Exception $e) {
            $this->assertStringContains('Hero and slider images must be at least 1200px wide', $e->getMessage());
        }
        
        // Test aspect ratio validation
        $wrongRatioFile = UploadedFile::fake()->image('wrong.jpg', 1600, 1200); // 4:3 ratio
        
        try {
            $mediaService->uploadImage($wrongRatioFile, $this->division, 'hero');
            $this->fail('Expected exception for wrong aspect ratio');
        } catch (\Exception $e) {
            $this->assertStringContains('Hero and slider images must have a 16:9 aspect ratio', $e->getMessage());
        }
        
        // Test valid hero image
        $validFile = UploadedFile::fake()->image('valid.jpg', 1920, 1080);
        $media = $mediaService->uploadImage($validFile, $this->division, 'hero');
        
        $this->assertInstanceOf(Media::class, $media);
    }

    /** @test */
    public function video_file_upload_works()
    {
        $this->actingAs($this->adminUser);
        
        $videoFile = UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4');
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadVideo($videoFile, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
    }

    /** @test */
    public function youtube_url_upload_works()
    {
        $this->actingAs($this->adminUser);
        
        $youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadVideo($youtubeUrl, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertEquals($youtubeUrl, $media->path_or_embed);
        $this->assertEquals('YouTube Video', $media->caption);
    }

    /** @test */
    public function vimeo_url_upload_works()
    {
        $this->actingAs($this->adminUser);
        
        $vimeoUrl = 'https://vimeo.com/123456789';
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadVideo($vimeoUrl, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertEquals($vimeoUrl, $media->path_or_embed);
        $this->assertEquals('Vimeo Video', $media->caption);
    }

    /** @test */
    public function invalid_video_url_is_rejected()
    {
        $this->actingAs($this->adminUser);
        
        $invalidUrl = 'https://example.com/video.mp4';
        $mediaService = new MediaService();
        
        try {
            $mediaService->uploadVideo($invalidUrl, $this->division);
            $this->fail('Expected exception for invalid video URL');
        } catch (\Exception $e) {
            $this->assertStringContains('Only YouTube and Vimeo URLs are allowed', $e->getMessage());
        }
    }

    /** @test */
    public function media_deletion_removes_files()
    {
        $this->actingAs($this->adminUser);
        
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        $filePath = $media->path_or_embed;
        
        $this->assertTrue(Storage::disk('public')->exists($filePath));
        
        $mediaService->deleteMedia($media);
        
        $this->assertFalse(Storage::disk('public')->exists($filePath));
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /** @test */
    public function storage_directory_structure_is_correct()
    {
        $this->actingAs($this->adminUser);
        
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        
        // Should follow pattern: media/{entity}/{YYYY}/{MM}/hash.ext
        $pattern = '/^media\/division\/\d{4}\/\d{2}\/[a-f0-9]{32}\.jpg$/';
        $this->assertMatchesRegularExpression($pattern, $media->path_or_embed);
    }

    /** @test */
    public function hashed_filename_is_generated_correctly()
    {
        $this->actingAs($this->adminUser);
        
        $mediaService = new MediaService();
        
        $filename1 = $mediaService->generateHashedFilename('test.jpg');
        $filename2 = $mediaService->generateHashedFilename('test.jpg');
        
        // Should be different each time
        $this->assertNotEquals($filename1, $filename2);
        
        // Should have correct format
        $this->assertStringEndsWith('.jpg', $filename1);
        $this->assertEquals(36, strlen($filename1)); // 32 chars + .jpg
        
        // Should be lowercase
        $this->assertEquals(strtolower($filename1), $filename1);
    }

    /** @test */
    public function file_size_limits_are_enforced()
    {
        $this->actingAs($this->adminUser);
        
        $mediaService = new MediaService();
        
        // Test image size limit (5MB)
        $largeImage = UploadedFile::fake()->create('large.jpg', 6 * 1024); // 6MB
        
        try {
            $mediaService->uploadImage($largeImage, $this->division);
            $this->fail('Expected exception for large image');
        } catch (\Exception $e) {
            $this->assertStringContains('Image file size must be less than 5MB', $e->getMessage());
        }
        
        // Test video size limit (50MB)
        $largeVideo = UploadedFile::fake()->create('large.mp4', 60 * 1024, 'video/mp4'); // 60MB
        
        try {
            $mediaService->uploadVideo($largeVideo, $this->division);
            $this->fail('Expected exception for large video');
        } catch (\Exception $e) {
            $this->assertStringContains('Video file size must be less than 50MB', $e->getMessage());
        }
    }

    /** @test */
    public function file_type_validation_works()
    {
        $this->actingAs($this->adminUser);
        
        $mediaService = new MediaService();
        
        // Test invalid image type
        $textFile = UploadedFile::fake()->create('test.txt', 100);
        
        try {
            $mediaService->uploadImage($textFile, $this->division);
            $this->fail('Expected exception for invalid image type');
        } catch (\Exception $e) {
            $this->assertStringContains('Only JPG, JPEG, PNG, and WebP images are allowed', $e->getMessage());
        }
        
        // Test invalid video type
        $aviFile = UploadedFile::fake()->create('test.avi', 1024, 'video/avi');
        
        try {
            $mediaService->uploadVideo($aviFile, $this->division);
            $this->fail('Expected exception for invalid video type');
        } catch (\Exception $e) {
            $this->assertStringContains('Only MP4 video files are allowed', $e->getMessage());
        }
    }
}