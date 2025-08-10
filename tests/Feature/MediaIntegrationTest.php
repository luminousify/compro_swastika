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

class MediaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->division = Division::factory()->create();
        
        Storage::fake('public');
    }

    public function test_complete_image_upload_workflow()
    {
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->image('test-photo.jpg', 800, 600);
        $mediaService = new MediaService();
        
        // Upload image
        $media = $mediaService->uploadImage($file, $this->division);
        
        // Verify media record
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::IMAGE, $media->type);
        $this->assertEquals('test-photo.jpg', $media->caption);
        $this->assertEquals($this->user->id, $media->uploaded_by);
        $this->assertEquals($this->division->id, $media->mediable_id);
        $this->assertEquals(Division::class, $media->mediable_type);
        
        // Verify file storage
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
        
        // Verify relationships work
        $this->assertEquals($this->user->id, $media->uploader->id);
        $this->assertEquals($this->division->id, $media->mediable->id);
        
        // Verify division has the media
        $this->assertTrue($this->division->media->contains($media));
        
        // Test URL generation (may be placeholder if file doesn't exist in fake storage)
        $url = $media->url;
        $this->assertNotEmpty($url);
        
        // Test responsive srcset (should be empty for small images)
        $srcset = $media->responsive_srcset;
        $this->assertIsString($srcset);
    }

    public function test_complete_video_url_workflow()
    {
        $this->actingAs($this->user);
        
        $youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $mediaService = new MediaService();
        
        // Upload video URL
        $media = $mediaService->uploadVideo($youtubeUrl, $this->division);
        
        // Verify media record
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(MediaType::VIDEO, $media->type);
        $this->assertEquals($youtubeUrl, $media->path_or_embed);
        $this->assertEquals('YouTube Video', $media->caption);
        
        // Verify URL returns the original URL for videos
        $this->assertEquals($youtubeUrl, $media->url);
        
        // Verify WebP URL is null for videos
        $this->assertNull($media->webp_url);
        
        // Verify responsive srcset is empty for videos
        $this->assertEquals('', $media->responsive_srcset);
    }

    public function test_media_ordering_works_correctly()
    {
        $this->actingAs($this->user);
        
        $mediaService = new MediaService();
        
        // Upload multiple media items
        $file1 = UploadedFile::fake()->image('first.jpg', 400, 300);
        $file2 = UploadedFile::fake()->image('second.jpg', 400, 300);
        $youtubeUrl = 'https://www.youtube.com/watch?v=test123';
        
        $media1 = $mediaService->uploadImage($file1, $this->division);
        $media2 = $mediaService->uploadImage($file2, $this->division);
        $media3 = $mediaService->uploadVideo($youtubeUrl, $this->division);
        
        // Verify ordering
        $this->assertEquals(1, $media1->order);
        $this->assertEquals(2, $media2->order);
        $this->assertEquals(3, $media3->order);
        
        // Verify division media collection is ordered
        $divisionMedia = $this->division->fresh()->media()->orderBy('order')->get();
        $this->assertEquals($media1->id, $divisionMedia[0]->id);
        $this->assertEquals($media2->id, $divisionMedia[1]->id);
        $this->assertEquals($media3->id, $divisionMedia[2]->id);
    }

    public function test_media_deletion_workflow()
    {
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->image('delete-me.jpg', 400, 300);
        $mediaService = new MediaService();
        
        // Upload and verify
        $media = $mediaService->uploadImage($file, $this->division);
        $mediaId = $media->id;
        $filePath = $media->path_or_embed;
        
        $this->assertTrue(Storage::disk('public')->exists($filePath));
        $this->assertDatabaseHas('media', ['id' => $mediaId]);
        
        // Delete and verify cleanup
        $mediaService->deleteMedia($media);
        
        $this->assertFalse(Storage::disk('public')->exists($filePath));
        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    public function test_media_model_accessors_work()
    {
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->image('accessor-test.jpg', 400, 300);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        
        // Test URL accessor
        $url = $media->url;
        $this->assertNotEmpty($url);
        
        // Test WebP URL accessor
        $webpUrl = $media->webp_url;
        $this->assertIsString($webpUrl);
        $this->assertStringContainsString('.webp', $webpUrl);
        
        // Test responsive srcset accessor
        $srcset = $media->responsive_srcset;
        $this->assertIsString($srcset);
    }

    public function test_polymorphic_relationships_work()
    {
        $this->actingAs($this->user);
        
        $file = UploadedFile::fake()->image('poly-test.jpg', 400, 300);
        $mediaService = new MediaService();
        
        $media = $mediaService->uploadImage($file, $this->division);
        
        // Test polymorphic relationship
        $this->assertEquals(Division::class, $media->mediable_type);
        $this->assertEquals($this->division->id, $media->mediable_id);
        $this->assertInstanceOf(Division::class, $media->mediable);
        $this->assertEquals($this->division->name, $media->mediable->name);
        
        // Test reverse relationship
        $this->assertTrue($this->division->media->contains($media));
    }
}