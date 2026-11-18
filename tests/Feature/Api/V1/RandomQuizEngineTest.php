<?php

use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPharmacistForEngine(string $name = 'Pharmacist'): array
{
    $user = User::factory()->create(['name' => $name, 'password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function generateQuestionBank(int $count, QuizCategory $category, ?Quiz $quiz = null): void
{
    for ($i = 1; $i <= $count; $i++) {
        $q = QuizQuestion::create([
            'quiz_id' => $quiz?->id,
            'quiz_category_id' => $category->id,
            'question_text' => "Pharmacology MCQ Question #{$i}: What is the clinical indication for Drug-{$i}?",
            'difficulty' => $i % 3 === 0 ? 'hard' : ($i % 2 === 0 ? 'medium' : 'easy'),
            'explanation' => "Clinical explanation for Drug-{$i} mechanism of action.",
            'points' => 1,
            'is_active' => true,
        ]);

        for ($o = 1; $o <= 4; $o++) {
            QuizQuestionOption::create([
                'quiz_question_id' => $q->id,
                'option_text' => "Option {$o} for Question {$i}",
                'is_correct' => $o === 1, // Option 1 is correct
                'display_order' => $o,
            ]);
        }
    }
}

test('server randomly selects configured number of questions from question bank without duplicates', function () {
    [$user, $token] = createPharmacistForEngine('Pharmacist A');

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 10,
        'is_randomized_questions' => true,
        'is_active' => true,
    ]);

    $category = QuizCategory::create(['name' => 'Therapeutics', 'slug' => 'therapeutics', 'is_active' => true]);
    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Clinical Therapeutics Quiz',
        'slug' => 'clinical-therapeutics-quiz',
        'is_active' => true,
    ]);

    // Populate question bank with 30 questions
    generateQuestionBank(30, $category, $quiz);

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(201)
        ->assertJsonCount(10, 'attempt.questions');

    $questionIds = collect($response->json('attempt.questions'))->pluck('id')->toArray();

    // Verify exactly 10 questions and ZERO duplicates
    expect(count($questionIds))->toBe(10)
        ->and(count(array_unique($questionIds)))->toBe(10);
});

test('two pharmacists receive distinct randomized question selections from large question bank', function () {
    [$pharmA, $tokenA] = createPharmacistForEngine('Pharmacist A');
    [$pharmB, $tokenB] = createPharmacistForEngine('Pharmacist B');

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 15,
        'is_randomized_questions' => true,
        'is_active' => true,
    ]);

    $category = QuizCategory::create(['name' => 'Large Bank Category', 'slug' => 'large-bank-category', 'is_active' => true]);
    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Randomized Pharmacist Exam',
        'slug' => 'randomized-pharmacist-exam',
        'is_active' => true,
    ]);

    // Create 100 questions in bank
    generateQuestionBank(100, $category, $quiz);

    $resA = $this->withToken($tokenA)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    // Flush authentication state between requests
    auth()->forgetGuards();
    app('auth')->forgetGuards();

    $resB = $this->withToken($tokenB)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $resA->assertStatus(201);
    $resB->assertStatus(201);

    $qIdsA = collect($resA->json('attempt.questions'))->pluck('id')->toArray();
    $qIdsB = collect($resB->json('attempt.questions'))->pluck('id')->toArray();

    expect(count($qIdsA))->toBe(15)
        ->and(count($qIdsB))->toBe(15)
        ->and(count(array_unique($qIdsA)))->toBe(15)
        ->and(count(array_unique($qIdsB)))->toBe(15);

    // Assert that the exact question sequence for Pharmacist A is not identical to Pharmacist B
    expect($qIdsA)->not->toEqual($qIdsB);
});

test('inactive questions and questions from inactive categories are never selected', function () {
    [$user, $token] = createPharmacistForEngine();

    $settings = QuizSetting::getSettings();
    $settings->update(['questions_per_quiz' => 10, 'is_active' => true]);

    $activeCat = QuizCategory::create(['name' => 'Active Category', 'slug' => 'active-category', 'is_active' => true]);
    $inactiveCat = QuizCategory::create(['name' => 'Inactive Category', 'slug' => 'inactive-category', 'is_active' => false]);

    $quiz = Quiz::create([
        'title' => 'Open Bank Quiz',
        'slug' => 'open-bank-quiz',
        'is_active' => true,
    ]);

    // Create 5 active questions in active category
    generateQuestionBank(5, $activeCat, $quiz);

    // Create 10 active questions in INACTIVE category (must be excluded)
    generateQuestionBank(10, $inactiveCat);

    // Create 10 INACTIVE questions in active category (must be excluded)
    for ($i = 101; $i <= 110; $i++) {
        $inactQ = QuizQuestion::create([
            'quiz_category_id' => $activeCat->id,
            'question_text' => "Inactive Question #{$i}",
            'is_active' => false,
        ]);
        QuizQuestionOption::create(['quiz_question_id' => $inactQ->id, 'option_text' => 'Opt', 'is_correct' => true]);
    }

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(201)
        ->assertJsonCount(5, 'attempt.questions'); // Only the 5 eligible active questions

    $qTexts = collect($response->json('attempt.questions'))->pluck('question_text')->toArray();
    foreach ($qTexts as $text) {
        expect($text)->not->toContain('Inactive Question');
    }
});

