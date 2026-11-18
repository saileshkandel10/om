<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function notifUser(): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('user can list their notifications', function () {
    [$user, $token] = notifUser();

    Notification::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/notifications');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can get unread count', function () {
    [$user, $token] = notifUser();

    Notification::factory()->count(5)->create(['user_id' => $user->id, 'read_at' => null]);
    Notification::factory()->count(2)->read()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/notifications/unread-count');

    $response->assertOk()
        ->assertJsonPath('unread_count', 5);
});

test('user can mark a notification as read', function () {
    [$user, $token] = notifUser();

    $notification = Notification::factory()->create(['user_id' => $user->id, 'read_at' => null]);

    $response = $this->withToken($token)
        ->putJson("/api/v1/notifications/{$notification->id}/read");

    $response->assertOk();

    $this->assertNotNull($notification->fresh()->read_at);
});

test('user can mark all notifications as read', function () {
    [$user, $token] = notifUser();

    Notification::factory()->count(5)->create(['user_id' => $user->id, 'read_at' => null]);

    $response = $this->withToken($token)
        ->putJson('/api/v1/notifications/read-all');

    $response->assertOk();

    expect(Notification::where('user_id', $user->id)->whereNull('read_at')->count())->toBe(0);
});
