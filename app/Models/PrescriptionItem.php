<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'product_id',
        'medicine_name_raw',
        'generic_name',
        'dosage',
        'quantity',
        'illness_category_id',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class);
    }
}
