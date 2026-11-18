<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Note: Never expose is_correct in active quiz taker mode.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_text' => $this->option_text,
            'display_order' => $this->display_order,
            // is_correct is strictly omitted during active quiz taking for security
        ];
    }
}
