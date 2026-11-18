<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareholderAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shareholder_master_id',
        'boid',
        'shares_owned',
        'verification_status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'shares_owned' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(ShareholderMaster::class, 'shareholder_master_id');
    }
}
