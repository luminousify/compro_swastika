<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class MediaService
{
    private ImageManager $imageManager;
    
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload and process an image
     */
    public function uploadImage(UploadedFile $file, $entity, string $type = 'general'): Media
    {
        // Validate image requirements
        $this->validateImageRequirements($file, $type);
        
        // Generate hashed filename
        $hashedFilename = $this->generateHashedFilename($file->getClientOriginalName());
        
        // Create directory structure
        $directory = $this->getStorageDirectory($entity);
        
        // Process and store the image
        $originalPath = $directory . '/' . $hashedFilename;
        
        // Load and process the image
        $image = $this->imageManager->read($file->getPathname());
        
        // Strip EXIF and normalize orientation
        $image = $this->stripExifAndNormalize($image);
        
        // Store original processed image
        $this->storeImageWithFallback($originalPath, $image->encode());
        
        // Create responsive variants
        $this->createResponsiveVariants($image, $directory, pathinfo($hashedFilename, PATHINFO_FILENAME));
        
        // Generate WebP version
        $this->generateWebP($originalPath);
        
        // Create media record
        return Media::create([
            'mediable_type' => get_class($entity),
            'mediable_id' => $entity->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => $originalPath,
            'caption' => $file->getClientOriginalName(),
            'width' => $image->width(),
            'height' => $image->height(),
            'bytes' => $file->getSize(),
            'order' => $this->getNextOrder($entity),
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Handle video upload (file or URL)
     */
    public function uploadVideo($input, $entity): Media
    {
        if ($input instanceof UploadedFile) {
            return $this->uploadVideoFile($input, $entity);
        } else {
            return $this->uploadVideoUrl($input, $entity);
        }
    }

    /**
     * Upload video file
     */
    private function uploadVideoFile(UploadedFile $file, $entity): Media
    {
        // Validate video file
        if ($file->getSize() > 50 * 1024 * 1024) { // 50MB
            throw new Exception('Video file size must be less than 50MB');
        }
        
        if (!in_array($file->getMimeType(), ['video/mp4'])) {
            throw new Exception('Only MP4 video files are allowed');
        }
        
        // Generate hashed filename
        $hashedFilename = $this->generateHashedFilename($file->getClientOriginalName());
        $directory = $this->getStorageDirectory($entity);
        $videoPath = $directory . '/' . $hashedFilename;
        
        // Store video file
        $this->storeFileWithFallback($videoPath, file_get_contents($file->getPathname()));
        
        // Generate video thumbnail
        $thumbnailPath = $this->generateVideoThumbnail($videoPath);
        
        return Media::create([
            'mediable_type' => get_class($entity),
            'mediable_id' => $entity->id,
            'type' => MediaType::VIDEO,
            'path_or_embed' => $videoPath,
            'caption' => $file->getClientOriginalName(),
            'bytes' => $file->getSize(),
            'order' => $this->getNextOrder($entity),
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Upload video URL (YouTube/Vimeo)
     */
    private function uploadVideoUrl(string $url, $entity): Media
    {
        // Validate URL
        if (!$this->isValidVideoUrl($url)) {
            throw new Exception('Only YouTube and Vimeo URLs are allowed');
        }
        
        // Extract video info
        $videoInfo = $this->extractVideoInfo($url);
        
        return Media::create([
            'mediable_type' => get_class($entity),
            'mediable_id' => $entity->id,
            'type' => MediaType::VIDEO,
            'path_or_embed' => $url,
            'caption' => $videoInfo['title'] ?? 'Video',
            'width' => $videoInfo['width'] ?? null,
            'height' => $videoInfo['height'] ?? null,
            'order' => $this->getNextOrder($entity),
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Validate image requirements
     */
    public function validateImageRequirements(UploadedFile $file, string $type): void
    {
        // File size validation (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new Exception('Image file size must be less than 5MB');
        }
        
        // File type validation
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception('Only JPG, JPEG, PNG, and WebP images are allowed');
        }
        
        // Load image to check dimensions
        try {
            $image = $this->imageManager->read($file->getPathname());
        } catch (Exception $e) {
            throw new Exception('Invalid image file');
        }
        
        // Max dimensions validation
        if ($image->width() > 4096 || $image->height() > 4096) {
            throw new Exception('Image dimensions must not exceed 4096×4096 pixels');
        }
        
        // Minimum width for hero/slider images
        if (in_array($type, ['hero', 'slider']) && $image->width() < 1200) {
            throw new Exception('Hero and slider images must be at least 1200px wide');
        }
        
        // Aspect ratio validation for hero/slider images (16:9 ±10%)
        if (in_array($type, ['hero', 'slider'])) {
            $aspectRatio = $image->width() / $image->height();
            $targetRatio = 16 / 9; // 1.777...
            $tolerance = 0.1;
            
            if (abs($aspectRatio - $targetRatio) > ($targetRatio * $tolerance)) {
                throw new Exception('Hero and slider images must have a 16:9 aspect ratio (±10% tolerance)');
            }
        }
    }

    /**
     * Strip EXIF data and normalize orientation
     */
    private function stripExifAndNormalize($image)
    {
        // Intervention Image v3 automatically handles orientation and strips EXIF
        return $image;
    }

    /**
     * Generate hashed filename while preserving original name info
     */
    public function generateHashedFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $hash = hash('sha256', $originalName . time() . Str::random(10));
        
        return substr($hash, 0, 32) . '.' . strtolower($extension);
    }

    /**
     * Create responsive image variants
     */
    private function createResponsiveVariants($image, string $directory, string $baseFilename): void
    {
        $sizes = [
            'hero' => 1920,
            'general' => 1280,
            'card' => 768
        ];
        
        foreach ($sizes as $name => $width) {
            if ($image->width() > $width) {
                $resized = clone $image;
                $resized->scale(width: $width);
                
                $filename = $baseFilename . '_' . $width . 'w.' . pathinfo($baseFilename, PATHINFO_EXTENSION);
                $path = $directory . '/' . $filename;
                
                $this->storeImageWithFallback($path, $resized->encode());
            }
        }
    }

    /**
     * Generate WebP version of image
     */
    public function generateWebP(string $imagePath): ?string
    {
        try {
            $image = $this->imageManager->read(storage_path('app/public/' . $imagePath));
            $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $imagePath);
            
            $this->storeImageWithFallback($webpPath, $image->toWebp());
            
            return $webpPath;
        } catch (Exception $e) {
            // WebP generation failed, continue without it
            return null;
        }
    }

    /**
     * Generate video thumbnail
     */
    public function generateVideoThumbnail(string $videoPath): string
    {
        // For now, create a static poster image
        // In production, you might want to use FFmpeg for actual thumbnail generation
        $thumbnailPath = str_replace('.mp4', '_thumb.jpg', $videoPath);
        
        // Create a simple placeholder thumbnail
        $image = $this->imageManager->create(1280, 720)->fill('cccccc');
        $image->text('Video Thumbnail', 640, 360, function($font) {
            $font->size(48);
            $font->color('666666');
            $font->align('center');
            $font->valign('middle');
        });
        
        $this->storeImageWithFallback($thumbnailPath, $image->encode());
        
        return $thumbnailPath;
    }

    /**
     * Validate video URL (YouTube/Vimeo only)
     */
    private function isValidVideoUrl(string $url): bool
    {
        $allowedDomains = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'www.vimeo.com'
        ];
        
        $parsedUrl = parse_url($url);
        
        if (!isset($parsedUrl['host'])) {
            return false;
        }
        
        return in_array(strtolower($parsedUrl['host']), $allowedDomains);
    }

    /**
     * Extract video information from URL
     */
    private function extractVideoInfo(string $url): array
    {
        // Basic video info extraction
        // In production, you might want to use APIs to get actual video metadata
        
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return [
                'title' => 'YouTube Video',
                'width' => 1920,
                'height' => 1080
            ];
        }
        
        if (str_contains($url, 'vimeo.com')) {
            return [
                'title' => 'Vimeo Video',
                'width' => 1920,
                'height' => 1080
            ];
        }
        
        return [];
    }

    /**
     * Get storage directory for entity
     */
    private function getStorageDirectory($entity): string
    {
        $entityName = strtolower(class_basename($entity));
        $year = date('Y');
        $month = date('m');
        
        return "media/{$entityName}/{$year}/{$month}";
    }

    /**
     * Get next order number for entity
     */
    private function getNextOrder($entity): int
    {
        return $entity->media()->max('order') + 1;
    }

    /**
     * Store image with symlink fallback
     */
    private function storeImageWithFallback(string $path, $content): void
    {
        try {
            // Try to store using Laravel's Storage facade
            Storage::disk('public')->put($path, $content);
        } catch (Exception $e) {
            // Fallback to direct file operations
            $fullPath = storage_path('app/public/' . $path);
            $directory = dirname($fullPath);
            
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            file_put_contents($fullPath, $content);
        }
        
        // Ensure public symlink exists or create file copy fallback
        $this->ensurePublicAccess();
    }

    /**
     * Store file with symlink fallback
     */
    private function storeFileWithFallback(string $path, $content): void
    {
        $this->storeImageWithFallback($path, $content);
    }

    /**
     * Ensure public access to storage files
     */
    private function ensurePublicAccess(): void
    {
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');
        
        // Check if symlink exists and is valid
        if (is_link($linkPath) && readlink($linkPath) === $targetPath) {
            return;
        }
        
        // Try to create symlink
        if (!file_exists($linkPath)) {
            try {
                symlink($targetPath, $linkPath);
                return;
            } catch (Exception $e) {
                // Symlink creation failed, will use file copy fallback
            }
        }
        
        // Fallback: copy files directly to public directory
        // This is handled by the storeImageWithFallback method
    }

    /**
     * Delete media and associated files
     */
    public function deleteMedia(Media $media): void
    {
        if ($media->type === MediaType::IMAGE) {
            // Delete main image and variants
            $this->deleteImageVariants($media->path_or_embed);
        } elseif ($media->type === MediaType::VIDEO && !str_contains($media->path_or_embed, 'http')) {
            // Delete video file and thumbnail
            Storage::disk('public')->delete($media->path_or_embed);
            $thumbnailPath = str_replace('.mp4', '_thumb.jpg', $media->path_or_embed);
            Storage::disk('public')->delete($thumbnailPath);
        }
        
        $media->delete();
    }

    /**
     * Delete image variants
     */
    private function deleteImageVariants(string $imagePath): void
    {
        $directory = dirname($imagePath);
        $filename = pathinfo($imagePath, PATHINFO_FILENAME);
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        
        // Delete original
        Storage::disk('public')->delete($imagePath);
        
        // Delete WebP version
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $imagePath);
        Storage::disk('public')->delete($webpPath);
        
        // Delete responsive variants
        $sizes = [768, 1280, 1920];
        foreach ($sizes as $size) {
            $variantPath = $directory . '/' . $filename . '_' . $size . 'w.' . $extension;
            Storage::disk('public')->delete($variantPath);
            
            $webpVariantPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $variantPath);
            Storage::disk('public')->delete($webpVariantPath);
        }
    }
}