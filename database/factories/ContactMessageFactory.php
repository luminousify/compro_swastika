<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company' => $this->faker->optional(0.7)->company(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional(0.8)->safeEmail(),
            'subject' => $this->faker->sentence(4), // Add required subject field
            'message' => $this->faker->paragraphs(2, true),
            'handled' => $this->faker->boolean(30), // 30% handled
            'note' => function (array $attributes) {
                return $attributes['handled'] ? $this->faker->optional(0.5)->sentence() : null;
            },
            'created_by_ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    public function unhandled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'handled' => false,
                'note' => null,
            ];
        });
    }

    public function handled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'handled' => true,
                'note' => $this->faker->sentence(),
            ];
        });
    }
}