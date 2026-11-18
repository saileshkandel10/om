<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineIndication extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'health_category_id',
        'evidence_level',
        'notes',
        'status',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function healthCategory(): BelongsTo
    {
        return $this->belongsTo(HealthCategory::class);
    }
}
