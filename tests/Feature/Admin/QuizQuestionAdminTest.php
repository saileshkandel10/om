<?php

use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdminForQuestion(): User
{
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);

    return $admin;
}

test('admin can view question bank index with stats and filters', function () {
    $admin = createAdminForQuestion();

    $cat = QuizCategory::create(['name' => 'Medicine Knowledge', 'slug' => 'medicine-knowledge']);
    $q = QuizQuestion::create([
        'quiz_category_id' => $cat->id,
        'question_text' => 'What is the dosage of Paracetamol?',
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => '500mg', 'is_correct' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => '5000mg', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => '10mg', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => '1mg', 'is_correct' => false]);

    $response = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.questions.index'));

    $response->assertOk()
        ->assertViewIs('admin.pharmacist-quiz.questions.index')
        ->assertViewHas('questions')
        ->assertViewHas('stats')
        ->assertSee('What is the dosage of Paracetamol?');
});

test('admin can create question with 4 options and designated correct answer', function () {
    $admin = createAdminForQuestion();
    $cat = QuizCategory::create(['name' => 'Drug Interaction', 'slug' => 'drug-interaction']);

    $response = $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.questions.store'), [
            'question_text' => 'Which drug interacts adversely with Warfarin?',
            'quiz_category_id' => $cat->id,
            'difficulty' => 'hard',
            'options' => [
                'Aspirin (increases bleeding risk)',
                'Vitamin C',
                'Calcium carbonate',
                'Oral rehydration salt',
            ],
            'correct_option' => 0, // Option A
            'explanation' => 'Aspirin and NSAIDs have antiplatelet effects that compound Warfarin bleeding risks.',
            'product_reference' => 'Warfarin 5mg',
            'points' => 2,
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.pharmacist-quiz.questions.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('quiz_questions', [
        'question_text' => 'Which drug interacts adversely with Warfarin?',
        'quiz_category_id' => $cat->id,
        'difficulty' => 'hard',
        'points' => 2,
        'is_active' => true,
    ]);

    $question = QuizQuestion::where('question_text', 'Which drug interacts adversely with Warfarin?')->first();
    expect($question->options)->toHaveCount(4);

    $correctOption = $question->options->firstWhere('is_correct', true);
    expect($correctOption)->not->toBeNull()
        ->and($correctOption->option_text)->toBe('Aspirin (increases bleeding risk)');
});

test('admin cannot create question with missing options or invalid fields', function () {
    $admin = createAdminForQuestion();

    $response = $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.questions.store'), [
            'question_text' => '',
            'quiz_category_id' => 99999, // non-existent
            'difficulty' => 'invalid_diff',
            'options' => ['Option 1', 'Option 2'], // less than 4
            'correct_option' => 5, // invalid index
        ]);

    $response->assertSessionHasErrors([
        'question_text',
        'quiz_category_id',
        'difficulty',
        'options',
        'correct_option',
    ]);
});

test('admin can update an existing question and its 4 options', function () {
    $admin = createAdminForQuestion();
    $cat = QuizCategory::create(['name' => 'OMPL Knowledge', 'slug' => 'ompl-knowledge']);

    $q = QuizQuestion::create([
        'quiz_category_id' => $cat->id,
        'question_text' => 'Original Question Text',
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Old A', 'is_correct' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Old B', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Old C', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Old D', 'is_correct' => false]);

    $response = $this->actingAs($admin)
        ->put(route('admin.pharmacist-quiz.questions.update', $q), [
            'question_text' => 'Updated Question Prompt Text',
            'quiz_category_id' => $cat->id,
            'difficulty' => 'medium',
            'options' => ['New Option A', 'New Option B (Correct)', 'New Option C', 'New Option D'],
            'correct_option' => 1, // Option B is correct
            'explanation' => 'Updated clinical explanation text.',
            'points' => 1,
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.pharmacist-quiz.questions.index'))
        ->assertSessionHas('success');

    expect($q->fresh()->question_text)->toBe('Updated Question Prompt Text')
        ->and($q->fresh()->difficulty)->toBe('medium')
        ->and($q->fresh()->options)->toHaveCount(4);

    $correctOption = $q->fresh()->options->firstWhere('is_correct', true);
    expect($correctOption->option_text)->toBe('New Option B (Correct)');
});

test('admin can duplicate question with all 4 options', function () {
    $admin = createAdminForQuestion();
    $cat = QuizCategory::create(['name' => 'Pharmacy Practice', 'slug' => 'pharmacy-practice']);

    $q = QuizQuestion::create([
        'quiz_category_id' => $cat->id,
        'question_text' => 'Good Pharmacy Practice Question',
        'difficulty' => 'medium',
        'is_active' => true,
    ]);

    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option 1', 'is_correct' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option 2', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option 3', 'is_correct' => false]);
    QuizQuestionOption::create(['quiz_question_id' => $q->id, 'option_text' => 'Option 4', 'is_correct' => false]);

    $response = $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.questions.duplicate', $q));

    $response->assertRedirect(route('admin.pharmacist-quiz.questions.index'))
        ->assertSessionHas('success');

    $duplicated = QuizQuestion::where('question_text', '[Copy] Good Pharmacy Practice Question')->first();
    expect($duplicated)->not->toBeNull()
        ->and($duplicated->options)->toHaveCount(4)
        ->and($duplicated->options->firstWhere('is_correct', true)->option_text)->toBe('Option 1');
});

test('admin can toggle question active status and soft delete', function () {
    $admin = createAdminForQuestion();
    $cat = QuizCategory::create(['name' => 'General Knowledge', 'slug' => 'general-knowledge']);

    $q = QuizQuestion::create([
        'quiz_category_id' => $cat->id,
        'question_text' => 'Toggle Status Question',
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    // Toggle Status
    $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.questions.toggle-status', $q));
    expect($q->fresh()->is_active)->toBeFalse();

    // Soft Delete
    $deleteRes = $this->actingAs($admin)
        ->delete(route('admin.pharmacist-quiz.questions.destroy', $q));

    $deleteRes->assertRedirect(route('admin.pharmacist-quiz.questions.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('quiz_questions', ['id' => $q->id]);
});
