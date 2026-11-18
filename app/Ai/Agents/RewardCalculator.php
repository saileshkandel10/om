<?php

namespace App\Ai\Agents;

use App\Models\RewardRule;
use Illuminate\Support\Facades\Log;

class RewardCalculator
{
    /**
     * Calculate rewards for a given receipt and user role.
     *
     * @param  array  $receiptData  Parsed receipt data from ReceiptParser
     * @param  string  $userRole  The user's role (shareholder, pharmacy, doctor, customer)
     * @return array{applicable_rules: array, total_points: float, total_bonus: float, total_royalty: float, summary: string}
     */
    public function calculate(array $receiptData, string $userRole): array
    {
        $totalAmount = (float) ($receiptData['total_amount'] ?? 0);
        $itemCount = 0;
        if (isset($receiptData['items']) && is_array($receiptData['items'])) {
            foreach ($receiptData['items'] as $item) {
                $itemCount += (float) ($item['quantity'] ?? 1);
            }
        }

        // Fetch applicable active rules for this user type
        $rules = RewardRule::where('is_active', true)
            ->where(function ($q) use ($userRole) {
                $q->where('user_type', $userRole)
                    ->orWhere('user_type', 'all');
            })
            ->get();

        $totalPoints = 0.0;
        $totalBonus = 0.0;
        $applicableRules = [];

        foreach ($rules as $rule) {
            $matched = false;
            $earned = 0.0;

            if ($rule->basis_type === 'purchase_amount' && $totalAmount >= ($rule->min_amount ?? 0)) {
                $earned = $totalAmount * ((float) $rule->point_rate / 100);
                $matched = true;
            } elseif ($rule->basis_type === 'quantity' && $itemCount >= ($rule->min_qty ?? 0)) {
                $earned = $itemCount * (float) $rule->point_rate;
                $matched = true;
            }

            if ($matched) {
                $bonus = 0.0;
                if ($rule->bonus_type === 'percentage') {
                    $bonus = $earned * ((float) $rule->bonus_value / 100);
                } elseif ($rule->bonus_type === 'fixed') {
                    $bonus = (float) $rule->bonus_value;
                }

                $totalPoints += $earned;
                $totalBonus += $bonus;

                $applicableRules[] = [
                    'rule_name' => $rule->name,
                    'reason' => "Applied rule based on {$rule->basis_type}",
                    'points_earned' => $earned,
                    'bonus_amount' => $bonus,
                ];
            }
        }

        // Default points calculation if no explicit rule matched
        if ($totalPoints == 0 && $totalAmount > 0) {
            $totalPoints = $totalAmount * 0.10; // 10% loyalty point rate default
            $totalBonus = $totalPoints * 0.20; // 20% bonus default
            $applicableRules[] = [
                'rule_name' => 'Standard Loyalty Reward',
                'reason' => 'Standard 10% point accrual on purchase amount',
                'points_earned' => $totalPoints,
                'bonus_amount' => $totalBonus,
            ];
        }

        $totalRoyalty = $totalAmount * 0.02; // 2% shareholder royalty

        $result = [
            'applicable_rules' => $applicableRules,
            'total_points' => round($totalPoints, 2),
            'total_bonus' => round($totalBonus, 2),
            'total_royalty' => round($totalRoyalty, 2),
            'summary' => 'Calculated successfully via OM Connect Rules Engine.',
        ];

        Log::info('RewardCalculator: Calculation complete', $result);

        return $result;
    }
}
