<?php

namespace Database\Factories;

use App\Models\ShareholderAccount;
use App\Models\ShareholderMaster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ShareholderAccount> */
class ShareholderAccountFactory extends Factory
{
    protected $model = ShareholderAccount::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shareholder_master_id' => ShareholderMaster::factory(),
            'boid' => fake()->unique()->numerify('################'),
            'shares_owned' => fake()->randomFloat(2, 100, 10000),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'verification_status' => 'pending',
            'verified_at' => null,
        ]);
    }
}
