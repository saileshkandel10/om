<?php

use App\Models\PurchaseBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function billUser(): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('user can list their own purchase bills', function () {
    [$user, $token] = billUser();
    $otherUser = User::factory()->create();

    PurchaseBill::factory()->count(3)->create(['user_id' => $user->id]);
    PurchaseBill::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/bills');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can upload a receipt and create a bill', function () {
    [$user, $token] = billUser();

    Storage::fake('public');
    Queue::fake();

    $response = $this->withToken($token)
        ->postJson('/api/v1/bills', [
            'receipt' => UploadedFile::fake()->image('receipt.jpg', 800, 600),
            'invoice_number' => 'INV-001',
            'total_amount' => 1500.50,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('message', 'Bill submitted successfully. It will be processed shortly.')
        ->assertJsonStructure(['bill' => ['id', 'invoice_number', 'total_amount', 'ocr_status']]);

    $this->assertDatabaseHas('purchase_bills', [
        'user_id' => $user->id,
        'invoice_number' => 'INV-001',
        'ocr_status' => 'pending',
    ]);
});

test('user can view their own bill details', function () {
    [$user, $token] = billUser();

    $bill = PurchaseBill::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson("/api/v1/bills/{$bill->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $bill->id);
});

test('user cannot view another user bill', function () {
    [$user, $token] = billUser();
    $otherUser = User::factory()->create();

    $bill = PurchaseBill::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->withToken($token)
        ->getJson("/api/v1/bills/{$bill->id}");

    $response->assertForbidden();
});

test('bills can be filtered by ocr status', function () {
    [$user, $token] = billUser();

    PurchaseBill::factory()->create(['user_id' => $user->id, 'ocr_status' => 'completed']);
    PurchaseBill::factory()->create(['user_id' => $user->id, 'ocr_status' => 'pending']);
    PurchaseBill::factory()->create(['user_id' => $user->id, 'ocr_status' => 'failed']);

    $response = $this->withToken($token)
        ->getJson('/api/v1/bills?ocr_status=completed');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('receipt is required to create a bill', function () {
    [$user, $token] = billUser();

    $response = $this->withToken($token)
        ->postJson('/api/v1/bills', [
            'invoice_number' => 'INV-001',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['receipt']);
});
