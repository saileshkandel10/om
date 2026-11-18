<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizQuestionRequest;
use App\Http\Requests\Admin\UpdateQuizQuestionRequest;
use App\Models\Product;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuizQuestionController extends Controller
{
    /**
     * Display question bank with filters, stats & pagination.
     */
    public function index(Request $request): View
    {
        $query = QuizQuestion::with(['category', 'quiz', 'options', 'product']);

        if ($request->filled('category_id')) {
            $query->where('quiz_category_id', $request->category_id);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                    ->orWhere('explanation', 'like', "%{$search}%")
                    ->orWhere('product_reference', 'like', "%{$search}%")
                    ->orWhereHas('options', fn ($oq) => $oq->where('option_text', 'like', "%{$search}%"));
            });
        }

        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');

        if (in_array($sortField, ['display_order', 'difficulty', 'created_at', 'points'], true)) {
            $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $questions = $query->paginate(15)->withQueryString();

        $categories = QuizCategory::orderBy('display_order')->get();
        $quizzes = Quiz::orderBy('title')->get();

        $stats = [
            'total' => QuizQuestion::count(),
            'active' => QuizQuestion::where('is_active', true)->count(),
            'easy' => QuizQuestion::where('difficulty', 'easy')->count(),
            'medium' => QuizQuestion::where('difficulty', 'medium')->count(),
            'hard' => QuizQuestion::where('difficulty', 'hard')->count(),
        ];

        return view('admin.pharmacist-quiz.questions.index', compact('questions', 'categories', 'quizzes', 'stats'));
    }

    /**
     * Show form to create new question.
     */
    public function create(Request $request): View
    {
        $categories = QuizCategory::where('is_active', true)->orderBy('display_order')->get();
        $quizzes = Quiz::where('is_active', true)->orderBy('title')->get();
        $products = Product::orderBy('brand_name')->take(200)->get(['id', 'brand_name', 'generic_name']);

        $preselectedCategory = $request->query('category_id');
        $preselectedQuiz = $request->query('quiz_id');

        return view('admin.pharmacist-quiz.questions.create', compact('categories', 'quizzes', 'products', 'preselectedCategory', 'preselectedQuiz'));
    }

    /**
     * Store a new question with 4 MCQ options in transaction.
     */
    public function store(StoreQuizQuestionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['points'] = ! empty($data['points']) ? (int) $data['points'] : 1;
        $data['display_order'] = isset($data['display_order']) ? (int) $data['display_order'] : 0;

        $options = $data['options'];
        $correctIndex = (int) $data['correct_option'];

        unset($data['options'], $data['correct_option']);

        DB::transaction(function () use ($data, $options, $correctIndex) {
            $question = QuizQuestion::create($data);

            foreach ($options as $index => $optionText) {
                QuizQuestionOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $index === $correctIndex,
                    'display_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('admin.pharmacist-quiz.questions.index')
            ->with('success', 'Question created successfully in Question Bank.');
    }

    /**
     * Display question details.
     */
    public function show(QuizQuestion $question): View
    {
        $question->load(['category', 'quiz', 'options', 'product']);

        return view('admin.pharmacist-quiz.questions.show', compact('question'));
    }

    /**
     * Show edit form.
     */
    public function edit(QuizQuestion $question): View
    {
        $question->load(['category', 'quiz', 'options', 'product']);
        $categories = QuizCategory::orderBy('display_order')->get();
        $quizzes = Quiz::orderBy('title')->get();
        $products = Product::orderBy('brand_name')->take(200)->get(['id', 'brand_name', 'generic_name']);

        return view('admin.pharmacist-quiz.questions.edit', compact('question', 'categories', 'quizzes', 'products'));
    }

    /**
     * Update question and its 4 options.
     */
    public function update(UpdateQuizQuestionRequest $request, QuizQuestion $question): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['points'] = ! empty($data['points']) ? (int) $data['points'] : 1;
        $data['display_order'] = isset($data['display_order']) ? (int) $data['display_order'] : 0;

        $options = $data['options'];
        $correctIndex = (int) $data['correct_option'];

        unset($data['options'], $data['correct_option']);

        DB::transaction(function () use ($question, $data, $options, $correctIndex) {
            $question->update($data);

            // Recreate options
            $question->options()->delete();

            foreach ($options as $index => $optionText) {
                QuizQuestionOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $index === $correctIndex,
                    'display_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('admin.pharmacist-quiz.questions.index')
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Soft delete question.
     */
    public function destroy(QuizQuestion $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.pharmacist-quiz.questions.index')
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Duplicate an existing question with all its options.
     */
    public function duplicate(QuizQuestion $question): RedirectResponse
    {
        DB::transaction(function () use ($question) {
            $newQuestion = $question->replicate();
            $newQuestion->question_text = '[Copy] '.$question->question_text;
            $newQuestion->display_order = $question->display_order + 1;
            $newQuestion->save();

            foreach ($question->options as $option) {
                $newOption = $option->replicate();
                $newOption->quiz_question_id = $newQuestion->id;
                $newOption->save();
            }
        });

        return redirect()->route('admin.pharmacist-quiz.questions.index')
            ->with('success', 'Question duplicated successfully.');
    }

    /**
     * Toggle question active status.
     */
    public function toggleStatus(QuizQuestion $question): RedirectResponse
    {
        $question->update([
            'is_active' => ! $question->is_active,
        ]);

        $status = $question->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Question #{$question->id} has been {$status}.");
    }
}
