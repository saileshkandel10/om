<?php

namespace App\Http\Requests\Api\V1\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePharmacyRequest extends FormRequest
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
            'store_name' => ['sometimes', 'string', 'max:255'],
            'license_no' => ['sometimes', 'string', 'max:100'],
            'pan_vat_no' => ['sometimes', 'nullable', 'string', 'max:50'],
            'owner_name' => ['sometimes', 'string', 'max:255'],
            'district' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
