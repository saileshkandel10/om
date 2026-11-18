<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campaign> */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Campaign',
            'campaign_type' => fake()->randomElement(['promotional', 'awareness', 'seasonal']),
            'target_segment' => fake()->randomElement(['all', 'shareholder', 'pharmacy', 'doctor']),
            'description' => fake()->paragraph(),
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(30),
            'status' => 'active',
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(30),
            'status' => 'completed',
        ]);
    }
}
