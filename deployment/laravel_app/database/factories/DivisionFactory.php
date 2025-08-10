<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        
        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'description' => $this->faker->paragraph(),
            'hero_image_path' => null,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}