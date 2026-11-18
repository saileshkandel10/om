<?php

use App\Models\Campaign;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::create(['name' => 'shareholder']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($this->role->id);
    $this->token = $this->user->createToken('test')->plainTextToken;
});

test('user can list active campaigns', function () {
    Campaign::factory()->create([
        'name' => 'Summer Health Awareness',
        'status' => 'active',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'target_segment' => 'shareholder',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/campaigns');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Summer Health Awareness');
});

test('user can view single campaign', function () {
    $campaign = Campaign::factory()->create([
        'name' => 'Cardio Care Camp',
        'status' => 'active',
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/campaigns/{$campaign->id}");

    $response->assertOk()
        ->assertJsonPath('data.name', 'Cardio Care Camp');
});
