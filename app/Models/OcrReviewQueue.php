<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcrReviewQueue extends Model
{
    use HasFactory;

    protected $table = 'ocr_review_queues';

    protected $fillable = [
        'ocr_job_id',
        'assigned_to',
        'review_status',
        'review_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function ocrJob(): BelongsTo
    {
        return $this->belongsTo(OcrJob::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
