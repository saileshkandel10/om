<?php

use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function rewardUser(): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('user can view their reward wallet', function () {
    [$user, $token] = rewardUser();

    RewardWallet::factory()->create([
        'user_id' => $user->id,
        'total_points' => 500,
        'total_bonus' => 100,
        'total_royalty' => 50,
    ]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/rewards/wallet');

    $response->assertOk()
        ->assertJsonPath('wallet.total_points', '500.00')
        ->assertJsonPath('wallet.total_bonus', '100.00');
});

test('wallet is auto-created if missing', function () {
    [$user, $token] = rewardUser();

    $response = $this->withToken($token)
        ->getJson('/api/v1/rewards/wallet');

    $response->assertOk()
        ->assertJsonPath('wallet.total_points', '0.00');

    $this->assertDatabaseHas('reward_wallets', ['user_id' => $user->id]);
});

test('user can view reward transactions', function () {
    [$user, $token] = rewardUser();

    RewardTransaction::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/rewards/transactions');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can request reward redemption and receives unique lucky draw code', function () {
    [$user, $token] = rewardUser();

    RewardWallet::factory()->create([
        'user_id' => $user->id,
        'total_points' => 1000,
    ]);

    $response = $this->withToken($token)
        ->postJson('/api/v1/rewards/redeem', [
            'points_used' => 500,
            'redemption_type' => 'cash',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'redemption_code',
            'lucky_draw_code',
            'redemption' => [
                'id',
                'redemption_code',
                'lucky_draw_code',
                'points_used',
                'status',
            ],
        ]);

    $code = $response->json('redemption_code');
    expect($code)->toStartWith('RDM-');

    $this->assertDatabaseHas('reward_redemptions', [
        'user_id' => $user->id,
        'redemption_code' => $code,
        'points_used' => 500,
    ]);
});

test('user cannot redeem more points than they have', function () {
    [$user, $token] = rewardUser();

    RewardWallet::factory()->create([
        'user_id' => $user->id,
        'total_points' => 100,
    ]);

    $response = $this->withToken($token)
        ->postJson('/api/v1/rewards/redeem', [
            'points_used' => 500,
            'redemption_type' => 'cash',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Insufficient points for redemption.');
});
