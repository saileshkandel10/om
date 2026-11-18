<?php

use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdminForCategory(): User
{
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);

    return $admin;
}

test('admin can view quiz categories list and stats', function () {
    $admin = createAdminForCategory();
    QuizCategory::create(['name' => 'Medicine Knowledge', 'slug' => 'medicine-knowledge']);

    $response = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.categories.index'));

    $response->assertOk()
        ->assertViewIs('admin.pharmacist-quiz.categories.index')
        ->assertViewHas('categories')
        ->assertViewHas('stats');
});

test('admin can create a new quiz category', function () {
    $admin = createAdminForCategory();

    $response = $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.categories.store'), [
            'name' => 'Drug Interaction & Safety',
            'description' => 'Clinical pharmacology drug-drug interaction studies.',
            'icon' => 'las la-exclamation-triangle',
            'color' => '#DC2626',
            'display_order' => 5,
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.pharmacist-quiz.categories.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('quiz_categories', [
        'name' => 'Drug Interaction & Safety',
        'slug' => 'drug-interaction-safety',
        'color' => '#DC2626',
        'is_active' => true,
    ]);
});

test('admin can update an existing quiz category', function () {
    $admin = createAdminForCategory();
    $cat = QuizCategory::create([
        'name' => 'Old Category Name',
        'slug' => 'old-category-name',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.pharmacist-quiz.categories.update', $cat), [
            'name' => 'Updated Category Title',
            'description' => 'Updated description content.',
            'display_order' => 3,
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.pharmacist-quiz.categories.index'))
        ->assertSessionHas('success');

    expect($cat->fresh()->name)->toBe('Updated Category Title')
        ->and($cat->fresh()->slug)->toBe('updated-category-title');
});

test('admin can toggle active status of a quiz category', function () {
    $admin = createAdminForCategory();
    $cat = QuizCategory::create([
        'name' => 'Test Status Toggle',
        'slug' => 'test-status-toggle',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.categories.toggle-status', $cat));

    $response->assertRedirect();
    expect($cat->fresh()->is_active)->toBeFalse();

    // Toggle back to true
    $this->actingAs($admin)
        ->post(route('admin.pharmacist-quiz.categories.toggle-status', $cat));
    expect($cat->fresh()->is_active)->toBeTrue();
});

test('admin can soft delete empty category but cannot delete category with linked quizzes', function () {
    $admin = createAdminForCategory();

    $catWithQuizzes = QuizCategory::create(['name' => 'Has Quizzes', 'slug' => 'has-quizzes']);
    Quiz::create(['quiz_category_id' => $catWithQuizzes->id, 'title' => 'Sample Quiz', 'slug' => 'sample-quiz']);

    $catEmpty = QuizCategory::create(['name' => 'Empty Category', 'slug' => 'empty-category']);

    // Attempt deleting non-empty category -> must be blocked
    $blockedRes = $this->actingAs($admin)
        ->delete(route('admin.pharmacist-quiz.categories.destroy', $catWithQuizzes));

    $blockedRes->assertRedirect(route('admin.pharmacist-quiz.categories.index'))
        ->assertSessionHas('error');

    expect($catWithQuizzes->fresh())->not->toBeNull();

    // Delete empty category -> soft deletes successfully
    $deleteRes = $this->actingAs($admin)
        ->delete(route('admin.pharmacist-quiz.categories.destroy', $catEmpty));

    $deleteRes->assertRedirect(route('admin.pharmacist-quiz.categories.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('quiz_categories', ['id' => $catEmpty->id]);
});
