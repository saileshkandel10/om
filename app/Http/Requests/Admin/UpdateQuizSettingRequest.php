<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questions_per_quiz' => ['required', 'integer', 'min:1', 'max:100'],
            'time_limit_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'passing_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'is_randomized_questions' => ['nullable', 'boolean'],
            'is_randomized_options' => ['nullable', 'boolean'],
            'show_explanations' => ['nullable', 'boolean'],
            'allow_retry' => ['nullable', 'boolean'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:50'],
            'reward_points' => ['required', 'numeric', 'min:0', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'instructions' => ['nullable', 'string'],
            'announcement' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'questions_per_quiz.required' => 'Please enter the number of questions per quiz.',
            'time_limit_minutes.required' => 'Please specify the time limit in minutes.',
            'passing_percentage.required' => 'Please enter a valid passing percentage between 1% and 100%.',
            'max_attempts.required' => 'Please specify the maximum allowed attempts.',
            'reward_points.required' => 'Please enter the reward points awarded upon passing.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
