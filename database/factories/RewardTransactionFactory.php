<?php

namespace Database\Factories;

use App\Models\RewardTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RewardTransaction> */
class RewardTransactionFactory extends Factory
{
    protected $model = RewardTransaction::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'purchase_bill_id' => null,
            'reward_rule_id' => null,
            'points_earned' => fake()->randomFloat(2, 1, 500),
            'bonus_amount' => fake()->randomFloat(2, 0, 100),
            'royalty_amount' => fake()->randomFloat(2, 0, 50),
            'transaction_type' => 'earned',
            'status' => 'completed',
        ];
    }
}
