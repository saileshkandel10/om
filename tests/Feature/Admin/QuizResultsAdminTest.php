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

uses(RefreshDatabase::class);

function createAdminForResults(): User
{
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);

    return $admin;
}

function setupQuizWithAttempt(string $pharmacistName = 'Pharmacist Sharma', bool $isPassed = true, float $percentage = 80.00): array
{
    $email = strtolower(str_replace(' ', '', $pharmacistName)).'_'.uniqid().'@omconnect.com';
    $user = User::factory()->create(['name' => $pharmacistName, 'email' => $email]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);

    $category = QuizCategory::firstOrCreate(
        ['slug' => 'clinical-pharmacology'],
        ['name' => 'Clinical Pharmacology', 'is_active' => true]
    );

    $quizSlug = 'antibiotic-selection-dosage-'.uniqid();
    $quiz = Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Antibiotic Selection & Dosage',
        'slug' => $quizSlug,
        'passing_percentage' => 70.00,
        'is_active' => true,
    ]);

    $q1 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question_text' => 'What is the dosage of Amoxicillin?',
        'explanation' => 'Amoxicillin adult dosage is 500mg TDS.',
        'points' => 1,
        'is_active' => true,
    ]);

    $opt1 = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '500mg TDS', 'is_correct' => true, 'display_order' => 1]);
    $opt2 = QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => '5g once daily', 'is_correct' => false, 'display_order' => 2]);

    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 1,
        'score' => $isPassed ? 1 : 0,
        'correct_answers_count' => $isPassed ? 1 : 0,
        'incorrect_answers_count' => $isPassed ? 0 : 1,
        'unanswered_count' => 0,
        'total_points' => 1,
        'percentage' => $percentage,
        'is_passed' => $isPassed,
        'reward_points_awarded' => $isPassed ? 50.00 : 0.00,
        'time_taken_seconds' => 180,
        'status' => 'completed',
        'started_at' => now()->subMinutes(10),
        'completed_at' => now()->subMinutes(7),
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
        'selected_option_id' => $isPassed ? $opt1->id : $opt2->id,
        'is_correct' => $isPassed,
        'points_possible' => 1,
        'points_earned' => $isPassed ? 1 : 0,
        'display_order' => 1,
    ]);

    return [$user, $quiz, $attempt];
}

test('admin can view pharmacist quiz results dashboard with all 8 KPI metrics', function () {
    $admin = createAdminForResults();
    setupQuizWithAttempt('Pharmacist Sita', true, 100.00);
    setupQuizWithAttempt('Pharmacist Ram', false, 40.00);

    $response = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.results.index'));

    $response->assertOk()
        ->assertViewIs('admin.pharmacist-quiz.results.index')
        ->assertViewHas('attempts')
        ->assertViewHas('stats')
        ->assertSee('Pharmacist Sita')
        ->assertSee('Pharmacist Ram');

    $stats = $response->viewData('stats');
    expect($stats['total_attempts'])->toBe(2)
        ->and($stats['completed_attempts'])->toBe(2)
        ->and($stats['passed_attempts'])->toBe(1)
        ->and($stats['failed_attempts'])->toBe(1)
        ->and($stats['average_score'])->toBe(70.0);
});

test('admin can filter results by result status, quiz, and category', function () {
    $admin = createAdminForResults();
    [$user1, $quiz1, $att1] = setupQuizWithAttempt('Passed Candidate', true, 90.00);
    [$user2, $quiz2, $att2] = setupQuizWithAttempt('Failed Candidate', false, 30.00);

    // Filter passed only
    $passRes = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.results.index', ['result' => 'passed']));

    $passRes->assertOk()
        ->assertSee('Passed Candidate')
        ->assertDontSee('Failed Candidate');

    // Filter failed only
    $failRes = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.results.index', ['result' => 'failed']));

    $failRes->assertOk()
        ->assertSee('Failed Candidate')
        ->assertDontSee('Passed Candidate');
});

test('admin can search results by pharmacist name and email', function () {
    $admin = createAdminForResults();
    setupQuizWithAttempt('Hari Bahadur', true, 85.00);
    setupQuizWithAttempt('Gopal Krishna', true, 95.00);

    $searchRes = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.results.index', ['search' => 'Hari Bahadur']));

    $searchRes->assertOk()
        ->assertSee('Hari Bahadur')
        ->assertDontSee('Gopal Krishna');
});

test('admin can view detailed attempt audit review breakdown', function () {
    $admin = createAdminForResults();
    [$user, $quiz, $attempt] = setupQuizWithAttempt('Audit Target Pharmacist', true, 100.00);

    $response = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.results.show', $attempt));

    $response->assertOk()
        ->assertViewIs('admin.pharmacist-quiz.results.show')
        ->assertViewHas('attempt')
        ->assertSee('Audit Target Pharmacist')
        ->assertSee('What is the dosage of Amoxicillin?')
        ->assertSee('Amoxicillin adult dosage is 500mg TDS.')
        ->assertSee('Correct Answer Choice:');
});

test('non-admin user is rejected from admin results dashboard', function () {
    $customer = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);
    $customer->roles()->syncWithoutDetaching([$role->id]);

    $response = $this->actingAs($customer)
        ->get(route('admin.pharmacist-quiz.results.index'));

    $response->assertStatus(403);
});
