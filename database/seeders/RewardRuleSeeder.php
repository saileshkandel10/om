<?php

namespace Database\Seeders;

use App\Models\IllnessCategory;
use App\Models\RewardRule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RewardRuleSeeder extends Seeder
{
    public function run(): void
    {
        $endocrineId = IllnessCategory::where('name', 'Endocrine')->value('id');

        $rules = [
            // ── Shareholder Rules ──
            [
                'name' => 'Shareholder Purchase Bonus',
                'user_type' => 'shareholder',
                'basis_type' => 'purchase_amount',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => null,
                'min_amount' => 1000,
                'point_rate' => 10,
                'bonus_type' => 'percentage',
                'bonus_value' => 5,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],
            [
                'name' => 'Shareholder Loyalty Points',
                'user_type' => 'shareholder',
                'basis_type' => 'quantity',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => 5,
                'min_amount' => null,
                'point_rate' => 15,
                'bonus_type' => 'fixed',
                'bonus_value' => 50,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],

            // ── Pharmacy Rules ──
            [
                'name' => 'Pharmacy Bulk Order Bonus',
                'user_type' => 'pharmacy',
                'basis_type' => 'purchase_amount',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => null,
                'min_amount' => 5000,
                'point_rate' => 20,
                'bonus_type' => 'percentage',
                'bonus_value' => 8,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],
            [
                'name' => 'Pharmacy New Product Incentive',
                'user_type' => 'pharmacy',
                'basis_type' => 'quantity',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => 20,
                'min_amount' => null,
                'point_rate' => 25,
                'bonus_type' => 'fixed',
                'bonus_value' => 100,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],

            // ── Doctor Rules ──
            [
                'name' => 'Doctor Prescription Reward',
                'user_type' => 'doctor',
                'basis_type' => 'prescription',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => null,
                'min_amount' => null,
                'point_rate' => 50,
                'bonus_type' => 'fixed',
                'bonus_value' => 25,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],
            [
                'name' => 'Doctor Specialty Bonus',
                'user_type' => 'doctor',
                'basis_type' => 'prescription',
                'product_id' => null,
                'illness_category_id' => $endocrineId,
                'min_qty' => null,
                'min_amount' => null,
                'point_rate' => 75,
                'bonus_type' => 'multiplier',
                'bonus_value' => 2,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],

            // ── Customer Rules ──
            [
                'name' => 'Customer First Purchase',
                'user_type' => 'customer',
                'basis_type' => 'purchase_amount',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => null,
                'min_amount' => null,
                'point_rate' => 5,
                'bonus_type' => 'fixed',
                'bonus_value' => 100,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],
            [
                'name' => 'Customer Quantity Discount',
                'user_type' => 'customer',
                'basis_type' => 'quantity',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => 3,
                'min_amount' => null,
                'point_rate' => 8,
                'bonus_type' => 'percentage',
                'bonus_value' => 10,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],
            [
                'name' => 'Customer Seasonal Campaign',
                'user_type' => 'customer',
                'basis_type' => 'purchase_amount',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => null,
                'min_amount' => 2000,
                'point_rate' => 12,
                'bonus_type' => 'multiplier',
                'bonus_value' => 3,
                'start_date' => Carbon::now()->subMonth(),
                'end_date' => Carbon::now()->addMonth(),
                'is_active' => true,
            ],

            // ── Product/Illness-specific ──
            [
                'name' => 'Diabetes Care Bonus',
                'user_type' => 'customer',
                'basis_type' => 'purchase_amount',
                'product_id' => null,
                'illness_category_id' => $endocrineId,
                'min_qty' => null,
                'min_amount' => 500,
                'point_rate' => 15,
                'bonus_type' => 'percentage',
                'bonus_value' => 5,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'is_active' => true,
            ],

            // ── Inactive / Past Rules ──
            [
                'name' => 'Legacy New Year Bonus',
                'user_type' => 'customer',
                'basis_type' => 'purchase_amount',
                'product_id' => null,
                'illness_category_id' => null,
                'min_qty' => null,
                'min_amount' => 1000,
                'point_rate' => 10,
                'bonus_type' => 'fixed',
                'bonus_value' => 200,
                'start_date' => Carbon::now()->subMonths(13),
                'end_date' => Carbon::now()->subMonths(1),
                'is_active' => false,
            ],
        ];

        foreach ($rules as $rule) {
            RewardRule::firstOrCreate(
                ['name' => $rule['name']],
                $rule,
            );
        }

        $this->command->info('Seeded '.count($rules).' reward rules.');
    }
}
