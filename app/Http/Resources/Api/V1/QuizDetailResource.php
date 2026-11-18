<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;
        $userAttempts = $userId ? $this->attempts()->where('user_id', $userId)->latest()->get() : collect();
        $completedAttempts = $userAttempts->where('status', 'completed');
        $isPassed = $completedAttempts->contains('is_passed', true);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'category' => new QuizCategoryResource($this->whenLoaded('category')),
            'time_limit_minutes' => $this->time_limit_minutes,
            'passing_percentage' => (float) $this->passing_percentage,
            'reward_points' => (float) $this->reward_points,
            'max_attempts' => $this->max_attempts,
            'questions_count' => $this->questions_count,
            'total_points' => $this->total_points,
            'is_randomized' => $this->is_randomized,
            'user_stats' => [
                'attempts_count' => $userAttempts->count(),
                'can_attempt' => $this->max_attempts === 0 || $userAttempts->count() < $this->max_attempts,
                'is_passed' => $isPassed,
                'best_score_percentage' => (float) ($completedAttempts->max('percentage') ?? 0.00),
                'past_attempts' => $userAttempts->map(fn ($att) => [
                    'id' => $att->id,
                    'attempt_number' => $att->attempt_number,
                    'score' => $att->score,
                    'total_points' => $att->total_points,
                    'percentage' => (float) $att->percentage,
                    'is_passed' => $att->is_passed,
                    'reward_points_awarded' => (float) $att->reward_points_awarded,
                    'status' => $att->status,
                    'completed_at' => $att->completed_at?->toIso8601String(),
                ]),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
