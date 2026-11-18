<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'points' => $this->points,
            'question_type' => $this->question_type,
            'display_order' => $this->display_order,
            'options' => QuizQuestionOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
