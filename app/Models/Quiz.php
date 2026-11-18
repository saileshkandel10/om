<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_category_id',
        'title',
        'slug',
        'description',
        'instructions',
        'time_limit_minutes',
        'passing_percentage',
        'reward_points',
        'max_attempts',
        'is_randomized',
        'is_active',
        'start_date',
        'end_date',
        'display_order',
    ];

    protected $casts = [
        'time_limit_minutes' => 'integer',
        'passing_percentage' => 'decimal:2',
        'reward_points' => 'decimal:2',
        'max_attempts' => 'integer',
        'is_randomized' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'display_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuizCategory::class, 'quiz_category_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('display_order', 'asc')->orderBy('id', 'asc');
    }

    public function activeQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->where('is_active', true)->orderBy('display_order', 'asc');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function userAttempts(int $userId): HasMany
    {
        return $this->hasMany(QuizAttempt::class)->where('user_id', $userId);
    }

    /**
     * Scope for currently active quizzes (within start and end date).
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            });
    }

    /**
     * Check if the quiz is currently active and within schedule.
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->start_date && $this->start_date->gt($today)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Total possible points for this quiz.
     */
    public function getTotalPointsAttribute(): int
    {
        return (int) $this->questions()->where('is_active', true)->sum('points');
    }

    /**
     * Total active questions count.
     */
    public function getQuestionsCountAttribute(): int
    {
        return (int) $this->questions()->where('is_active', true)->count();
    }
}
