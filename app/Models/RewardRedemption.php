<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'redemption_code',
        'redemption_type',
        'points_used',
        'amount_value',
        'status',
        'requested_at',
        'approved_at',
    ];

    public static function generateRedemptionCode(): string
    {
        do {
            $code = 'RDM-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while (static::where('redemption_code', $code)->exists());

        return $code;
    }

    protected function casts(): array
    {
        return [
            'points_used' => 'decimal:2',
            'amount_value' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
