<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'province_id',
        'district_id',
        'location_status',
        'location_notes',
        'order_status',
        'payment_status',
        'uploaded_file_id',
        'invoice_number',
        'invoice_date',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'doctor_name',
        'doctor_nmc',
        'pharmacy_name',
        'pharmacy_location',
        'ocr_status',
        'validation_status',
        'confidence_score',
        'duplicate_flag',
        'fraud_flag',
    ];

    public function getPointsAwardedAttribute(): float
    {
        return (float) $this->rewardTransactions()->sum('points_earned');
    }

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'duplicate_flag' => 'boolean',
            'fraud_flag' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function uploadedFile(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'uploaded_file_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseBillItem::class);
    }

    public function rewardTransactions(): HasMany
    {
        return $this->hasMany(RewardTransaction::class);
    }

    public function patientIllnesses(): HasMany
    {
        return $this->hasMany(PatientIllness::class, 'source_id')->where('source_type', 'purchase_bill');
    }

    public function ocrJob(): HasOneThrough
    {
        return $this->hasOneThrough(
            OcrJob::class,
            UploadedFile::class,
            'id',
            'source_file_id',
            'uploaded_file_id',
            'id'
        )->latest();
    }
}
