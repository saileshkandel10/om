<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createAdminForSettings(): User
{
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);

    return $admin;
}

function createPharmacistForSettings(): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => 'pharmacy'], ['display_name' => 'Pharmacy']);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('admin can view quiz settings configuration page', function () {
    $admin = createAdminForSettings();

    $response = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.settings.index'));

    $response->assertOk()
        ->assertViewIs('admin.pharmacist-quiz.settings.index')
        ->assertViewHas('settings')
        ->assertViewHas('stats');
});

test('admin can update quiz settings with valid parameters', function () {
    $admin = createAdminForSettings();

    $response = $this->actingAs($admin)
        ->put(route('admin.pharmacist-quiz.settings.update'), [
            'questions_per_quiz' => 20,
            'time_limit_minutes' => 15,
            'passing_percentage' => 80.00,
            'is_randomized_questions' => 1,
            'is_randomized_options' => 1,
            'show_explanations' => 1,
            'allow_retry' => 1,
            'max_attempts' => 5,
            'reward_points' => 100.00,
            'is_active' => 1,
            'instructions' => 'Custom updated pharmacist test instructions.',
        ]);

    $response->assertRedirect(route('admin.pharmacist-quiz.settings.index'))
        ->assertSessionHas('success');

    $settings = QuizSetting::getSettings();
    expect($settings->questions_per_quiz)->toBe(20)
        ->and($settings->time_limit_minutes)->toBe(15)
        ->and((float) $settings->passing_percentage)->toBe(80.00)
        ->and($settings->max_attempts)->toBe(5)
        ->and((float) $settings->reward_points)->toBe(100.00)
        ->and($settings->instructions)->toBe('Custom updated pharmacist test instructions.');
});

test('admin cannot update quiz settings with invalid values', function () {
    $admin = createAdminForSettings();

    $response = $this->actingAs($admin)
        ->put(route('admin.pharmacist-quiz.settings.update'), [
            'questions_per_quiz' => 0, // invalid: min 1
            'time_limit_minutes' => -5, // invalid
            'passing_percentage' => 150, // invalid: max 100
            'max_attempts' => 0, // invalid
            'reward_points' => -10, // invalid
        ]);

    $response->assertSessionHasErrors([
        'questions_per_quiz',
        'time_limit_minutes',
        'passing_percentage',
        'max_attempts',
        'reward_points',
    ]);
});

test('pharmacist API returns dynamic backend settings accurately', function () {
    [$user, $token] = createPharmacistForSettings();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'questions_per_quiz' => 12,
        'time_limit_minutes' => 10,
        'passing_percentage' => 75.00,
        'reward_points' => 60.00,
        'is_active' => true,
    ]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/pharmacy/quizzes/settings');

    $response->assertOk()
        ->assertJsonPath('settings.questions_per_quiz', 12)
        ->assertJsonPath('settings.time_limit_minutes', 10)
        ->assertJsonPath('settings.passing_percentage', 75)
        ->assertJsonPath('settings.reward_points', 60)
        ->assertJsonPath('settings.is_currently_active', true);
});

test('pharmacist cannot start quiz when global settings are inactive', function () {
    [$user, $token] = createPharmacistForSettings();

    $settings = QuizSetting::getSettings();
    $settings->update(['is_active' => false]);

    $cat = QuizCategory::create(['name' => 'General', 'slug' => 'general']);
    $quiz = Quiz::create([
        'quiz_category_id' => $cat->id,
        'title' => 'Sample Active Quiz',
        'slug' => 'sample-active-quiz',
        'is_active' => true,
    ]);

    $q1 = QuizQuestion::create(['quiz_id' => $quiz->id, 'question_text' => 'Sample Q', 'is_active' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'A', 'is_correct' => true]);

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'The Pharmacist Quiz system is currently unavailable or off schedule.');
});

test('pharmacist cannot retry quiz when allow_retry is disabled in settings', function () {
    [$user, $token] = createPharmacistForSettings();

    $settings = QuizSetting::getSettings();
    $settings->update([
        'is_active' => true,
        'allow_retry' => false,
    ]);

    $cat = QuizCategory::create(['name' => 'General', 'slug' => 'general']);
    $quiz = Quiz::create([
        'quiz_category_id' => $cat->id,
        'title' => 'No Retry Quiz',
        'slug' => 'no-retry-quiz',
        'is_active' => true,
    ]);

    $q1 = QuizQuestion::create(['quiz_id' => $quiz->id, 'question_text' => 'Sample Q', 'is_active' => true]);
    QuizQuestionOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'A', 'is_correct' => true]);

    // Already completed one attempt
    QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 1,
        'status' => 'completed',
        'started_at' => now()->subHour(),
        'completed_at' => now()->subMinutes(30),
    ]);

    $response = $this->withToken($token)
        ->postJson("/api/v1/pharmacy/quizzes/{$quiz->id}/start");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Retrying quizzes is currently disabled by administrator.');
});
