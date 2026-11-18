<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'quiz_id',
        'attempt_number',
        'score',
        'correct_answers_count',
        'incorrect_answers_count',
        'unanswered_count',
        'total_points',
        'percentage',
        'is_passed',
        'reward_points_awarded',
        'time_taken_seconds',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'score' => 'integer',
        'correct_answers_count' => 'integer',
        'incorrect_answers_count' => 'integer',
        'unanswered_count' => 'integer',
        'total_points' => 'integer',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'reward_points_awarded' => 'decimal:2',
        'time_taken_seconds' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attempt) {
            if (empty($attempt->uuid)) {
                $attempt->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    /**
     * Check if the attempt has exceeded the quiz time limit.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        $timeLimit = $this->quiz?->time_limit_minutes;
        if (! $timeLimit || $timeLimit <= 0) {
            return false;
        }

        $expiryTime = $this->started_at->copy()->addMinutes($timeLimit)->addSeconds(30); // 30s grace period

        return now()->gt($expiryTime);
    }

    /**
     * Formatted string of time taken (e.g. "3m 45s" or "45s").
     */
    public function getTimeTakenFormattedAttribute(): string
    {
        $seconds = (int) $this->time_taken_seconds;
        if ($seconds <= 0 && $this->started_at && $this->completed_at) {
            $seconds = $this->started_at->diffInSeconds($this->completed_at);
        }

        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        if ($m > 0) {
            return "{$m}m {$s}s";
        }

        return "{$s}s";
    }

    /**
     * Check if a specific user owns or is authorized to access this attempt.
     */
    public function isAccessibleBy(User $user): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('staff')) {
            return true;
        }

        return (int) $this->user_id === (int) $user->id;
    }
}
