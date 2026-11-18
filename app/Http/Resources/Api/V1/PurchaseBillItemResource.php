<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PurchaseBillItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchaseBillItem */
class PurchaseBillItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name_raw' => $this->product_name_raw,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'amount' => $this->amount,
            'batch_no' => $this->batch_no,
            'confidence_score' => $this->confidence_score,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
