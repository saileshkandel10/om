<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::create(['name' => 'shareholder']);
    $this->user = User::factory()->create(['name' => 'Initial Name', 'email' => 'user@example.com']);
    $this->user->roles()->attach($this->role->id);
    $this->token = $this->user->createToken('test')->plainTextToken;
});

test('user can fetch full profile', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/profile');

    $response->assertOk()
        ->assertJsonPath('data.name', 'Initial Name')
        ->assertJsonPath('data.email', 'user@example.com');
});

test('user can update their profile', function () {
    $response = $this->withToken($this->token)
        ->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'mobile' => '9841999999',
            'gender' => 'male',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.mobile', '9841999999');

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Updated Name',
        'mobile' => '9841999999',
    ]);
});
