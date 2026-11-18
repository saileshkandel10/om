<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_bill_id',
        'reward_rule_id',
        'points_earned',
        'bonus_amount',
        'royalty_amount',
        'transaction_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'points_earned' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'royalty_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function rewardRule(): BelongsTo
    {
        return $this->belongsTo(RewardRule::class);
    }
}
