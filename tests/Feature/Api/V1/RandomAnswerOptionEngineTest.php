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

function createPharmacistForOptionsEngine(string $name = 'Pharmacist'): array
{
    $user = User::factory()->create(['name' => $name, 'password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function createQuestionWithOptions(Quiz $quiz, QuizCategory $category, string $correctText = 'Option A (Correct)'): QuizQuestion
{
    $q = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question_text' => 'Which of the following statements regarding drug bioavailability is correct?',
        'difficulty' => 'medium',
        'explanation' => 'Bioavailability defines the rate and extent to which the active drug substance is absorbed.',
        'points' => 1,
        'is_active' => true,
    ]);

    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => $correctText, 'is_correct' => true, 'display_order' => 1]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option B (Distractor 1)', 'is_correct' => false, 'display_order' => 2]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option C (Distractor 2)', 'is_correct' => false, 'display_order' => 3]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option D (Distractor 3)', 'is_correct' => false, 'display_order' => 4]);

    return $q;
}

test('options order is locked and remains 100% consistent across attempt reloads and resumes', function () {
    [$user, $token] = createPharmacistForOptionsEngine();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 1,
        'is_randomized_options' => true,
        'is_active' => true,
    ]);

    $category = QuizCategory::create(['name' => 'Pharmacokinetics', 'slug' => 'pharmacokinetics', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Pharmacokinetics Quiz', 'slug' => 'pharmacokinetics-quiz', 'is_active' => true]);
    $question = createQuestionWithOptions($quiz, $category);

    // 1. Initial Start Request
    $res1 = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $res1->assertStatus(201);

    $attemptId = $res1->json('attempt.id');
    $initialOptionsOrder = collect($res1->json('attempt.questions.0.options'))->pluck('id')->toArray();

    // 2. Reload attempt via GET /api/v1/pharmacy/quizzes/attempts/{attempt}
    $res2 = $this->withToken($token)->getJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}");
    $res2->assertOk();
    $reloadOptionsOrder = collect($res2->json('attempt.questions.0.options'))->pluck('id')->toArray();

    // 3. Resume attempt via POST /api/v1/pharmacy/quizzes/{quiz}/start
    $res3 = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $res3->assertSuccessful();
    $resumeOptionsOrder = collect($res3->json('attempt.questions.0.options'))->pluck('id')->toArray();

    // The option order MUST remain 100% identical and stable across all reads
    expect($initialOptionsOrder)->toEqual($reloadOptionsOrder)
        ->and($initialOptionsOrder)->toEqual($resumeOptionsOrder);
});

test('evaluation accurately scores the selected option regardless of shuffled display position', function () {
    [$user, $token] = createPharmacistForOptionsEngine();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 1,
        'is_randomized_options' => true,
        'is_active' => true,
    ]);

    $category = QuizCategory::create(['name' => 'Clinical Safety', 'slug' => 'clinical-safety', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Safety Assessment', 'slug' => 'safety-assessment', 'is_active' => true]);
    $question = createQuestionWithOptions($quiz, $category, 'Correct Option Text');

    $correctOption = $question->options()->where('is_correct', true)->first();

    // Start attempt
    $startRes = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $startRes->assertStatus(201);
    $attemptId = $startRes->json('attempt.id');

    // Submit correct answer
    $submitRes = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/attempts/{$attemptId}/submit", [
            'answers' => [
                ['question_id' => $question->id, 'selected_option_id' => $correctOption->id],
            ],
        ]);

    $submitRes->assertOk()
        ->assertJsonPath('is_passed', true)
        ->assertJsonPath('attempt.score', 1)
        ->assertJsonPath('attempt.percentage', 100);

    // Verify answers breakdown shows correctly
    $answerItem = $submitRes->json('attempt.answers.0');
    expect($answerItem['is_correct'])->toBeTrue()
        ->and($answerItem['selected_option_id'])->toBe($correctOption->id)
        ->and($answerItem['correct_option_id'])->toBe($correctOption->id);
});

test('pharmacist API responses never leak correct answer flag in options', function () {
    [$user, $token] = createPharmacistForOptionsEngine();

    $settings = QuizSetting::getSettings();
    $settings->update(['is_randomized_options' => true, 'is_active' => true]);

    $category = QuizCategory::create(['name' => 'Security Test', 'slug' => 'security-test', 'is_active' => true]);
    $quiz = Quiz::create(['quiz_category_id' => $category->id, 'title' => 'Security Quiz', 'slug' => 'security-quiz', 'is_active' => true]);
    createQuestionWithOptions($quiz, $category);

    $res = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $res->assertStatus(201);

    $options = $res->json('attempt.questions.0.options');
    expect($options)->toHaveCount(4);

    foreach ($options as $opt) {
        expect(array_key_exists('id', $opt))->toBeTrue()
            ->and(array_key_exists('option_text', $opt))->toBeTrue()
            ->and(array_key_exists('is_correct', $opt))->toBeFalse()
            ->and(array_key_exists('correct', $opt))->toBeFalse()
            ->and(array_key_exists('answer', $opt))->toBeFalse();
    }
});

test('when is_randomized_options is disabled, original alphabetical display order is preserved', function () {
    [$user, $token] = createPharmacistForOptionsEngine();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 1,
        'is_randomized_options' => false,
        'is_active' => true,
    ]);

    $category = QuizCategory::create(['name' => 'Order Test', 'slug' => 'order-test', 'is_active' => true]);
    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Sequential Quiz',
        'slug' => 'sequential-quiz',
        'is_randomized' => false,
        'is_active' => true,
    ]);

    $question = createQuestionWithOptions($quiz, $category);
    $expectedOptionIds = $question->options()->orderBy('display_order')->pluck('id')->toArray();

    $res = $this->withToken($token)->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");
    $res->assertStatus(201);

    $deliveredOptionIds = collect($res->json('attempt.questions.0.options'))->pluck('id')->toArray();
    expect($deliveredOptionIds)->toEqual($expectedOptionIds);
});
