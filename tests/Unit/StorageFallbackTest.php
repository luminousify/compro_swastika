<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Division;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageFallbackTest extends TestCase
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

    public function test_media_service_handles_storage_gracefully()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        
        // This should not throw any exceptions even if storage has issues
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        $this->assertNotNull($media);
        $this->assertNotEmpty($media->path_or_embed);
        $this->assertTrue(Storage::disk('public')->exists($media->path_or_embed));
    }

    public function test_storage_directory_structure_is_created()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        
        $media = $this->mediaService->uploadImage($file, $this->division);
        
        // Verify the directory structure follows the expected pattern
        $pattern = '/^media\/division\/\d{4}\/\d{2}\/[a-f0-9]{32}\.jpg$/';
        $this->assertMatchesRegularExpression($pattern, $media->path_or_embed);
        
        // Verify the directory was created
        $directory = dirname($media->path_or_embed);
        $this->assertTrue(Storage::disk('public')->exists($directory));
    }

    public function test_hashed_filename_generation_is_consistent()
    {
        $mediaService = new MediaService();
        
        $filename1 = $mediaService->generateHashedFilename('test.jpg');
        $filename2 = $mediaService->generateHashedFilename('test.jpg');
        
        // Should be different each time due to time and random components
        $this->assertNotEquals($filename1, $filename2);
        
        // But should follow the same format
        $this->assertStringEndsWith('.jpg', $filename1);
        $this->assertStringEndsWith('.jpg', $filename2);
        $this->assertEquals(36, strlen($filename1));
        $this->assertEquals(36, strlen($filename2));
    }

    public function test_media_deletion_cleans_up_properly()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        
        $media = $this->mediaService->uploadImage($file, $this->division);
        $filePath = $media->path_or_embed;
        
        // Verify file exists
        $this->assertTrue(Storage::disk('public')->exists($filePath));
        
        // Delete media
        $this->mediaService->deleteMedia($media);
        
        // Verify file is deleted
        $this->assertFalse(Storage::disk('public')->exists($filePath));
        
        // Verify database record is deleted
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }
}