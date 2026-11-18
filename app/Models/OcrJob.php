<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OcrJob extends Model
{
    use HasFactory;

    protected $table = 'ocr_jobs';

    protected $fillable = [
        'module_name',
        'source_file_id',
        'status',
        'engine_name',
        'raw_text',
        'parsed_json',
        'confidence_score',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'parsed_json' => 'array',
            'confidence_score' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function sourceFile(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'source_file_id');
    }

    public function reviewQueue(): HasMany
    {
        return $this->hasMany(OcrReviewQueue::class, 'ocr_job_id');
    }
}
