<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RewardWallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RewardWallet */
class RewardWalletResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_points' => $this->total_points,
            'total_bonus' => $this->total_bonus,
            'total_royalty' => $this->total_royalty,
            'total_value' => bcadd(bcadd($this->total_points ?? '0', $this->total_bonus ?? '0', 2), $this->total_royalty ?? '0', 2),
        ];
    }
}
