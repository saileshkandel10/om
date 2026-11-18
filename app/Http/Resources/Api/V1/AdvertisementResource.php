<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'image_url' => $this->image_url,
            'background_color' => $this->background_color ?: '#1E3A5F',
            'text_color' => $this->text_color ?: '#FFFFFF',
            'button_color' => $this->button_color ?: '#CD0B6F',
            'button_text_color' => $this->button_text_color ?: '#FFFFFF',
            'cta_text' => $this->cta_text,
            'cta_action' => $this->cta_action,
            'target_role' => $this->target_role,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
