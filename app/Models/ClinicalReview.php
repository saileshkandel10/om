<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_analytic_id',
        'reviewer_id',
        'decision',
        'comments',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function analytic(): BelongsTo
    {
        return $this->belongsTo(PatientHealthAnalytic::class, 'health_analytic_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
