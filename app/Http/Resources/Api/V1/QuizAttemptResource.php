<?php

namespace App\Http\Resources\Api\V1;

use App\Models\QuizSetting;
use App\Services\QuizEngineService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isCompleted = $this->status === 'completed';
        $settings = QuizSetting::getSettings();
        $showExplanations = (bool) $settings->show_explanations;

        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'quiz_title' => $this->quiz?->title,
            'category_name' => $this->quiz?->category?->name ?? 'General',
            'category_color' => $this->quiz?->category?->color ?? '#CD0B6F',
            'attempt_number' => $this->attempt_number,
            'score' => $this->score,
            'correct_answers_count' => $this->correct_answers_count,
            'incorrect_answers_count' => $this->incorrect_answers_count,
            'unanswered_count' => $this->unanswered_count,
            'total_points' => $this->total_points,
            'percentage' => (float) $this->percentage,
            'is_passed' => $this->is_passed,
            'reward_points_awarded' => (float) $this->reward_points_awarded,
            'time_taken_seconds' => $this->time_taken_seconds,
            'time_taken_formatted' => $this->time_taken_formatted,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'time_limit_minutes' => $this->quiz?->time_limit_minutes ?: $settings->time_limit_minutes,
            // When in progress, return sanitized locked questions for this attempt
            'questions' => $this->when(! $isCompleted, function () {
                return app(QuizEngineService::class)->getSanitizedQuestions($this->resource);
            }),
            // When completed, return answers breakdown with explanations (if enabled by admin)
            'answers' => $this->when($isCompleted, function () use ($showExplanations) {
                return $this->answers()->orderBy('display_order', 'asc')->with(['question.options', 'selectedOption'])->get()->map(function ($ans) use ($showExplanations) {
                    $correctOption = $ans->question?->options->firstWhere('is_correct', true);

                    return [
                        'question_id' => $ans->quiz_question_id,
                        'question_text' => $ans->question_text_snapshot ?: $ans->question?->question_text,
                        'explanation' => $showExplanations ? ($ans->explanation_snapshot ?: $ans->question?->explanation) : null,
                        'selected_option_id' => $ans->selected_option_id,
                        'selected_option_text' => $ans->selectedOption?->option_text,
                        'correct_option_id' => $showExplanations ? ($ans->correct_option_id ?: $correctOption?->id) : null,
                        'correct_option_text' => $showExplanations ? $correctOption?->option_text : null,
                        'is_correct' => $ans->is_correct,
                        'points_earned' => $ans->points_earned,
                        'possible_points' => $ans->points_possible,
                    ];
                });
            }),
        ];
    }
}
