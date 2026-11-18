<?php

namespace Database\Factories;

use App\Models\ShareholderMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ShareholderMaster> */
class ShareholderMasterFactory extends Factory
{
    protected $model = ShareholderMaster::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'boid' => fake()->unique()->numerify('################'),
            'shareholder_name' => fake()->name(),
            'mobile' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'status' => 'active',
            'source_note' => 'Factory generated',
        ];
    }
}
