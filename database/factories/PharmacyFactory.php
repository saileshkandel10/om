<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Pharmacy> */
class PharmacyFactory extends Factory
{
    protected $model = Pharmacy::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_name' => fake()->company().' Pharmacy',
            'license_no' => 'PHR-'.fake()->unique()->numerify('#####'),
            'pan_vat_no' => fake()->numerify('#########'),
            'owner_name' => fake()->name(),
            'district' => fake()->randomElement(['Kathmandu', 'Lalitpur', 'Bhaktapur', 'Pokhara', 'Biratnagar']),
            'address' => fake()->address(),
            'status' => 'active',
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'approved_at' => null,
        ]);
    }
}
