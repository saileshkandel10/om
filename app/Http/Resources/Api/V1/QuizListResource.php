<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;
        $userAttempts = $userId ? $this->attempts()->where('user_id', $userId)->get() : collect();
        $completedAttempts = $userAttempts->where('status', 'completed');
        $isPassed = $completedAttempts->contains('is_passed', true);
        $bestScore = $completedAttempts->max('percentage') ?? 0.00;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => new QuizCategoryResource($this->whenLoaded('category')),
            'time_limit_minutes' => $this->time_limit_minutes,
            'passing_percentage' => (float) $this->passing_percentage,
            'reward_points' => (float) $this->reward_points,
            'max_attempts' => $this->max_attempts,
            'questions_count' => $this->questions_count,
            'total_points' => $this->total_points,
            'is_active' => $this->is_active,
            'user_stats' => [
                'attempts_count' => $userAttempts->count(),
                'max_attempts' => $this->max_attempts,
                'can_attempt' => $this->max_attempts === 0 || $userAttempts->count() < $this->max_attempts,
                'is_passed' => $isPassed,
                'best_score_percentage' => (float) $bestScore,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
