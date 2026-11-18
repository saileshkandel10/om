<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductBatch */
class ProductBatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_no' => $this->batch_no,
            'manufacture_date' => $this->manufacture_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'quantity' => $this->quantity,
        ];
    }
}
