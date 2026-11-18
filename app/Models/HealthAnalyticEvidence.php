<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthAnalyticEvidence extends Model
{
    use HasFactory;

    protected $table = 'health_analytics_evidence';

    protected $fillable = [
        'health_analytic_id',
        'source_type',
        'source_id',
        'medicine_name',
        'generic_name',
        'detected_date',
        'source_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'detected_date' => 'date',
        ];
    }

    public function analytic(): BelongsTo
    {
        return $this->belongsTo(PatientHealthAnalytic::class, 'health_analytic_id');
    }
}
