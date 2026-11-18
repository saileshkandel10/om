<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthAnalyticsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'patient_id',
        'action',
        'module',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
