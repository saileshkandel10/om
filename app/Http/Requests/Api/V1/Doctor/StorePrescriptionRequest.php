<?php

namespace App\Http\Requests\Api\V1\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
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
            'patient_ref_code' => ['nullable', 'string', 'max:100'],
            'prescription_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'source_type' => ['required', 'string', 'in:manual,upload'],
            'prescription_image' => ['required_if:source_type,upload', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'items' => ['required_if:source_type,manual', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.medicine_name_raw' => ['required', 'string', 'max:255'],
            'items.*.generic_name' => ['nullable', 'string', 'max:255'],
            'items.*.dosage' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.illness_category_id' => ['nullable', 'exists:illness_categories,id'],
        ];
    }
}
