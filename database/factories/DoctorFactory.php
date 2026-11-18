<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Doctor> */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'registration_no' => 'NMC-'.fake()->unique()->numerify('####'),
            'specialty' => fake()->randomElement(['General', 'Cardiology', 'Dermatology', 'Neurology', 'Orthopedics', 'Pediatrics']),
            'hospital_clinic' => fake()->company().' Hospital',
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
