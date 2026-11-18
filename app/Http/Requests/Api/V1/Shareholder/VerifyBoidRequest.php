<?php

namespace App\Http\Requests\Api\V1\Shareholder;

use Illuminate\Foundation\Http\FormRequest;

class VerifyBoidRequest extends FormRequest
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
            'boid' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'boid.size' => 'BOID must be exactly 16 digits.',
            'boid.regex' => 'BOID must contain only digits.',
        ];
    }
}
