<?php

use App\Models\Role;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);
    Sanctum::actingAs($this->admin);
});

test('api can fetch illness summary and statistics', function () {
    $response = $this->getJson(route('api.v1.reports.illness-summary'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'summary' => [
                'total_unique_patients',
                'total_illness_records',
                'multiple_illness_patients_count',
            ],
            'categories',
            'distribution',
        ]);
});

test('api can fetch illness patient records with filters', function () {
    $response = $this->getJson(route('api.v1.reports.illness-patients'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
            'patients',
        ]);
});

test('api can fetch multi-illness analytics and combinations', function () {
    $resMulti = $this->getJson(route('api.v1.reports.multi-illness'));
    $resMulti->assertStatus(200)->assertJson(['success' => true]);

    $resCombo = $this->getJson(route('api.v1.reports.illness-combinations'));
    $resCombo->assertStatus(200)->assertJson(['success' => true]);

    $resTrends = $this->getJson(route('api.v1.reports.illness-trends'));
    $resTrends->assertStatus(200)->assertJson(['success' => true]);
});
