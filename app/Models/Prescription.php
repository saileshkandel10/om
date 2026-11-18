<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_ref_code',
        'prescription_date',
        'source_type',
        'uploaded_file_id',
        'parsed_status',
        'confidence_score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'prescription_date' => 'date',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function uploadedFile(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'uploaded_file_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
