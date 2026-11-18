<?php

namespace App\Http\Requests\Api\V1\Reward;

use Illuminate\Foundation\Http\FormRequest;

class RedeemRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'points_used' => ['required', 'numeric', 'min:1'],
            'redemption_type' => ['required', 'string', 'in:cash,discount,gift'],
        ];
    }
}
