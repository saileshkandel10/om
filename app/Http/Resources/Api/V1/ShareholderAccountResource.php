<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ShareholderAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShareholderAccount */
class ShareholderAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'boid' => $this->boid,
            'shares_owned' => $this->shares_owned,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'master' => $this->whenLoaded('master', fn () => [
                'name' => $this->master->name,
                'boid' => $this->master->boid,
                'share_count' => $this->master->share_count,
            ]),
        ];
    }
}
