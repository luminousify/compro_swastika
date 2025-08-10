<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'division_id' => Division::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraphs(2, true),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}