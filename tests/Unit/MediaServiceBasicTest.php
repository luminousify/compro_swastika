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

class MediaServiceBasicTest extends TestCase
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

    public function test_it_can_upload_a_basic_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::IMAGE, $media->type);
        $this->assertEquals($this->division->id, $media->mediable_id);
        $this->assertEquals($this->user->id, $media->uploaded_by);
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
    }

    public function test_it_validates_image_file_size()
    {
        $file = UploadedFile::fake()->create('large.jpg', 6 * 1024); // 6MB
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Image file size must be less than 5MB');
        
        $this->mediaService->uploadImage($file, $this->division);
    }

    public function test_it_validates_image_file_type()
    {
        $file = UploadedFile::fake()->create('test.txt', 100);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only JPG, JPEG, PNG, and WebP images are allowed');
        
        $this->mediaService->uploadImage($file, $this->division);
    }

    public function test_it_generates_hashed_filename()
    {
        $originalName = 'test-image.jpg';
        $hashedName = $this->mediaService->generateHashedFilename($originalName);
        
        $this->assertStringEndsWith('.jpg', $hashedName);
        $this->assertEquals(36, strlen($hashedName)); // 32 char hash + .jpg
        $this->assertNotEquals($originalName, $hashedName);
    }

    public function test_it_can_upload_youtube_url()
    {
        $youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        
        $media = $this->mediaService->uploadVideo($youtubeUrl, $this->division);
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertEquals($youtubeUrl, $media->path_or_embed);
        $this->assertEquals('YouTube Video', $media->caption);
    }

    public function test_it_validates_video_url_domain()
    {
        $invalidUrl = 'https://example.com/video.mp4';
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only YouTube and Vimeo URLs are allowed');
        
        $this->mediaService->uploadVideo($invalidUrl, $this->division);
    }

    public function test_it_creates_proper_storage_directory_structure()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        $expectedPattern = '/^media\/division\/\d{4}\/\d{2}\/[a-f0-9]{32}\.jpg$/';
        $this->assertMatchesRegularExpression($expectedPattern, $media->path_or_embed);
    }

    public function test_it_sets_correct_order_for_new_media()
    {
        $file1 = UploadedFile::fake()->image('test1.jpg', 400, 300);
        $file2 = UploadedFile::fake()->image('test2.jpg', 400, 300);
        
        $media1 = $this->mediaService->uploadImage($file1, $this->division);
        $media2 = $this->mediaService->uploadImage($file2, $this->division);
        
        $this->assertEquals(1, $media1->order);
        $this->assertEquals(2, $media2->order);
    }
}