<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'quiz_category_id' => ['required', 'integer', 'exists:quiz_categories,id'],
            'quiz_id' => ['nullable', 'integer', 'exists:quizzes,id'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'options' => ['required', 'array', 'size:4'],
            'options.*' => ['required', 'string'],
            'correct_option' => ['required', 'in:0,1,2,3'],
            'explanation' => ['nullable', 'string'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_reference' => ['nullable', 'string', 'max:255'],
            'points' => ['nullable', 'integer', 'min:1'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'The question text is required.',
            'quiz_category_id.required' => 'Please select a quiz category.',
            'difficulty.required' => 'Please select a difficulty level (Easy, Medium, or Hard).',
            'options.required' => 'All four options (A, B, C, D) are required.',
            'options.*.required' => 'Each option text must not be empty.',
            'correct_option.required' => 'Please specify which option is the correct answer.',
        ];
    }
}
