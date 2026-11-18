<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Campaign */
class CampaignResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'campaign_type' => $this->campaign_type,
            'target_segment' => $this->target_segment,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
        ];
    }
}
