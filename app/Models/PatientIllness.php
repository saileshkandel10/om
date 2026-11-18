<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientIllness extends Model
{
    use HasFactory;

    protected $table = 'patient_illnesses';

    protected $fillable = [
        'user_id',
        'illness_category_id',
        'source_type',
        'source_id',
        'product_id',
        'detected_at',
        'notes',
        'diagnosed_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(IllnessCategory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class, 'source_id');
    }
}
