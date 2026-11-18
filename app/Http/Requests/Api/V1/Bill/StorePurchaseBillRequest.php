<?php

namespace App\Http\Requests\Api\V1\Bill;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseBillRequest extends FormRequest
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
            'pharmacy_id' => ['nullable', 'exists:pharmacies,id'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'doctor_name' => ['nullable', 'string', 'max:255'],
            'doctor_nmc' => ['nullable', 'string', 'max:100'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receipt.required' => 'A receipt image is required.',
            'receipt.max' => 'The receipt file must not exceed 5MB.',
        ];
    }
}
