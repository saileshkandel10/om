<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizSetting;
use App\Models\RewardWallet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPharmacistForSubmission(string $name = 'Pharmacist'): array
{
    $user = User::factory()->create(['name' => $name, 'password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function setupScoringQuiz(): array
{
    $category = QuizCategory::create(['name' => 'Clinical Pharmacology', 'slug' => 'clinical-pharmacology', 'is_active' => true]);

    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Dispensing & Safety Assessment',
        'slug' => 'dispensing-safety-assessment',
        'passing_percentage' => 70.00,
        'reward_points' => 50.00,
        'time_limit_minutes' => 10,
        'is_active' => true,
    ]);

    $questions = [];
    for ($i = 1; $i <= 5; $i++) {
        $q = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'quiz_category_id' => $category->id,
            'question_text' => "Question {$i}",
            'points' => 1,
            'is_active' => true,
        ]);

        $correct = QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => "Correct {$i}", 'is_correct' => true, 'display_order' => 1]);
        $wrong = QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => "Wrong {$i}", 'is_correct' => false, 'display_order' => 2]);

        $questions[] = ['question' => $q, 'correct' => $correct, 'wrong' => $wrong];
    }

    return [$quiz, $questions];
}

test('pharmacist can submit valid attempt with full summary breakdown calculated authoritatively by backend', function () {
    [$user, $token] = createPharmacistForSubmission();
    [$quiz, $questions] = setupScoringQuiz();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 5,
        'passing_percentage' => 70.00,
        'reward_points' => 50.00,
        'is_active' => true,
    ]);

    // Start attempt
    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $startRes->assertStatus(201);
    $attemptId = $startRes->json('attempt.id');

    // Submit: 4 correct, 1 wrong = 4/5 = 80% (>= 70% passing)
    $submitPayload = [
        'answers' => [
            ['question_id' => $questions[0]['question']->id, 'selected_option_id' => $questions[0]['correct']->id],
            ['question_id' => $questions[1]['question']->id, 'selected_option_id' => $questions[1]['correct']->id],
            ['question_id' => $questions[2]['question']->id, 'selected_option_id' => $questions[2]['correct']->id],
            ['question_id' => $questions[3]['question']->id, 'selected_option_id' => $questions[3]['correct']->id],
            ['question_id' => $questions[4]['question']->id, 'selected_option_id' => $questions[4]['wrong']->id],
        ],
    ];

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $submitPayload);

    $response->assertOk()
        ->assertJsonPath('is_passed', true)
        ->assertJsonPath('reward_points_awarded', 50)
        ->assertJsonPath('summary.total_questions', 5)
        ->assertJsonPath('summary.correct_answers', 4)
        ->assertJsonPath('summary.incorrect_answers', 1)
        ->assertJsonPath('summary.unanswered_questions', 0)
        ->assertJsonPath('summary.score', 4)
        ->assertJsonPath('summary.total_points', 5)
        ->assertJsonPath('summary.percentage', 80)
        ->assertJsonPath('summary.passing_percentage', 70);

    // Verify wallet was credited 50 points
    $wallet = RewardWallet::where('user_id', $user->id)->first();
    expect((float) $wallet->total_points)->toBe(50.00);

    // Verify attempt in DB is marked completed
    $attempt = QuizAttempt::find($attemptId);
    expect($attempt->status)->toBe('completed')
        ->and($attempt->is_passed)->toBeTrue()
        ->and($attempt->completed_at)->not->toBeNull();
});

test('pharmacist cannot submit an attempt that has already been submitted (duplicate submission prevention)', function () {
    [$user, $token] = createPharmacistForSubmission();
    [$quiz, $questions] = setupScoringQuiz();

    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptId = $startRes->json('attempt.id');

    $payload = [
        'answers' => [
            ['question_id' => $questions[0]['question']->id, 'selected_option_id' => $questions[0]['correct']->id],
        ],
    ];

    // First submit -> 200 OK
    $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $payload)->assertOk();

    // Second submit -> 422 Unprocessable (Duplicate submission rejected)
    $secondRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $payload);
    $secondRes->assertStatus(422)
        ->assertJsonPath('message', 'This quiz attempt has already been submitted and scored.');
});

test('pharmacist cannot submit an attempt belonging to another user', function () {
    [$userA, $tokenA] = createPharmacistForSubmission('User A');
    [$userB, $tokenB] = createPharmacistForSubmission('User B');
    [$quiz, $questions] = setupScoringQuiz();

    $startRes = $this->withToken($tokenA)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptIdA = $startRes->json('attempt.id');

    // User B attempts to submit User A's attempt
    auth()->forgetGuards();
    app('auth')->forgetGuards();

    $response = $this->withToken($tokenB)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptIdA}/submit", [
            'answers' => [
                ['question_id' => $questions[0]['question']->id, 'selected_option_id' => $questions[0]['correct']->id],
            ],
        ]);

    $response->assertStatus(403)
        ->assertJsonPath('message', 'Unauthorized access to this attempt.');
});

test('pharmacist cannot submit an expired attempt past time limit', function () {
    [$user, $token] = createPharmacistForSubmission();
    [$quiz, $questions] = setupScoringQuiz();

    $quiz->update(['time_limit_minutes' => 5]);

    // Create attempt started 10 minutes ago (> 5 mins limit)
    $expiredAttempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'in_progress',
        'started_at' => now()->subMinutes(10),
    ]);

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$expiredAttempt->id}/submit", [
            'answers' => [
                ['question_id' => $questions[0]['question']->id, 'selected_option_id' => $questions[0]['correct']->id],
            ],
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'This quiz attempt has exceeded the time limit and has expired.');

    expect($expiredAttempt->fresh()->status)->toBe('expired');
});

test('unanswered questions receive zero points and failing score does not award rewards', function () {
    [$user, $token] = createPharmacistForSubmission();
    [$quiz, $questions] = setupScoringQuiz(); // 5 questions

    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptId = $startRes->json('attempt.id');

    // Only answer 1 question correctly, 4 unanswered -> 1/5 = 20% (Fail)
    $payload = [
        'answers' => [
            ['question_id' => $questions[0]['question']->id, 'selected_option_id' => $questions[0]['correct']->id],
            ['question_id' => $questions[1]['question']->id, 'selected_option_id' => null],
            ['question_id' => $questions[2]['question']->id, 'selected_option_id' => null],
            ['question_id' => $questions[3]['question']->id, 'selected_option_id' => null],
            ['question_id' => $questions[4]['question']->id, 'selected_option_id' => null],
        ],
    ];

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $payload);

    $response->assertOk()
        ->assertJsonPath('is_passed', false)
        ->assertJsonPath('reward_points_awarded', 0)
        ->assertJsonPath('summary.correct_answers', 1)
        ->assertJsonPath('summary.unanswered_questions', 4)
        ->assertJsonPath('summary.percentage', 20);

    $wallet = RewardWallet::where('user_id', $user->id)->first();
    expect($wallet)->toBeNull(); // No rewards created
});
