<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module_name',
        'file_type',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class, 'uploaded_file_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'uploaded_file_id');
    }

    public function ocrJobs(): HasMany
    {
        return $this->hasMany(OcrJob::class, 'source_file_id');
    }
}
