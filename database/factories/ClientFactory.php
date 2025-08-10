<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'logo_path' => 'clients/placeholder-' . $this->faker->randomNumber(3) . '.png',
            'url' => $this->faker->optional(0.7)->url(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}