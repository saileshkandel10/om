<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PurchaseBill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchaseBill */
class PurchaseBillResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pharmacy_name' => $this->pharmacy_name ?? $this->pharmacy?->store_name,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'subtotal_amount' => $this->subtotal_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'doctor_name' => $this->doctor_name,
            'doctor_nmc' => $this->doctor_nmc,
            'ocr_status' => $this->ocr_status,
            'validation_status' => $this->validation_status,
            'confidence_score' => $this->confidence_score,
            'duplicate_flag' => $this->duplicate_flag,
            'fraud_flag' => $this->fraud_flag,
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
            'items' => PurchaseBillItemResource::collection($this->whenLoaded('items')),
            'reward_transactions' => RewardTransactionResource::collection($this->whenLoaded('rewardTransactions')),
            'receipt_url' => $this->whenLoaded('uploadedFile', fn () => $this->uploadedFile?->file_path
                ? url('storage/'.$this->uploadedFile->file_path)
                : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
