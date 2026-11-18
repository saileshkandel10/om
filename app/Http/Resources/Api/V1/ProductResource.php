<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'brand_name' => $this->brand_name,
            'generic_name' => $this->generic_name,
            'strength' => $this->strength,
            'dosage_form' => $this->dosage_form,
            'pack_size' => $this->pack_size,
            'mrp' => $this->mrp,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'launch_status' => $this->launch_status,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'illness_category' => new IllnessCategoryResource($this->whenLoaded('illnessCategory')),
            'batches' => ProductBatchResource::collection($this->whenLoaded('batches')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
