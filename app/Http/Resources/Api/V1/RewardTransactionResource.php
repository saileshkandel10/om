<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RewardTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RewardTransaction */
class RewardTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'points_earned' => $this->points_earned,
            'bonus_amount' => $this->bonus_amount,
            'royalty_amount' => $this->royalty_amount,
            'transaction_type' => $this->transaction_type,
            'status' => $this->status,
            'purchase_bill_id' => $this->purchase_bill_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
