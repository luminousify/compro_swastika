# MediaService Documentation

## Overview

The MediaService handles all media upload, processing, and management functionality for the DSP Company Profile Website. It provides comprehensive image and video handling with automatic optimization, validation, and storage management.

## Features

### Image Processing
- **File Validation**: Size limits (5MB), type validation (JPG, JPEG, PNG, WebP)
- **Dimension Validation**: Max 4096×4096 pixels, minimum width for hero/slider images (1200px)
- **Aspect Ratio Validation**: 16:9 ±10% tolerance for hero/slider images
- **EXIF Stripping**: Automatic removal of metadata for privacy and security
- **Responsive Variants**: Automatic generation of 768px, 1280px, and 1920px variants
- **WebP Generation**: Automatic WebP format generation when possible
- **Hashed Filenames**: Secure filename generation while preserving original names

### Video Processing
- **File Upload**: MP4 files up to 50MB with automatic thumbnail generation
- **URL Support**: YouTube and Vimeo URL validation and embedding
- **Thumbnail Generation**: Static poster images for video grid display

### Storage Management
- **Directory Structure**: Organized by entity type and date (media/{entity}/{YYYY}/{MM}/)
- **Symlink Fallback**: Automatic fallback to file copying if symlinks fail
- **Cleanup**: Complete file removal on media deletion including all variants

## Usage Examples

### Basic Image Upload
```php
use App\Services\MediaService;

$mediaService = new MediaService();
$media = $mediaService->uploadImage($uploadedFile, $entity);
```

### Hero Image Upload (with validation)
```php
$media = $mediaService->uploadImage($uploadedFile, $entity, 'hero');
```

### Video File Upload
```php
$media = $mediaService->uploadVideo($uploadedFile, $entity);
```

### Video URL Upload
```php
$youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$media = $mediaService->uploadVideo($youtubeUrl, $entity);
```

### Media Deletion
```php
$mediaService->deleteMedia($media);
```

## Validation Rules

### Images
- **File Size**: Maximum 5MB
- **File Types**: JPG, JPEG, PNG, WebP only
- **Dimensions**: Maximum 4096×4096 pixels
- **Hero/Slider Images**: 
  - Minimum width: 1200px
  - Aspect ratio: 16:9 ±10% tolerance

### Videos
- **File Size**: Maximum 50MB for uploaded files
- **File Types**: MP4 only for uploads
- **URLs**: YouTube and Vimeo domains only

## Storage Structure

Files are stored in the following structure:
```
storage/app/public/media/{entity}/{YYYY}/{MM}/{hash}.{ext}
```

Example:
```
storage/app/public/media/division/2025/01/a1b2c3d4e5f6...jpg
```

## Error Handling

The service throws descriptive exceptions for validation failures:
- File size exceeded
- Invalid file type
- Dimension requirements not met
- Aspect ratio validation failed
- Invalid video URL domain

## Testing

Comprehensive test coverage includes:
- Unit tests for all validation rules
- Feature tests for complete workflows
- Storage fallback mechanism testing
- Integration tests with Media model

Run tests with:
```bash
php artisan test tests/Unit/MediaServiceBasicTest.php
php artisan test tests/Unit/MediaValidationTest.php
php artisan test tests/Feature/MediaIntegrationTest.php
```

## Storage Setup

Use the provided command to set up storage with fallback:
```bash
php artisan storage:setup
```

This command will:
1. Create a symlink from `public/storage` to `storage/app/public`
2. Fall back to directory creation if symlinks are not supported
3. Provide appropriate feedback for shared hosting environments

## Requirements Covered

This implementation satisfies the following requirements:
- **8.1**: Image upload validation and processing
- **8.2**: EXIF stripping and orientation normalization
- **8.3**: Image resizing pipeline with WebP generation
- **8.4**: Video upload handling with URL validation
- **8.5**: Aspect ratio validation for hero/slider images
- **8.6**: Storage symlink with fallback mechanism
- **8.7**: Comprehensive testing coverage