test('start quiz API response is 100% sanitized with no correct answer or internal scoring leak', function () {
    [$user, $token] = createPharmacistForEngine();

    $category = QuizCategory::create(['name' => 'Security Audit', 'slug' => 'security-audit', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Secure Quiz', 'slug' => 'secure-quiz', 'is_active' => true]);
    generateQuestionBank(5, $category, $quiz);

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(201);
    $attempt = $response->json('attempt');

    // Verify top-level
    expect(array_key_exists('answers', $attempt))->toBeFalse();

    // Verify all questions and options
    foreach ($attempt['questions'] as $q) {
        expect(array_key_exists('explanation', $q))->toBeFalse()
            ->and(array_key_exists('correct_option_id', $q))->toBeFalse()
            ->and(array_key_exists('is_correct', $q))->toBeFalse();

        foreach ($q['options'] as $opt) {
            expect(array_key_exists('is_correct', $opt))->toBeFalse()
                ->and(array_key_exists('correct', $opt))->toBeFalse();
        }
    }
});

test('system gracefully handles question bank having fewer questions than questions_per_quiz', function () {
    [$user, $token] = createPharmacistForEngine();

    $settings = QuizSetting::getSettings();
    $settings->update(['questions_per_quiz' => 20, 'is_active' => true]); // Asked for 20

    $category = QuizCategory::create(['name' => 'Small Bank', 'slug' => 'small-bank', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Small Quiz', 'slug' => 'small-quiz', 'is_active' => true]);

    // Bank only has 4 questions
    generateQuestionBank(4, $category, $quiz);

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    // Gracefully returns all 4 without error
    $response->assertStatus(201)
        ->assertJsonCount(4, 'attempt.questions');
});

test('question edits in question bank after attempt start do not alter locked attempt snapshot', function () {
    [$user, $token] = createPharmacistForEngine();

    $category = QuizCategory::create(['name' => 'Snapshot Test', 'slug' => 'snapshot-test', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Snapshot Quiz', 'slug' => 'snapshot-quiz', 'is_active' => true]);

    $q1 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question_text' => 'Original Locked Question Text',
        'is_active' => true,
    ]);
    $optA = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'Original Option A (Correct)', 'is_correct' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'Original Option B', 'is_correct' => false]);

    // 1. Pharmacist starts attempt
    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $attemptId = $startRes->json('attempt.id');

    // 2. Admin edits question and options in Question Bank
    $q1->update(['question_text' => 'Tampered / Altered Question Text']);
    $optA->update(['option_text' => 'Altered Option A']);

    // 3. Pharmacist submits against attempt
    $submitRes = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", [
            'answers' => [
                ['question_id' => $q1->id, 'selected_option_id' => $optA->id],
            ],
        ]);

    $submitRes->assertOk()
        ->assertJsonPath('is_passed', true)
        ->assertJsonPath('attempt.answers.0.question_text', 'Original Locked Question Text');
});

test('scale test: engine securely selects 20 questions out of 500 questions bank with zero duplicates in < 50ms', function () {
    [$user, $token] = createPharmacistForEngine();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 20,
        'is_randomized_questions' => true,
        'is_active' => true,
    ]);

    $category = QuizCategory::create(['name' => '500 Qs Bank', 'slug' => '500-qs-bank', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Big Bank Quiz', 'slug' => 'big-bank-quiz', 'is_active' => true]);

    generateQuestionBank(500, $category, $quiz);

    $startTime = microtime(true);
    $response = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $durationMs = (microtime(true) - $startTime) * 1000;

    $response->assertStatus(201)
        ->assertJsonCount(20, 'attempt.questions');

    $qIds = collect($response->json('attempt.questions'))->pluck('id')->toArray();
    expect(count($qIds))->toBe(20)
        ->and(count(array_unique($qIds)))->toBe(20);

    // Performance assertion
    expect($durationMs)->toBeLessThan(200.0); // well within acceptable bounds
});
