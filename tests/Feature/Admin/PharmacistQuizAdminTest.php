<?php

use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view pharmacist quiz module and stats', function () {
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);

    $category = QuizCategory::create([
        'name' => 'Pharmacology',
        'slug' => 'pharmacology',
    ]);

    Quiz::create([
        'quiz_category_id' => $category->id,
        'title' => 'Sample Pharmacology Test',
        'slug' => 'sample-pharmacology-test',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.pharmacist-quiz.index'));

    $response->assertOk()
        ->assertViewIs('admin.pharmacist-quiz.index')
        ->assertViewHas('quizzes')
        ->assertViewHas('categories')
        ->assertViewHas('stats');
});
