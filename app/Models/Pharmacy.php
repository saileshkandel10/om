<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'license_no',
        'pan_vat_no',
        'owner_name',
        'district',
        'address',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function sourcingRecords(): HasMany
    {
        return $this->hasMany(PharmacySourcingRecord::class);
    }
}
