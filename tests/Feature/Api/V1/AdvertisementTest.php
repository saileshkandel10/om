<?php

use App\Models\Advertisement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createRoleUser(string $roleName): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $role = Role::firstOrCreate(['name' => $roleName], ['display_name' => ucfirst($roleName)]);
    $user->roles()->syncWithoutDetaching([$role->id]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('patient user only receives patient and all-user advertisements', function () {
    [$patient, $token] = createRoleUser('customer');

    // Create ads for different roles
    Advertisement::factory()->forPatient()->create(['title' => 'Patient Exclusive Offer']);
    Advertisement::factory()->forAll()->create(['title' => 'Global Health Campaign']);
    Advertisement::factory()->forDoctor()->create(['title' => 'Doctor CME Seminar']);
    Advertisement::factory()->forPharmacy()->create(['title' => 'Pharmacy Wholesale Discount']);
    Advertisement::factory()->forShareholder()->create(['title' => 'Shareholder AGM Notice']);

    $response = $this->withToken($token)
        ->getJson('/api/v1/advertisements');

    $response->assertOk()
        ->assertJsonCount(2, 'data');

    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toContain('Patient Exclusive Offer')
        ->and($titles)->toContain('Global Health Campaign')
        ->and($titles)->not->toContain('Doctor CME Seminar')
        ->and($titles)->not->toContain('Pharmacy Wholesale Discount')
        ->and($titles)->not->toContain('Shareholder AGM Notice');
});

test('doctor user only receives doctor and all-user advertisements', function () {
    [$doctor, $token] = createRoleUser('doctor');

    Advertisement::factory()->forDoctor()->create(['title' => 'Doctor CME Seminar']);
    Advertisement::factory()->forAll()->create(['title' => 'Global Health Campaign']);
    Advertisement::factory()->forPatient()->create(['title' => 'Patient Exclusive Offer']);
    Advertisement::factory()->forPharmacy()->create(['title' => 'Pharmacy Wholesale Discount']);

    $response = $this->withToken($token)
        ->getJson('/api/v1/advertisements');

    $response->assertOk()
        ->assertJsonCount(2, 'data');

    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toContain('Doctor CME Seminar')
        ->and($titles)->toContain('Global Health Campaign')
        ->and($titles)->not->toContain('Patient Exclusive Offer');
});

test('pharmacy user only receives pharmacy and all-user advertisements', function () {
    [$pharmacy, $token] = createRoleUser('pharmacy');

    Advertisement::factory()->forPharmacy()->create(['title' => 'Pharmacy Bulk Order Special']);
    Advertisement::factory()->forAll()->create(['title' => 'Global Announcement']);
    Advertisement::factory()->forDoctor()->create(['title' => 'Doctor Only Portal']);

    $response = $this->withToken($token)
        ->getJson('/api/v1/advertisements');

    $response->assertOk()
        ->assertJsonCount(2, 'data');

    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toContain('Pharmacy Bulk Order Special')
        ->and($titles)->toContain('Global Announcement')
        ->and($titles)->not->toContain('Doctor Only Portal');
});

test('shareholder user only receives shareholder and all-user advertisements', function () {
    [$shareholder, $token] = createRoleUser('shareholder');

    Advertisement::factory()->forShareholder()->create(['title' => 'Shareholder Dividend Report']);
    Advertisement::factory()->forAll()->create(['title' => 'Global Announcement']);
    Advertisement::factory()->forPatient()->create(['title' => 'Patient Discount']);

    $response = $this->withToken($token)
        ->getJson('/api/v1/advertisements');

    $response->assertOk()
        ->assertJsonCount(2, 'data');

    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toContain('Shareholder Dividend Report')
        ->and($titles)->toContain('Global Announcement')
        ->and($titles)->not->toContain('Patient Discount');
});

test('inactive and out-of-schedule advertisements are excluded', function () {
    [$user, $token] = createRoleUser('customer');

    // Inactive ad
    Advertisement::factory()->forAll()->create(['title' => 'Inactive Ad', 'is_active' => false]);

    // Expired ad
    Advertisement::factory()->forAll()->create([
        'title' => 'Expired Ad',
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDay(),
        'is_active' => true,
    ]);

    // Future ad
    Advertisement::factory()->forAll()->create([
        'title' => 'Future Ad',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(15),
        'is_active' => true,
    ]);

    // Active ad
    Advertisement::factory()->forAll()->create([
        'title' => 'Currently Active Ad',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(5),
        'is_active' => true,
    ]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/advertisements');

    $response->assertOk()
        ->assertJsonCount(1, 'data');

    expect($response->json('data.0.title'))->toBe('Currently Active Ad');
});

test('recording impressions and clicks increments analytics counters', function () {
    [$user, $token] = createRoleUser('customer');
    $ad = Advertisement::factory()->forAll()->create(['impressions_count' => 10, 'clicks_count' => 2]);

    // Record Impression
    $impRes = $this->withToken($token)
        ->postJson("/api/v1/advertisements/{$ad->id}/impression");

    $impRes->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('impressions_count', 11);

    expect($ad->fresh()->impressions_count)->toBe(11);

    // Record Click
    $clickRes = $this->withToken($token)
        ->postJson("/api/v1/advertisements/{$ad->id}/click");

    $clickRes->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('clicks_count', 3);

    expect($ad->fresh()->clicks_count)->toBe(3)
        ->and($ad->fresh()->ctr)->toBe(27.27);
});
