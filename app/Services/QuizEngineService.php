<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizSetting;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class QuizEngineService
{
    /**
     * Start or resume a secure, randomized quiz attempt with locked question snapshots.
     *
     * @throws ValidationException
     */
    public function generateAttempt(User $user, Quiz $quiz, ?QuizSetting $settings = null): QuizAttempt
    {
        $settings = $settings ?: QuizSetting::getSettings();

        // 1. Validate master system schedule & activation
        if (! $settings->isCurrentlyActive()) {
            throw ValidationException::withMessages([
                'quiz' => 'The Pharmacist Quiz system is currently unavailable or off schedule.',
            ]);
        }

        // 2. Validate quiz-specific schedule & activation
        if (! $quiz->isCurrentlyActive()) {
            throw ValidationException::withMessages([
                'quiz' => 'This quiz is currently not active or has expired.',
            ]);
        }

        // 3. Check for existing in-progress attempt to resume
        $previousAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->get();

        $activeAttempt = $previousAttempts->firstWhere('status', 'in_progress');
        if ($activeAttempt) {
            if (! $activeAttempt->isExpired()) {
                return $activeAttempt;
            }
            // Mark expired attempt
            $activeAttempt->update(['status' => 'expired']);
        }

        $completedCount = $previousAttempts->where('status', 'completed')->count();

        // 4. Validate retry permissions
        if (! $settings->allow_retry && $completedCount >= 1) {
            throw ValidationException::withMessages([
                'quiz' => 'Retrying quizzes is currently disabled by administrator.',
            ]);
        }

        // 5. Validate maximum attempts allowed
        $maxAttempts = $quiz->max_attempts > 0 ? $quiz->max_attempts : $settings->max_attempts;
        if ($maxAttempts > 0 && $completedCount >= $maxAttempts) {
            throw ValidationException::withMessages([
                'quiz' => 'You have reached the maximum allowed attempts ('.$maxAttempts.') for this quiz.',
            ]);
        }

        // 6. Query eligible active questions from active categories
        $activeCategoryIds = QuizCategory::where('is_active', true)->pluck('id')->toArray();

        $questionsQuery = QuizQuestion::where('is_active', true)
            ->where(function ($q) use ($quiz, $activeCategoryIds) {
                // If question is directly linked to this quiz OR belongs to category
                $q->where('quiz_id', $quiz->id)
                    ->orWhere(function ($subQ) use ($quiz, $activeCategoryIds) {
                        if ($quiz->quiz_category_id) {
                            $subQ->where('quiz_category_id', $quiz->quiz_category_id);
                        } else {
                            $subQ->whereIn('quiz_category_id', $activeCategoryIds);
                        }
                    });
            })
            ->whereHas('options', fn ($oq) => $oq->where('is_correct', true))
            ->with(['options']);

        // 7. Select & Randomize eligible questions
        $targetCount = $settings->questions_per_quiz > 0 ? $settings->questions_per_quiz : 10;
        $shouldRandomizeQuestions = $settings->is_randomized_questions || $quiz->is_randomized;

        if ($shouldRandomizeQuestions) {
            $eligibleQuestions = $questionsQuery->get();
            $selectedQuestions = $eligibleQuestions->shuffle()->take($targetCount)->values();
        } else {
            $selectedQuestions = $questionsQuery->orderBy('display_order', 'asc')
                ->orderBy('id', 'asc')
                ->take($targetCount)
                ->get();
        }

        if ($selectedQuestions->isEmpty()) {
            throw ValidationException::withMessages([
                'quiz' => 'No eligible questions found in the question bank for this assessment.',
            ]);
        }

        // 8. Prevent duplicate questions and calculate total points
        $uniqueQuestions = $selectedQuestions->unique('id')->values();
        $totalPossiblePoints = $uniqueQuestions->sum('points') ?: $uniqueQuestions->count();
        $attemptNumber = $previousAttempts->count() + 1;

        // 9. Atomic Transaction to lock attempt and question/option snapshots
        return DB::transaction(function () use ($user, $quiz, $uniqueQuestions, $totalPossiblePoints, $attemptNumber, $settings) {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'attempt_number' => $attemptNumber,
                'score' => 0,
                'correct_answers_count' => 0,
                'incorrect_answers_count' => 0,
                'unanswered_count' => $uniqueQuestions->count(),
                'total_points' => $totalPossiblePoints,
                'percentage' => 0.00,
                'is_passed' => false,
                'reward_points_awarded' => 0.00,
                'time_taken_seconds' => 0,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $shouldRandomizeOptions = (bool) $settings->is_randomized_options;

            foreach ($uniqueQuestions as $index => $question) {
                $optionsCollection = $question->options;
                if ($shouldRandomizeOptions) {
                    $optionsCollection = $optionsCollection->shuffle();
                }

                $correctOption = $optionsCollection->firstWhere('is_correct', true);

                // Safe sanitized options snapshot (without is_correct)
                $safeOptionsSnapshot = $optionsCollection->map(fn ($opt) => [
                    'id' => $opt->id,
                    'option_text' => $opt->option_text,
                ])->values()->toArray();

                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'question_text_snapshot' => $question->question_text,
                    'explanation_snapshot' => $question->explanation,
                    'options_snapshot' => $safeOptionsSnapshot,
                    'correct_option_id' => $correctOption?->id,
                    'selected_option_id' => null,
                    'is_correct' => false,
                    'points_possible' => $question->points ?: 1,
                    'points_earned' => 0,
                    'display_order' => $index + 1,
                ]);
            }

            Log::info('QuizEngine: Generated attempt with locked question snapshot', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'attempt_id' => $attempt->id,
                'questions_count' => $uniqueQuestions->count(),
            ]);

            return $attempt;
        });
    }

    /**
     * Submit and calculate authoritative score for a pharmacist's quiz attempt against the locked snapshot.
     */
    public function evaluateAttempt(QuizAttempt $attempt, array $submittedAnswersList, ?QuizSetting $settings = null): array
    {
        $settings = $settings ?: QuizSetting::getSettings();
        $quiz = $attempt->quiz;
        $user = $attempt->user;

        return DB::transaction(function () use ($attempt, $quiz, $user, $submittedAnswersList, $settings) {
            $attemptAnswers = $attempt->answers()->orderBy('display_order', 'asc')->get();
            $submittedMap = collect($submittedAnswersList)->keyBy('question_id');

            $earnedScore = 0;
            $totalPointsPossible = 0;
            $correctCount = 0;
            $incorrectCount = 0;
            $unansweredCount = 0;

            foreach ($attemptAnswers as $ans) {
                $subAnswer = $submittedMap->get($ans->quiz_question_id);
                $selectedOptionId = $subAnswer ? ($subAnswer['selected_option_id'] ?? null) : null;

                $isCorrect = false;
                $pointsEarned = 0;

                if ($selectedOptionId === null) {
                    $unansweredCount++;
                } else {
                    $isCorrect = $ans->correct_option_id && (int) $selectedOptionId === (int) $ans->correct_option_id;
                    if ($isCorrect) {
                        $correctCount++;
                        $pointsEarned = $ans->points_possible;
                    } else {
                        $incorrectCount++;
                    }
                }

                $earnedScore += $pointsEarned;
                $totalPointsPossible += $ans->points_possible;

                $ans->update([
                    'selected_option_id' => $selectedOptionId,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);
            }

            $percentage = $totalPointsPossible > 0
                ? round(($earnedScore / $totalPointsPossible) * 100, 2)
                : 0.00;

            $passMark = $quiz->passing_percentage ?: $settings->passing_percentage;
            $isPassed = $percentage >= (float) $passMark;
            $rewardPointsToAward = 0.00;

            // Check if user already received reward points for this quiz previously
            $alreadyRewarded = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->where('id', '!=', $attempt->id)
                ->where('is_passed', true)
                ->where('reward_points_awarded', '>', 0)
                ->exists();

            $rewardConfig = $quiz->reward_points ?: $settings->reward_points;
            if ($isPassed && ! $alreadyRewarded && $rewardConfig > 0) {
                $rewardPointsToAward = (float) $rewardConfig;

                RewardTransaction::create([
                    'user_id' => $user->id,
                    'points_earned' => $rewardPointsToAward,
                    'bonus_amount' => 0,
                    'royalty_amount' => 0,
                    'transaction_type' => 'quiz_reward',
                    'status' => 'completed',
                ]);

                $wallet = RewardWallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0],
                );
                $wallet->increment('total_points', $rewardPointsToAward);
            }

            $now = now();
            $timeTakenSeconds = $attempt->started_at ? $attempt->started_at->diffInSeconds($now) : 0;

            $attempt->update([
                'score' => $earnedScore,
                'correct_answers_count' => $correctCount,
                'incorrect_answers_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'total_points' => $totalPointsPossible,
                'percentage' => $percentage,
                'is_passed' => $isPassed,
                'reward_points_awarded' => $rewardPointsToAward,
                'time_taken_seconds' => $timeTakenSeconds,
                'status' => 'completed',
                'completed_at' => $now,
            ]);

            return [
                'attempt' => $attempt,
                'is_passed' => $isPassed,
                'reward_points_awarded' => $rewardPointsToAward,
                'percentage' => $percentage,
                'score' => $earnedScore,
                'total_points' => $totalPointsPossible,
                'correct_answers' => $correctCount,
                'incorrect_answers' => $incorrectCount,
                'unanswered_questions' => $unansweredCount,
                'total_questions' => $attemptAnswers->count(),
                'passing_percentage' => (float) $passMark,
                'time_taken_seconds' => $timeTakenSeconds,
                'time_taken_formatted' => $attempt->time_taken_formatted,
                'completed_at' => $now->toIso8601String(),
            ];
        });
    }

    /**
     * Get 100% sanitized questions payload for active quiz takers (zero correct answer leak).
     */
    public function getSanitizedQuestions(QuizAttempt $attempt): array
    {
        $answers = $attempt->answers()->orderBy('display_order', 'asc')->get();

        return $answers->map(function ($ans) {
            $options = is_array($ans->options_snapshot) ? $ans->options_snapshot : [];

            // Strip any internal or correct answer keys
            $safeOptions = array_map(function ($opt) {
                return [
                    'id' => (int) $opt['id'],
                    'option_text' => (string) $opt['option_text'],
                ];
            }, $options);

            return [
                'id' => $ans->quiz_question_id,
                'question_text' => $ans->question_text_snapshot ?: $ans->question?->question_text,
                'points' => $ans->points_possible,
                'display_order' => $ans->display_order,
                'options' => $safeOptions,
            ];
        })->toArray();
    }
}
