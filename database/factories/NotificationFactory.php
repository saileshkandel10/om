<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Notification> */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'channel' => fake()->randomElement(['in_app', 'push', 'email']),
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => now(),
        ]);
    }
}
