<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Prescription> */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'patient_ref_code' => 'PAT-'.fake()->numerify('####'),
            'prescription_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'source_type' => 'manual',
            'uploaded_file_id' => null,
            'parsed_status' => 'completed',
            'confidence_score' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
