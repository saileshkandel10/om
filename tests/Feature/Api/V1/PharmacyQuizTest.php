<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\RewardWallet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPharmacistUser(): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function createSampleQuiz(): Quiz
{
    $category = QuizCategory::create([
        'name' => 'Clinical Pharmacology',
        'slug' => 'clinical-pharmacology',
        'description' => 'Clinical drug standards and interactions',
    ]);

    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Pharmacology Assessment',
        'slug' => 'pharmacology-assessment',
        'description' => 'Test your clinical pharmacy knowledge',
        'time_limit_minutes' => 15,
        'passing_percentage' => 60.00,
        'reward_points' => 50.00,
        'max_attempts' => 2,
        'is_active' => true,
    ]);

    $q1 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question_text' => 'What is the standard adult dose of Paracetamol for fever?',
        'explanation' => 'Standard adult dose is 500mg to 1000mg every 4-6 hours (max 4g/day).',
        'points' => 1,
        'is_active' => true,
    ]);

    QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '500mg to 1000mg', 'is_correct' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '5000mg single dose', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '10mg once daily', 'is_correct' => false]);

    $q2 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question_text' => 'Which class of antibiotics does Cefixime belong to?',
        'explanation' => 'Cefixime is a third-generation cephalosporin antibiotic.',
        'points' => 1,
        'is_active' => true,
    ]);

    QuizQuestionOption::create(['quiz_question_id' => $q2->id, 'option_text' => '3rd Generation Cephalosporin', 'is_correct' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'Macrolide', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'Fluoroquinolone', 'is_correct' => false]);

    return $quiz;
}

test('pharmacist can list active quizzes and categories', function () {
    [$user, $token] = createPharmacistUser();
    $quiz = createSampleQuiz();

    $response = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Pharmacology Assessment')
        ->assertJsonPath('data.0.reward_points', 50)
        ->assertJsonPath('data.0.user_stats.can_attempt', true);

    $catResponse = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes/categories');

    $catResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'clinical-pharmacology');
});

test('non-pharmacist user is unauthorized to access pharmacy quizzes', function () {
    $customer = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);
    $customer->roles()->syncWithoutDetaching([$role->id]);
    $token = $customer->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes');

    $response->assertStatus(403);
});

test('pharmacist can start quiz and questions do not expose correct answers', function () {
    [$user, $token] = createPharmacistUser();
    $quiz = createSampleQuiz();

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'attempt' => [
                'id',
                'quiz_id',
                'status',
                'questions' => [
                    '*' => [
                        'id',
                        'question_text',
                        'points',
                        'options' => [
                            '*' => [
                                'id',
                                'option_text',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    // Ensure is_correct is NOT exposed in the active attempt API response
    $responseData = $response->json();
    $options = $responseData['attempt']['questions'][0]['options'];
    foreach ($options as $opt) {
        expect(array_key_exists('is_correct', $opt))->toBeFalse();
    }
});

test('pharmacist can submit quiz answers and earn reward points upon passing', function () {
    [$user, $token] = createPharmacistUser();
    $quiz = createSampleQuiz();

    // Start attempt
    $startRes = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $attemptId = $startRes->json('attempt.id');

    $q1 = $quiz->questions()->first();
    $q1CorrectOpt = $q1->options()->where('is_correct', true)->first();

    $q2 = $quiz->questions()->skip(1)->first();
    $q2CorrectOpt = $q2->options()->where('is_correct', true)->first();

    // Submit 100% correct answers
    $submitRes = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", [
            'answers' => [
                ['question_id' => $q1->id, 'selected_option_id' => $q1CorrectOpt->id],
                ['question_id' => $q2->id, 'selected_option_id' => $q2CorrectOpt->id],
            ],
        ]);

    $submitRes->assertOk()
        ->assertJsonPath('is_passed', true)
        ->assertJsonPath('reward_points_awarded', 50)
        ->assertJsonPath('attempt.score', 2)
        ->assertJsonPath('attempt.percentage', 100);

    // Verify wallet was credited with 50 reward points
    $wallet = RewardWallet::where('user_id', $user->id)->first();
    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->total_points)->toBe(50.00);

    // Verify attempt history contains this attempt
    $historyRes = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes/my-attempts');

    $historyRes->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_passed', true);
});

test('pharmacist cannot exceed maximum allowed attempts', function () {
    [$user, $token] = createPharmacistUser();
    $quiz = createSampleQuiz(); // max_attempts = 2

    // Complete attempt 1
    $att1 = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 1,
        'status' => 'completed',
        'is_passed' => false,
        'started_at' => now()->subHour(),
        'completed_at' => now()->subMinutes(50),
    ]);

    // Complete attempt 2
    $att2 = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 2,
        'status' => 'completed',
        'is_passed' => false,
        'started_at' => now()->subMinutes(40),
        'completed_at' => now()->subMinutes(30),
    ]);

    // Attempt 3 should fail
    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'You have reached the maximum allowed attempts (2) for this quiz.');
});
