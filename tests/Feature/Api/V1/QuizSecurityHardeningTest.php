<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPharmacistForSecurity(string $name = 'Pharmacist'): array
{
    $user = User::factory()->create(['name' => $name, 'password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function setupSecurityTestQuiz(): array
{
    $category = QuizCategory::create(['name' => 'Pharmacology Security', 'slug' => 'pharmacology-security', 'is_active' => true]);

    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Security Audit Quiz',
        'slug' => 'security-audit-quiz',
        'passing_percentage' => 70.00,
        'reward_points' => 50.00,
        'time_limit_minutes' => 10,
        'is_active' => true,
    ]);

    $q1 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question_text' => 'What is the standard storage temperature for cold-chain vaccines?',
        'points' => 1,
        'is_active' => true,
    ]);

    $opt1 = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '+2°C to +8°C', 'is_correct' => true, 'display_order' => 1]);
    $opt2 = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '-20°C', 'is_correct' => false, 'display_order' => 2]);

    return [$quiz, $q1, $opt1, $opt2];
}

test('attempt generates tamper-resistant UUID on creation', function () {
    [$user, $token] = createPharmacistForSecurity();
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();

    $res = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $res->assertStatus(201);

    $attemptId = $res->json('attempt.id');
    $attempt = QuizAttempt::find($attemptId);

    expect($attempt->uuid)->not->toBeNull()
        ->and(strlen($attempt->uuid))->toBe(36);
});

test('ownership validation rejects attempt start and submit by non-pharmacist or foreign user (403)', function () {
    [$userA, $tokenA] = createPharmacistForSecurity('User A');
    [$userB, $tokenB] = createPharmacistForSecurity('User B');
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();

    $resA = $this->withToken($tokenA)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptIdA = $resA->json('attempt.id');

    auth()->forgetGuards();
    app('auth')->forgetGuards();

    // User B tries to view User A's attempt
    $viewRes = $this->withToken($tokenB)->getJson("/api/v1/pharmacy/quizzes/attempts/{$attemptIdA}");
    $viewRes->assertStatus(403)
        ->assertJsonPath('message', 'Unauthorized access.');

    // User B tries to submit User A's attempt
    $submitRes = $this->withToken($tokenB)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptIdA}/submit", [
        'answers' => [
            ['question_id' => $q1->id, 'selected_option_id' => $opt1->id],
        ],
    ]);
    $submitRes->assertStatus(403)
        ->assertJsonPath('message', 'Unauthorized access to this attempt.');
});

test('server-side timer strictly blocks submission after expiration', function () {
    [$user, $token] = createPharmacistForSecurity();
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();
    $quiz->update(['time_limit_minutes' => 5]);

    // Create an expired attempt started 15 minutes ago
    $expiredAttempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'in_progress',
        'started_at' => now()->subMinutes(15),
    ]);

    $response = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$expiredAttempt->id}/submit", [
        'answers' => [
            ['question_id' => $q1->id, 'selected_option_id' => $opt1->id],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'This quiz attempt has exceeded the time limit and has expired.');

    expect($expiredAttempt->fresh()->status)->toBe('expired');
});

test('duplicate submissions on completed attempts are strictly rejected (422)', function () {
    [$user, $token] = createPharmacistForSecurity();
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();

    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptId = $startRes->json('attempt.id');

    $payload = [
        'answers' => [
            ['question_id' => $q1->id, 'selected_option_id' => $opt1->id],
        ],
    ];

    // First submit -> 200 OK
    $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $payload)->assertOk();

    // Re-submission -> 422 rejected
    $secondRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $payload);
    $secondRes->assertStatus(422)
        ->assertJsonPath('message', 'This quiz attempt has already been submitted and scored.');
});

test('tampered score payload is ignored and score is computed strictly server-side', function () {
    [$user, $token] = createPharmacistForSecurity();
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();

    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptId = $startRes->json('attempt.id');

    // Attacker submits wrong answer ($opt2) but sends forged score parameters
    $tamperedPayload = [
        'answers' => [
            ['question_id' => $q1->id, 'selected_option_id' => $opt2->id], // Wrong answer!
        ],
        'score' => 100,
        'percentage' => 100,
        'is_passed' => true,
        'reward_points_awarded' => 9999,
    ];

    $response = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", $tamperedPayload);

    $response->assertOk()
        ->assertJsonPath('is_passed', false)
        ->assertJsonPath('reward_points_awarded', 0)
        ->assertJsonPath('summary.score', 0)
        ->assertJsonPath('summary.percentage', 0);

    $attempt = QuizAttempt::find($attemptId);
    expect($attempt->is_passed)->toBeFalse()
        ->and($attempt->score)->toBe(0)
        ->and((float) $attempt->reward_points_awarded)->toBe(0.0);
});

test('submitting rogue option ID from another question is safely scored as wrong', function () {
    [$user, $token] = createPharmacistForSecurity();
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();

    // Create a foreign question & option
    $foreignQ = QuizQuestion::create(['question_text' => 'Foreign Q', 'is_active' => true]);
    $foreignOpt = QuizQuestionOption::create(['quiz_question_id' => $foreignQ->id, 'option_text' => 'Foreign Opt', 'is_correct' => true]);

    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptId = $startRes->json('attempt.id');

    // Submit foreign option ID for $q1
    $response = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", [
        'answers' => [
            ['question_id' => $q1->id, 'selected_option_id' => $foreignOpt->id],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('is_passed', false)
        ->assertJsonPath('summary.score', 0);
});

test('start quiz API response never reveals is_correct, explanation, or correct_option_id', function () {
    [$user, $token] = createPharmacistForSecurity();
    [$quiz, $q1, $opt1, $opt2] = setupSecurityTestQuiz();

    $response = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $response->assertStatus(201);

    $json = $response->json();
    $attempt = $json['attempt'];

    expect(array_key_exists('answers', $attempt))->toBeFalse();

    foreach ($attempt['questions'] as $q) {
        expect(array_key_exists('explanation', $q))->toBeFalse()
            ->and(array_key_exists('correct_option_id', $q))->toBeFalse();

        foreach ($q['options'] as $opt) {
            expect(array_key_exists('is_correct', $opt))->toBeFalse()
                ->and(array_key_exists('correct', $opt))->toBeFalse();
        }
    }
});
