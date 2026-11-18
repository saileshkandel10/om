<?php

namespace Database\Factories;

use App\Models\RewardWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RewardWallet> */
class RewardWalletFactory extends Factory
{
    protected $model = RewardWallet::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_points' => fake()->randomFloat(2, 0, 5000),
            'total_bonus' => fake()->randomFloat(2, 0, 1000),
            'total_royalty' => fake()->randomFloat(2, 0, 500),
        ];
    }

    public function empty(): static
    {
        return $this->state(fn () => [
            'total_points' => 0,
            'total_bonus' => 0,
            'total_royalty' => 0,
        ]);
    }
}
