<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'type',
        'path_or_embed',
        'collection',
        'caption',
        'flags',
        'is_home_slider',
        'is_featured',
        'width',
        'height',
        'bytes',
        'order',
        'uploaded_by',
    ];

    protected $casts = [
        'is_home_slider' => 'boolean',
        'is_featured' => 'boolean',
        'type' => MediaType::class,
        'flags' => 'array',
    ];

    /**
     * Get the owning mediable model
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded this media
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the URL for this media
     */
    public function getUrlAttribute(): string
    {
        // Handle null or empty path
        if (empty($this->path_or_embed)) {
            return $this->getPlaceholderUrl();
        }

        if ($this->type === MediaType::VIDEO && str_contains($this->path_or_embed, 'http')) {
            return $this->path_or_embed;
        }

        // Check if file exists, return placeholder if not
        $storagePath = storage_path('app/public/' . $this->path_or_embed);
        if (! file_exists($storagePath)) {
            return $this->getPlaceholderUrl();
        }

        return asset('storage/' . $this->path_or_embed);
    }

    /**
     * Get placeholder URL for missing images
     */
    private function getPlaceholderUrl(): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="1920" height="1080" xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#e5e7eb"/>
                <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-family="Arial" font-size="48" fill="#6b7280">
                    ' . htmlspecialchars($this->caption ?: 'Image Placeholder') . '
                </text>
            </svg>'
        );
    }

    /**
     * Get WebP URL if available
     */
    public function getWebpUrlAttribute(): ?string
    {
        if ($this->type === MediaType::VIDEO) {
            return null;
        }

        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $this->path_or_embed);

        return asset('storage/' . $webpPath);
    }

    /**
     * Get responsive srcset attribute
     */
    public function getResponsiveSrcsetAttribute(): string
    {
        if ($this->type === MediaType::VIDEO) {
            return '';
        }

        $basePath = pathinfo($this->path_or_embed, PATHINFO_DIRNAME);
        $filename = pathinfo($this->path_or_embed, PATHINFO_FILENAME);
        $extension = pathinfo($this->path_or_embed, PATHINFO_EXTENSION);

        $srcset = [];
        $sizes = [768, 1280, 1920];

        foreach ($sizes as $size) {
            $path = $basePath . '/' . $filename . '-' . $size . 'w.' . $extension;
            $srcset[] = asset('storage/' . $path) . ' ' . $size . 'w';
        }

        return implode(', ', $srcset);
    }
}
