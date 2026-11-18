<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PrescriptionItem */
class PrescriptionItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medicine_name_raw' => $this->medicine_name_raw,
            'generic_name' => $this->generic_name,
            'dosage' => $this->dosage,
            'quantity' => $this->quantity,
            'confidence_score' => $this->confidence_score,
            'product' => new ProductResource($this->whenLoaded('product')),
            'illness_category' => new IllnessCategoryResource($this->whenLoaded('illnessCategory')),
        ];
    }
}
