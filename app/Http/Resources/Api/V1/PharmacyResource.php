<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pharmacy */
class PharmacyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_name' => $this->store_name,
            'license_no' => $this->license_no,
            'pan_vat_no' => $this->pan_vat_no,
            'owner_name' => $this->owner_name,
            'district' => $this->district,
            'address' => $this->address,
            'status' => $this->status,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
