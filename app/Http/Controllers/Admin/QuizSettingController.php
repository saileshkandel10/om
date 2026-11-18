<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateQuizSettingRequest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizSettingController extends Controller
{
    /**
     * Show the quiz settings configuration page.
     */
    public function index(): View
    {
        $settings = QuizSetting::getSettings();

        $stats = [
            'total_categories' => QuizCategory::count(),
            'total_questions' => QuizQuestion::count(),
            'total_quizzes' => Quiz::count(),
            'total_attempts' => QuizAttempt::count(),
        ];

        return view('admin.pharmacist-quiz.settings.index', compact('settings', 'stats'));
    }

    /**
     * Update global quiz settings.
     */
    public function update(UpdateQuizSettingRequest $request): RedirectResponse
    {
        $settings = QuizSetting::getSettings();
        $data = $request->validated();

        $data['is_randomized_questions'] = $request->boolean('is_randomized_questions', false);
        $data['is_randomized_options'] = $request->boolean('is_randomized_options', false);
        $data['show_explanations'] = $request->boolean('show_explanations', false);
        $data['allow_retry'] = $request->boolean('allow_retry', false);
        $data['is_active'] = $request->boolean('is_active', false);

        $settings->update($data);

        return redirect()->route('admin.pharmacist-quiz.settings.index')
            ->with('success', 'Pharmacist Quiz settings updated successfully.');
    }
}
