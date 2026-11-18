<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use App\Models\PurchaseBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PurchaseBill> */
class PurchaseBillFactory extends Factory
{
    protected $model = PurchaseBill::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pharmacy_id' => Pharmacy::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'invoice_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'subtotal_amount' => $subtotal = fake()->randomFloat(2, 100, 5000),
            'tax_amount' => round($subtotal * 0.13, 2),
            'total_amount' => round($subtotal * 1.13, 2),
            'ocr_status' => 'pending',
            'validation_status' => 'pending',
            'confidence_score' => null,
            'duplicate_flag' => false,
            'fraud_flag' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'ocr_status' => 'completed',
            'validation_status' => 'approved',
            'confidence_score' => fake()->randomFloat(2, 80, 99),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'ocr_status' => 'failed',
        ]);
    }
}
