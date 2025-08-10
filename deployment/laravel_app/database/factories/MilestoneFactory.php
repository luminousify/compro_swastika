<?php

namespace Database\Factories;

use App\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Factory;

class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        return [
            'year' => $this->faker->numberBetween(1990, 2024),
            'text' => $this->faker->sentence(10),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}