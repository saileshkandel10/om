<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPharmacistForHistory(string $name = 'Pharmacist'): array
{
    $user = User::factory()->create(['name' => $name, 'password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function createHistoryQuizWithAttempts(User $user, int $attemptsCount = 2): Quiz
{
    $category = QuizCategory::create([
        'name' => 'Medicine Knowledge',
        'slug' => 'medicine-knowledge',
        'color' => '#CD0B6F',
        'is_active' => true,
    ]);

    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Clinical Dosage & Safety Quiz',
        'slug' => 'clinical-dosage-safety-quiz',
        'passing_percentage' => 70.00,
        'reward_points' => 50.00,
        'is_active' => true,
    ]);

    $q1 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question_text' => 'What is the adult dosage of Paracetamol for mild fever?',
        'explanation' => 'Paracetamol dosage is 500mg-1000mg q4-6h.',
        'points' => 1,
        'is_active' => true,
    ]);

    $opt1 = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '500mg - 1000mg', 'is_correct' => true, 'display_order' => 1]);
    $opt2 = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '5000mg', 'is_correct' => false, 'display_order' => 2]);

    for ($i = 1; $i <= $attemptsCount; $i++) {
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'attempt_number' => $i,
            'score' => $i === 1 ? 0 : 1,
            'correct_answers_count' => $i === 1 ? 0 : 1,
            'incorrect_answers_count' => $i === 1 ? 1 : 0,
            'unanswered_count' => 0,
            'total_points' => 1,
            'percentage' => $i === 1 ? 0.00 : 100.00,
            'is_passed' => $i !== 1,
            'reward_points_awarded' => $i === 1 ? 0.00 : 50.00,
            'time_taken_seconds' => 125,
            'status' => 'completed',
            'started_at' => now()->subHours($attemptsCount - $i + 1),
            'completed_at' => now()->subHours($attemptsCount - $i + 1)->addMinutes(2),
        ]);

        QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $q1->id,
            'question_text_snapshot' => $q1->question_text,
            'explanation_snapshot' => $q1->explanation,
            'options_snapshot' => [
                ['id' => $opt1->id, 'option_text' => $opt1->option_text],
                ['id' => $opt2->id, 'option_text' => $opt2->option_text],
            ],
            'correct_option_id' => $opt1->id,
            'selected_option_id' => $i === 1 ? $opt2->id : $opt1->id,
            'is_correct' => $i !== 1,
            'points_possible' => 1,
            'points_earned' => $i === 1 ? 0 : 1,
            'display_order' => 1,
        ]);
    }

    return $quiz;
}

test('pharmacist can fetch their own quiz attempt history with pagination', function () {
    [$user, $token] = createPharmacistForHistory();
    createHistoryQuizWithAttempts($user, 3); // 3 attempts

    $response = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes/my-attempts?per_page=2');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.per_page', 2);
});

test('pharmacist history displays quiz title, category, score, percentage, pass/fail status, and attempt number', function () {
    [$user, $token] = createPharmacistForHistory();
    createHistoryQuizWithAttempts($user, 2);

    $response = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes/my-attempts');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.quiz_title', 'Clinical Dosage & Safety Quiz')
        ->assertJsonPath('data.0.category_name', 'Medicine Knowledge')
        ->assertJsonPath('data.0.score', 1)
        ->assertJsonPath('data.0.percentage', 100)
        ->assertJsonPath('data.0.is_passed', true)
        ->assertJsonPath('data.0.reward_points_awarded', 50)
        ->assertJsonPath('data.0.attempt_number', 2);
});

test('pharmacist can fetch details of their own past attempt with locked answers review', function () {
    [$user, $token] = createPharmacistForHistory();
    createHistoryQuizWithAttempts($user, 1);

    $attempt = QuizAttempt::where('user_id', $user->id)->first();

    $response = $this->withToken($token)
        ->getJson("/api/v1/pharmacy/quizzes/attempts/{$attempt->id}");

    $response->assertOk()
        ->assertJsonPath('attempt.id', $attempt->id)
        ->assertJsonPath('attempt.score', 0)
        ->assertJsonPath('attempt.is_passed', false)
        ->assertJsonPath('attempt.answers.0.question_text', 'What is the adult dosage of Paracetamol for mild fever?')
        ->assertJsonPath('attempt.answers.0.explanation', 'Paracetamol dosage is 500mg-1000mg q4-6h.')
        ->assertJsonPath('attempt.answers.0.is_correct', false);
});

test('pharmacist cannot view or access another pharmacists attempt records (403 forbidden)', function () {
    [$userA, $tokenA] = createPharmacistForHistory('Pharmacist A');
    [$userB, $tokenB] = createPharmacistForHistory('Pharmacist B');

    createHistoryQuizWithAttempts($userA, 1);
    $attemptA = QuizAttempt::where('user_id', $userA->id)->first();

    // Flush auth guards between requests
    auth()->forgetGuards();
    app('auth')->forgetGuards();

    // Pharmacist B attempts to inspect Pharmacist A's attempt details
    $response = $this->withToken($tokenB)
        ->getJson("/api/v1/pharmacy/quizzes/attempts/{$attemptA->id}");

    $response->assertStatus(403)
        ->assertJsonPath('message', 'Unauthorized access.');
});

test('empty history returns clean paginated empty list with zero errors', function () {
    [$user, $token] = createPharmacistForHistory('Brand New Pharmacist');

    $response = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes/my-attempts');

    $response->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.total', 0);
});
