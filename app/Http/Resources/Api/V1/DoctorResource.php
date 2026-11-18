<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Doctor */
class DoctorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_no' => $this->registration_no,
            'specialty' => $this->specialty,
            'hospital_clinic' => $this->hospital_clinic,
            'status' => $this->status,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
