<?php

use App\Models\Notice;
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

test('user can list published notices', function () {
    Notice::factory()->create([
        'title' => 'Annual General Meeting',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'audience_type' => 'all',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/notices');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Annual General Meeting');
});

test('user can view single notice', function () {
    $notice = Notice::factory()->create([
        'title' => 'Important Notice',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/notices/{$notice->id}");

    $response->assertOk()
        ->assertJsonPath('data.title', 'Important Notice');
});
