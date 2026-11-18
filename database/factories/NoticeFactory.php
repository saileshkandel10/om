<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Notice> */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'notice_type' => fake()->randomElement(['general', 'agm', 'financial_report', 'announcement']),
            'audience_type' => fake()->randomElement(['all', 'shareholder', 'pharmacy', 'doctor']),
            'body' => fake()->paragraphs(3, true),
            'published_at' => now(),
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
