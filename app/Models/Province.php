<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function activeDistricts(): HasMany
    {
        return $this->hasMany(District::class)->where('status', 'active');
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
