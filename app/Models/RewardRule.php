<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_type',
        'basis_type',
        'product_id',
        'illness_category_id',
        'min_qty',
        'min_amount',
        'point_rate',
        'bonus_type',
        'bonus_value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_qty' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'point_rate' => 'decimal:2',
            'bonus_value' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(RewardTransaction::class);
    }
}
