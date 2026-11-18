<?php

use App\Models\Pharmacy;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::create(['name' => 'pharmacy']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($this->role->id);
    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->pharmacy = Pharmacy::factory()->create([
        'user_id' => $this->user->id,
        'store_name' => 'City Care Pharmacy',
    ]);
});

test('pharmacy user can view pharmacy dashboard', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/pharmacy/dashboard');

    $response->assertOk()
        ->assertJsonPath('store_name', 'City Care Pharmacy')
        ->assertJsonStructure([
            'store_name',
            'status',
            'total_bills',
            'total_sourcing_records',
            'total_points',
            'total_bonus',
            'total_royalty',
        ]);
});

test('pharmacy user can view their pharmacy profile', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/pharmacy/profile');

    $response->assertOk()
        ->assertJsonPath('pharmacy.store_name', 'City Care Pharmacy');
});

test('pharmacy user can update pharmacy profile', function () {
    $response = $this->withToken($this->token)
        ->putJson('/api/v1/pharmacy/profile', [
            'store_name' => 'Updated City Care Pharmacy',
            'pan_number' => '123456789',
            'contact_person' => 'Jane Smith',
            'contact_number' => '9841234567',
            'address' => 'Kathmandu, Nepal',
        ]);

    $response->assertOk()
        ->assertJsonPath('pharmacy.store_name', 'Updated City Care Pharmacy');

    $this->assertDatabaseHas('pharmacies', [
        'id' => $this->pharmacy->id,
        'store_name' => 'Updated City Care Pharmacy',
    ]);
});
