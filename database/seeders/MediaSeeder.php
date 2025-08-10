<?php

namespace Database\Seeders;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();

        // Create placeholder slider images
        $sliderImages = [
            [
                'mediable_type' => 'App\Models\Setting',
                'mediable_id' => 1,
                'type' => MediaType::IMAGE,
                'path_or_embed' => 'placeholders/slider-1.jpg',
                'caption' => 'PT. Daya Swastika Perkasa - Solusi Terpadu Industri',
                'is_home_slider' => true,
                'is_featured' => false,
                'width' => 1920,
                'height' => 1080,
                'bytes' => 500000,
                'order' => 1,
                'uploaded_by' => $adminUser->id,
            ],
            [
                'mediable_type' => 'App\Models\Setting',
                'mediable_id' => 1,
                'type' => MediaType::IMAGE,
                'path_or_embed' => 'placeholders/slider-2.jpg',
                'caption' => 'Grundfos Pump Solutions - Kualitas Terpercaya',
                'is_home_slider' => true,
                'is_featured' => false,
                'width' => 1920,
                'height' => 1080,
                'bytes' => 480000,
                'order' => 2,
                'uploaded_by' => $adminUser->id,
            ],
            [
                'mediable_type' => 'App\Models\Setting',
                'mediable_id' => 1,
                'type' => MediaType::IMAGE,
                'path_or_embed' => 'placeholders/slider-3.jpg',
                'caption' => 'Automotive Parts & Accessories',
                'is_home_slider' => true,
                'is_featured' => false,
                'width' => 1920,
                'height' => 1080,
                'bytes' => 520000,
                'order' => 3,
                'uploaded_by' => $adminUser->id,
            ],
            [
                'mediable_type' => 'App\Models\Setting',
                'mediable_id' => 1,
                'type' => MediaType::IMAGE,
                'path_or_embed' => 'placeholders/slider-4.jpg',
                'caption' => 'Fabrication & Automation Services',
                'is_home_slider' => true,
                'is_featured' => false,
                'width' => 1920,
                'height' => 1080,
                'bytes' => 510000,
                'order' => 4,
                'uploaded_by' => $adminUser->id,
            ],
        ];

        foreach ($sliderImages as $image) {
            Media::create($image);
        }
    }
}
