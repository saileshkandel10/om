<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'brand_name',
        'generic_name',
        'strength',
        'dosage_form',
        'pack_size',
        'product_category_id',
        'illness_category_id',
        'mrp',
        'image',
        'launch_status',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'mrp' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function purchaseBillItems(): HasMany
    {
        return $this->hasMany(PurchaseBillItem::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function rewardRules(): HasMany
    {
        return $this->hasMany(RewardRule::class);
    }

    public function patientIllnesses(): HasMany
    {
        return $this->hasMany(PatientIllness::class);
    }
}
