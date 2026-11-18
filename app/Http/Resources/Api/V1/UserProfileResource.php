<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? (str_starts_with($this->avatar, 'http') ? $this->avatar : asset('storage/'.$this->avatar)) : null,
            'mobile' => $this->mobile,
            'status' => $this->status,
            'uploaded_bills_count' => $this->purchase_bills_count ?? $this->purchaseBills()->count(),
            'bills_count' => $this->purchase_bills_count ?? $this->purchaseBills()->count(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'shareholder_account' => new ShareholderAccountResource($this->whenLoaded('shareholderAccount')),
            'pharmacy' => new PharmacyResource($this->whenLoaded('pharmacy')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'reward_wallet' => new RewardWalletResource($this->whenLoaded('rewardWallet')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
