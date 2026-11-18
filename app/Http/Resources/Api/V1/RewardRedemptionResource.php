<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RewardRedemption */
class RewardRedemptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'redemption_code' => $this->redemption_code,
            'lucky_draw_code' => $this->redemption_code,
            'redemption_type' => $this->redemption_type,
            'points_used' => $this->points_used,
            'amount_value' => $this->amount_value,
            'status' => $this->status,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
