<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'questions_per_quiz',
        'time_limit_minutes',
        'passing_percentage',
        'is_randomized_questions',
        'is_randomized_options',
        'show_explanations',
        'allow_retry',
        'max_attempts',
        'reward_points',
        'is_active',
        'start_date',
        'end_date',
        'instructions',
        'announcement',
    ];

    protected $casts = [
        'questions_per_quiz' => 'integer',
        'time_limit_minutes' => 'integer',
        'passing_percentage' => 'decimal:2',
        'is_randomized_questions' => 'boolean',
        'is_randomized_options' => 'boolean',
        'show_explanations' => 'boolean',
        'allow_retry' => 'boolean',
        'max_attempts' => 'integer',
        'reward_points' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the singleton quiz settings record or create default.
     */
    public static function getSettings(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'questions_per_quiz' => 10,
                'time_limit_minutes' => 10,
                'passing_percentage' => 70.00,
                'is_randomized_questions' => true,
                'is_randomized_options' => true,
                'show_explanations' => true,
                'allow_retry' => true,
                'max_attempts' => 3,
                'reward_points' => 50.00,
                'is_active' => true,
                'instructions' => 'Complete all multiple-choice questions within the allocated time. Passing score credits reward points directly to your pharmacy wallet.',
            ]
        );
    }

    /**
     * Check if the quiz system is currently active and within schedule.
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
}
