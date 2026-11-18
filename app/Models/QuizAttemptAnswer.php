<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'question_text_snapshot',
        'explanation_snapshot',
        'options_snapshot',
        'correct_option_id',
        'selected_option_id',
        'is_correct',
        'points_possible',
        'points_earned',
        'display_order',
    ];

    protected $casts = [
        'options_snapshot' => 'array',
        'is_correct' => 'boolean',
        'points_possible' => 'integer',
        'points_earned' => 'integer',
        'display_order' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuizQuestionOption::class, 'selected_option_id');
    }

    public function correctOption(): BelongsTo
    {
        return $this->belongsTo(QuizQuestionOption::class, 'correct_option_id');
    }
}
