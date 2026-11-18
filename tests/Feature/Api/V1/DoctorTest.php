<?php

use App\Models\Doctor;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::create(['name' => 'doctor']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($this->role->id);
    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->doctor = Doctor::factory()->create([
        'user_id' => $this->user->id,
        'registration_no' => 'NMC12345',
        'specialty' => 'Cardiology',
    ]);
});

test('doctor can view doctor dashboard', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/doctor/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'doctor',
            'total_prescriptions',
            'total_points',
            'total_bonus',
        ]);
});

test('doctor can create manual prescription with items', function () {
    $product = Product::factory()->create(['brand_name' => 'OmParacetamol']);

    $response = $this->withToken($this->token)
        ->postJson('/api/v1/doctor/prescriptions', [
            'patient_ref_code' => 'PAT-1001',
            'prescription_date' => now()->toDateString(),
            'source_type' => 'manual',
            'notes' => 'Take with food',
            'items' => [
                [
                    'product_id' => $product->id,
                    'medicine_name_raw' => 'OmParacetamol 500mg',
                    'dosage' => '500mg',
                    'frequency' => 'Twice daily',
                    'duration_days' => 5,
                    'quantity_prescribed' => 10,
                ],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('prescription.patient_ref_code', 'PAT-1001');

    $this->assertDatabaseHas('prescriptions', [
        'doctor_id' => $this->doctor->id,
        'patient_ref_code' => 'PAT-1001',
    ]);

    $this->assertDatabaseHas('prescription_items', [
        'product_id' => $product->id,
        'medicine_name_raw' => 'OmParacetamol 500mg',
    ]);
});

test('doctor can list prescriptions', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/doctor/prescriptions');

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);
});
