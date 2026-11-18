<?php

use App\Models\Role;
use App\Models\ShareholderAccount;
use App\Models\ShareholderMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::create(['name' => 'shareholder']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($this->role->id);
    $this->token = $this->user->createToken('test')->plainTextToken;
});

test('shareholder can view dashboard stats', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/shareholder/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'total_bills',
            'approved_bills',
            'pending_bills',
            'total_points',
            'total_bonus',
            'total_royalty',
            'boid_verified',
        ]);
});

test('shareholder can verify a valid boid', function () {
    $master = ShareholderMaster::factory()->create([
        'boid' => '1301000000001234',
        'shareholder_name' => 'John Doe',
    ]);

    $response = $this->withToken($this->token)
        ->postJson('/api/v1/shareholder/verify-boid', [
            'boid' => '1301000000001234',
        ]);

    $response->assertOk()
        ->assertJsonPath('verified', true)
        ->assertJsonPath('account.boid', '1301000000001234');

    $this->assertDatabaseHas('shareholder_accounts', [
        'user_id' => $this->user->id,
        'boid' => '1301000000001234',
        'verification_status' => 'verified',
    ]);
});

test('shareholder fails verification with invalid boid', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/shareholder/verify-boid', [
            'boid' => '9999999999999999',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('verified', false);
});

test('shareholder can retrieve their account info', function () {
    $master = ShareholderMaster::factory()->create(['boid' => '1301000000001234']);
    ShareholderAccount::factory()->create([
        'user_id' => $this->user->id,
        'shareholder_master_id' => $master->id,
        'boid' => '1301000000001234',
        'verification_status' => 'verified',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/shareholder/account');

    $response->assertOk()
        ->assertJsonPath('account.boid', '1301000000001234');
});
