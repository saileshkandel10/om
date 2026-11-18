<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'name',
        'code',
        'status',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function pharmacies(): HasMany
    {
        return $this->hasMany(Pharmacy::class);
    }
}
