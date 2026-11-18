<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientHealthAnalytic extends Model
{
    use HasFactory;

    protected $table = 'patient_health_analytics';

    protected $fillable = [
        'patient_id',
        'health_category_id',
        'association_level',
        'supporting_medicines_count',
        'supporting_records_count',
        'first_detected_date',
        'latest_detected_date',
        'explanation',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'first_detected_date' => 'date',
            'latest_detected_date' => 'date',
            'supporting_medicines_count' => 'integer',
            'supporting_records_count' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function healthCategory(): BelongsTo
    {
        return $this->belongsTo(HealthCategory::class, 'health_category_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(HealthAnalyticEvidence::class, 'health_analytic_id');
    }

    public function clinicalReviews(): HasMany
    {
        return $this->hasMany(ClinicalReview::class, 'health_analytic_id');
    }
}
