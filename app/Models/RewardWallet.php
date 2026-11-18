<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'total_bonus',
        'total_royalty',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'decimal:2',
            'total_bonus' => 'decimal:2',
            'total_royalty' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
