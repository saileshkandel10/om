<?php

namespace App\Services;

use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\RewardRule;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use App\Models\User;

class RewardCalculationService
{
    /**
     * Calculate and issue rewards for a purchase bill.
     */
    public function processBill(PurchaseBill $bill): void
    {
        $user = $bill->user;
        $userType = $this->getUserType($user);

        $applicableRules = RewardRule::where('is_active', true)
            ->where(function ($q) use ($userType) {
                $q->where('user_type', $userType)
                    ->orWhere('user_type', 'all');
            })
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->get();

        foreach ($bill->items as $item) {
            $this->processItem($bill, $item, $user, $applicableRules);
        }
    }

    /**
     * Process a single bill item against applicable reward rules.
     */
    protected function processItem(PurchaseBill $bill, PurchaseBillItem $item, User $user, iterable $rules): void
    {
        foreach ($rules as $rule) {
            if (! $this->isItemQualified($item, $rule)) {
                continue;
            }

            $pointsEarned = $this->calculatePoints($item, $rule);
            $bonusAmount = $this->calculateBonus($pointsEarned, $rule);

            if ($pointsEarned <= 0) {
                continue;
            }

            RewardTransaction::create([
                'user_id' => $user->id,
                'purchase_bill_id' => $bill->id,
                'reward_rule_id' => $rule->id,
                'points_earned' => $pointsEarned,
                'bonus_amount' => $bonusAmount,
                'royalty_amount' => 0,
                'transaction_type' => 'earned',
                'status' => 'completed',
            ]);

            $this->updateWallet($user, $pointsEarned, $bonusAmount);
        }
    }

    /**
     * Check if a bill item qualifies for a reward rule.
     */
    protected function isItemQualified(PurchaseBillItem $item, RewardRule $rule): bool
    {
        if ($rule->product_id && $item->product_id !== $rule->product_id) {
            return false;
        }

        if ($rule->min_qty > 0 && $item->quantity < $rule->min_qty) {
            return false;
        }

        if ($rule->min_amount > 0 && $item->amount < $rule->min_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate points for a bill item based on the rule.
     */
    protected function calculatePoints(PurchaseBillItem $item, RewardRule $rule): float
    {
        return match ($rule->basis_type) {
            'qty' => $item->quantity * $rule->point_rate,
            'amount' => $item->amount * $rule->point_rate,
            'hybrid' => ($item->quantity + ($item->amount / 100)) * $rule->point_rate,
            default => 0,
        };
    }

    /**
     * Calculate bonus amount based on the rule.
     */
    protected function calculateBonus(float $points, RewardRule $rule): float
    {
        if (! $rule->bonus_value || $rule->bonus_value <= 0) {
            return 0;
        }

        return match ($rule->bonus_type) {
            'percentage' => $points * ($rule->bonus_value / 100),
            'fixed' => $rule->bonus_value,
            default => 0,
        };
    }

    /**
     * Update user's reward wallet.
     */
    protected function updateWallet(User $user, float $points, float $bonus): void
    {
        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        $wallet->increment('total_points', $points);
        $wallet->increment('total_bonus', $bonus);
    }

    /**
     * Determine user type for reward calculation.
     */
    protected function getUserType(User $user): string
    {
        if ($user->shareholderAccount()->exists()) {
            return 'shareholder';
        }

        if ($user->pharmacy()->exists()) {
            return 'pharmacy';
        }

        if ($user->doctor()->exists()) {
            return 'doctor';
        }

        return 'shareholder';
    }
}
