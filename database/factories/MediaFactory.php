<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'mediable_type' => Division::class,
            'mediable_id' => Division::factory(),
            'type' => $this->faker->randomElement(['image', 'video']),
            'path_or_embed' => function (array $attributes) {
                return $attributes['type'] === 'video' 
                    ? $this->faker->randomElement([
                        'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9]{11}'),
                        'https://vimeo.com/' . $this->faker->randomNumber(9),
                        'videos/' . $this->faker->uuid() . '.mp4'
                    ])
                    : 'images/' . $this->faker->uuid() . '.jpg';
            },
            'caption' => $this->faker->optional(0.7)->sentence(),
            'is_home_slider' => $this->faker->boolean(20),
            'is_featured' => $this->faker->boolean(30),
            'width' => function (array $attributes) {
                return $attributes['type'] === 'image' ? 1920 : null;
            },
            'height' => function (array $attributes) {
                return $attributes['type'] === 'image' ? 1080 : null;
            },
            'bytes' => $this->faker->numberBetween(100000, 5000000),
            'order' => $this->faker->numberBetween(1, 100),
            'uploaded_by' => User::factory(),
            'flags' => [],
        ];
    }

    public function image(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'image',
                'path_or_embed' => 'images/' . $this->faker->uuid() . '.jpg',
                'width' => 1920,
                'height' => 1080,
            ];
        });
    }

    public function video(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'video',
                'path_or_embed' => 'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9]{11}'),
                'width' => null,
                'height' => null,
            ];
        });
    }

    public function homeSlider(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'mediable_type' => 'home_slider',
                'mediable_id' => 0, // Use 0 instead of null for home slider
                'type' => 'image',
                'path_or_embed' => 'images/' . $this->faker->uuid() . '.jpg',
                'is_home_slider' => true,
                'is_featured' => true,
                'width' => 1920,
                'height' => 1080,
                'flags' => ['home_slider' => true],
            ];
        });
    }
}