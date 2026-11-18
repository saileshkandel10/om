<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'generic_name',
        'active_ingredients',
        'strength',
        'dosage_form',
        'therapeutic_class',
        'manufacturer',
        'product_id',
        'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function indications(): HasMany
    {
        return $this->hasMany(MedicineIndication::class);
    }
}
