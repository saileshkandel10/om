<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PharmacySourcingRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PharmacySourcingRecord */
class PharmacySourcingRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_name' => $this->supplier_name,
            'source_invoice_no' => $this->source_invoice_no,
            'source_date' => $this->source_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
