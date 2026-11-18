<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubmitQuizAttemptRequest;
use App\Http\Resources\Api\V1\QuizAttemptResource;
use App\Http\Resources\Api\V1\QuizCategoryResource;
use App\Http\Resources\Api\V1\QuizDetailResource;
use App\Http\Resources\Api\V1\QuizListResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizSetting;
use App\Services\QuizEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PharmacyQuizController extends Controller
{
    /**
     * Get dynamic global settings for the Pharmacist Quiz system.
     */
    public function settings(): JsonResponse
    {
        $settings = QuizSetting::getSettings();
        $isCurrentlyActive = $settings->isCurrentlyActive();

        return response()->json([
            'settings' => [
                'questions_per_quiz' => $settings->questions_per_quiz,
                'time_limit_minutes' => $settings->time_limit_minutes,
                'passing_percentage' => (float) $settings->passing_percentage,
                'is_randomized_questions' => (bool) $settings->is_randomized_questions,
                'is_randomized_options' => (bool) $settings->is_randomized_options,
                'show_explanations' => (bool) $settings->show_explanations,
                'allow_retry' => (bool) $settings->allow_retry,
                'max_attempts' => $settings->max_attempts,
                'reward_points' => (float) $settings->reward_points,
                'is_active' => (bool) $settings->is_active,
                'is_currently_active' => $isCurrentlyActive,
                'start_date' => $settings->start_date?->toDateString(),
                'end_date' => $settings->end_date?->toDateString(),
                'instructions' => $settings->instructions,
                'announcement' => $settings->announcement,
            ],
        ]);
    }

    /**
     * List all available active quizzes for pharmacists.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $settings = QuizSetting::getSettings();

        if (! $settings->isCurrentlyActive()) {
            return QuizListResource::collection(collect());
        }

        $query = Quiz::active()->with('category');

        if ($request->filled('category_id')) {
            $query->where('quiz_category_id', $request->category_id);
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
            ->paginate($request->integer('per_page', 15));

        return QuizListResource::collection($quizzes);
    }

    /**
     * List active quiz categories.
     */
    public function categories(): AnonymousResourceCollection
    {
        $categories = QuizCategory::where('is_active', true)
            ->withCount(['quizzes' => fn ($q) => $q->active()])
            ->orderBy('display_order', 'asc')
            ->get();

        return QuizCategoryResource::collection($categories);
    }

    /**
     * Get detailed information for a specific quiz.
     */
    public function show(Quiz $quiz): JsonResponse
    {
        $quiz->load('category');

        return response()->json([
            'quiz' => new QuizDetailResource($quiz),
        ]);
    }

    /**
     * Start a new quiz attempt with server-side random question selection.
     */
    public function start(Request $request, Quiz $quiz, QuizEngineService $engine): JsonResponse
    {
        $user = $request->user();
        $attempt = $engine->generateAttempt($user, $quiz);
        $attempt->load(['quiz']);

        return response()->json([
            'message' => 'Quiz started successfully! Good luck.',
            'attempt' => new QuizAttemptResource($attempt),
        ], 201);
    }

    /**
     * Submit answers for a quiz attempt and calculate score authoritatively.
     */
    public function submit(SubmitQuizAttemptRequest $request, QuizAttempt $attempt, QuizEngineService $engine): JsonResponse
    {
        $user = $request->user();

        // 1. Validate ownership
        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized access to this attempt.'], 403);
        }

        // 2. Prevent duplicate submission or modification of completed attempt
        if ($attempt->status === 'completed') {
            return response()->json([
                'message' => 'This quiz attempt has already been submitted and scored.',
                'attempt' => new QuizAttemptResource($attempt),
            ], 422);
        }

        // 3. Validate attempt expiry (if past time limit)
        if ($attempt->isExpired()) {
            $attempt->update(['status' => 'expired']);

            return response()->json([
                'message' => 'This quiz attempt has exceeded the time limit and has expired.',
            ], 422);
        }

        // 4. Server-side authoritative evaluation inside database transaction
        $result = $engine->evaluateAttempt($attempt, $request->validated('answers'));
        $attempt->load(['quiz', 'answers.question.options', 'answers.selectedOption']);

        $message = $result['is_passed']
            ? 'Congratulations! You passed the quiz with '.$result['percentage'].'% score.'.($result['reward_points_awarded'] > 0 ? ' Earned '.$result['reward_points_awarded'].' reward points!' : '')
            : 'Quiz completed. Your score is '.$result['percentage'].'%.';

        return response()->json([
            'message' => $message,
            'is_passed' => $result['is_passed'],
            'reward_points_awarded' => $result['reward_points_awarded'],
            'summary' => [
                'total_questions' => $result['total_questions'],
                'correct_answers' => $result['correct_answers'],
                'incorrect_answers' => $result['incorrect_answers'],
                'unanswered_questions' => $result['unanswered_questions'],
                'score' => $result['score'],
                'total_points' => $result['total_points'],
                'percentage' => $result['percentage'],
                'passing_percentage' => $result['passing_percentage'],
                'time_taken_seconds' => $result['time_taken_seconds'],
                'time_taken_formatted' => $result['time_taken_formatted'],
                'completed_at' => $result['completed_at'],
            ],
            'attempt' => new QuizAttemptResource($attempt),
        ]);
    }

    /**
     * Get attempt breakdown and answers with explanations.
     */
    public function attemptDetail(Request $request, QuizAttempt $attempt): JsonResponse
    {
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $attempt->load(['quiz', 'answers.question.options', 'answers.selectedOption']);

        return response()->json([
            'attempt' => new QuizAttemptResource($attempt),
        ]);
    }

    /**
     * Get the authenticated pharmacist's attempt history.
     */
    public function myAttempts(Request $request): AnonymousResourceCollection
    {
        $attempts = QuizAttempt::where('user_id', $request->user()->id)
            ->with('quiz.category')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return QuizAttemptResource::collection($attempts);
    }
}
