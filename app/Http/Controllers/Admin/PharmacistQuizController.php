<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacistQuizController extends Controller
{
    /**
     * Pharmacist Quiz Overview and Directory.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'quizzes');

        $query = Quiz::with('category')->withCount(['questions', 'attempts']);

        if ($request->filled('category_id')) {
            $query->where('quiz_category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $quizzes = $query->orderBy('display_order', 'asc')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $categories = QuizCategory::withCount('quizzes')->orderBy('display_order')->get();
        $recentAttempts = QuizAttempt::with(['user', 'quiz.category'])->latest()->take(20)->get();

        $stats = [
            'total_quizzes' => Quiz::count(),
            'active_quizzes' => Quiz::active()->count(),
            'total_questions' => QuizQuestion::count(),
            'active_questions' => QuizQuestion::where('is_active', true)->count(),
            'total_pharmacists' => QuizAttempt::distinct('user_id')->count('user_id'),
            'total_attempts' => QuizAttempt::count(),
            'completed_attempts' => QuizAttempt::where('status', 'completed')->count(),
            'passed_attempts' => QuizAttempt::where('is_passed', true)->count(),
            'failed_attempts' => QuizAttempt::where('status', 'completed')->where('is_passed', false)->count(),
            'average_score' => QuizAttempt::where('status', 'completed')->count() > 0
                ? round((float) QuizAttempt::where('status', 'completed')->avg('percentage'), 1)
                : 0.0,
            'pass_rate' => QuizAttempt::count() > 0
                ? round((QuizAttempt::where('is_passed', true)->count() / QuizAttempt::count()) * 100, 1)
                : 0.0,
        ];

        return view('admin.pharmacist-quiz.index', compact('quizzes', 'categories', 'recentAttempts', 'stats', 'tab'));
    }

    /**
     * Complete Pharmacist Quiz Results & Analytics Dashboard.
     */
    public function results(Request $request): View
    {
        $query = QuizAttempt::with(['user', 'quiz.category']);

        // 1. Filter by Pharmacist / User ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 2. Filter by Quiz
        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // 3. Filter by Category
        if ($request->filled('category_id')) {
            $catId = $request->category_id;
            $query->whereHas('quiz', fn ($q) => $q->where('quiz_category_id', $catId));
        }

        // 4. Filter by Result / Pass Status
        if ($request->filled('result')) {
            if ($request->result === 'passed') {
                $query->where('is_passed', true);
            } elseif ($request->result === 'failed') {
                $query->where('status', 'completed')->where('is_passed', false);
            } elseif ($request->result === 'in_progress') {
                $query->where('status', 'in_progress');
            } elseif ($request->result === 'expired') {
                $query->where('status', 'expired');
            }
        }

        // 5. Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        // 6. Filter by Score Range
        if ($request->filled('min_score')) {
            $query->where('percentage', '>=', (float) $request->min_score);
        }
        if ($request->filled('max_score')) {
            $query->where('percentage', '<=', (float) $request->max_score);
        }

        // 7. Search keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                })->orWhereHas('quiz', function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%");
                });
            });
        }

        // 8. Sorting
        $sort = $request->input('sort', 'recent');
        match ($sort) {
            'highest_score' => $query->orderByDesc('percentage')->orderByDesc('id'),
            'lowest_score' => $query->orderBy('percentage', 'asc')->orderByDesc('id'),
            'oldest' => $query->orderBy('id', 'asc'),
            'longest_time' => $query->orderByDesc('time_taken_seconds'),
            default => $query->orderByDesc('id'),
        };

        $attempts = $query->paginate(15)->withQueryString();

        $categories = QuizCategory::orderBy('display_order')->get();
        $quizzes = Quiz::orderBy('title')->get();
        $pharmacists = User::whereHas('roles', fn ($r) => $r->where('name', 'pharmacy'))->orderBy('name')->take(100)->get(['id', 'name', 'email']);

        $completedQuery = QuizAttempt::where('status', 'completed');
        $stats = [
            'total_questions' => QuizQuestion::count(),
            'active_questions' => QuizQuestion::where('is_active', true)->count(),
            'total_pharmacists' => QuizAttempt::distinct('user_id')->count('user_id'),
            'total_attempts' => QuizAttempt::count(),
            'completed_attempts' => (clone $completedQuery)->count(),
            'passed_attempts' => QuizAttempt::where('is_passed', true)->count(),
            'failed_attempts' => (clone $completedQuery)->where('is_passed', false)->count(),
            'average_score' => (clone $completedQuery)->count() > 0
                ? round((float) (clone $completedQuery)->avg('percentage'), 1)
                : 0.0,
        ];

        return view('admin.pharmacist-quiz.results.index', compact('attempts', 'categories', 'quizzes', 'pharmacists', 'stats'));
    }

    /**
     * Show detailed audit review of a specific pharmacist attempt.
     */
    public function showAttempt(QuizAttempt $attempt): View
    {
        $attempt->load([
            'user',
            'quiz.category',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        return view('admin.pharmacist-quiz.results.show', compact('attempt'));
    }
}
