<?php

namespace Database\Factories;

use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(1),
            'type' => fake()->randomElement(['image', 'text']),
            'image_path' => null,
            'background_color' => '#1E3A5F',
            'text_color' => '#FFFFFF',
            'button_color' => '#CD0B6F',
            'button_text_color' => '#FFFFFF',
            'cta_text' => 'Learn More',
            'cta_action' => 'https://ommegashreepharmaceuticals.com',
            'target_role' => fake()->randomElement(['all', 'patient', 'doctor', 'pharmacy', 'shareholder']),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'display_order' => 0,
            'is_active' => true,
            'impressions_count' => 0,
            'clicks_count' => 0,
        ];
    }

    public function text(): static
    {
        return $this->state(fn () => [
            'type' => 'text',
            'image_path' => null,
            'background_color' => '#CD0B6F',
            'text_color' => '#FFFFFF',
            'button_color' => '#FFFFFF',
            'button_text_color' => '#CD0B6F',
        ]);
    }

    public function image(): static
    {
        return $this->state(fn () => [
            'type' => 'image',
            'image_path' => 'advertisements/sample.jpg',
        ]);
    }

    public function forPatient(): static
    {
        return $this->state(fn () => ['target_role' => 'patient']);
    }

    public function forDoctor(): static
    {
        return $this->state(fn () => ['target_role' => 'doctor']);
    }

    public function forPharmacy(): static
    {
        return $this->state(fn () => ['target_role' => 'pharmacy']);
    }

    public function forShareholder(): static
    {
        return $this->state(fn () => ['target_role' => 'shareholder']);
    }

    public function forAll(): static
    {
        return $this->state(fn () => ['target_role' => 'all']);
    }
}
