<?php

use App\Ai\Agents\RewardCalculator;
use App\Models\RewardRule;

test('reward calculator includes global active rules in the prompt', function () {
    RewardCalculator::fake([
        json_encode([
            'applicable_rules' => [],
            'total_points' => 0,
            'total_bonus' => 0,
            'total_royalty' => 0,
            'summary' => 'No matching rules.',
        ]),
    ]);

    $rule = RewardRule::create([
        'name' => 'Global rule',
        'user_type' => 'all',
        'basis_type' => 'amount',
        'min_amount' => 100,
        'point_rate' => 1,
        'bonus_type' => 'fixed',
        'bonus_value' => 10,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'is_active' => true,
    ]);

    $result = (new RewardCalculator)->calculate([
        'invoice_number' => 'INV-001',
        'total_amount' => 150,
        'pharmacy_name' => 'Test Pharmacy',
        'items' => [],
    ], 'customer');

    expect($result['summary'])->toBe('No matching rules. Basic royalty (2%) applied as fallback.');

    RewardCalculator::assertPrompted(fn ($prompt) => $prompt->contains($rule->name)
        && $prompt->contains('User Role: customer')
    );
});
