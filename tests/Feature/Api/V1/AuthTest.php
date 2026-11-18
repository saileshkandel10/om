<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'shareholder']);
    Role::create(['name' => 'pharmacy']);
    Role::create(['name' => 'doctor']);
});

test('user can register with email and password', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'mobile' => '9841000000',
        'role' => 'shareholder',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'roles'],
            'token',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('user cannot register with duplicate email', function () {
    User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('password')]);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'shareholder',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'Password123!',
        'device_name' => 'test-device',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user', 'token']);
});

test('user cannot login with wrong password', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'WrongPassword!',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can fetch their profile', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/auth/user');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

test('user can logout', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $token = $user->createToken('test')->plainTextToken;

    $this->assertDatabaseCount('personal_access_tokens', 1);

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    // Token should be deleted from the database
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('unauthenticated requests to protected routes return 401', function () {
    $this->getJson('/api/v1/auth/user')->assertUnauthorized();
    $this->getJson('/api/v1/profile')->assertUnauthorized();
    $this->getJson('/api/v1/products')->assertUnauthorized();
});
