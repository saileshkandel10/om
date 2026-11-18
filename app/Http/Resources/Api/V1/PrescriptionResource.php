<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Prescription */
class PrescriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_ref_code' => $this->patient_ref_code,
            'prescription_date' => $this->prescription_date?->toDateString(),
            'source_type' => $this->source_type,
            'parsed_status' => $this->parsed_status,
            'confidence_score' => $this->confidence_score,
            'notes' => $this->notes,
            'items' => PrescriptionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
