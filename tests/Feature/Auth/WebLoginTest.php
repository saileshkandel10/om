<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('OM Connect')
        ->assertSee('Email Address');
});

test('admin user redirects to admin dashboard upon login', function () {
    $role = Role::create(['name' => 'admin']);
    $user = User::factory()->create([
        'email' => 'admin@omconnect.com',
        'password' => Hash::make('Password123!'),
    ]);
    $user->roles()->attach($role->id);

    $response = $this->post('/login', [
        'email' => 'admin@omconnect.com',
        'password' => 'Password123!',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect();

    $dashboardResponse = $this->actingAs($user)->get('/dashboard');
    $dashboardResponse->assertRedirect(route('admin.dashboard'));
});

test('patient user redirects to patient dashboard upon login', function () {
    $role = Role::create(['name' => 'customer']);
    $user = User::factory()->create([
        'email' => 'customer@omconnect.com',
        'password' => Hash::make('Password123!'),
    ]);
    $user->roles()->attach($role->id);

    $response = $this->post('/login', [
        'email' => 'customer@omconnect.com',
        'password' => 'Password123!',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect();

    $dashboardResponse = $this->actingAs($user)->get('/dashboard');
    $dashboardResponse->assertRedirect(route('patient.dashboard'));
});

test('patient user cannot access admin routes', function () {
    $role = Role::create(['name' => 'customer']);
    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertForbidden();
});

test('shareholder user redirects to shareholder dashboard', function () {
    $role = Role::create(['name' => 'shareholder']);
    $user = User::factory()->create([
        'email' => 'shareholder@omconnect.com',
        'password' => Hash::make('Password123!'),
    ]);
    $user->roles()->attach($role->id);

    $dashboardResponse = $this->actingAs($user)->get('/dashboard');
    $dashboardResponse->assertRedirect(route('shareholder.dashboard'));
});

test('doctor user redirects to doctor dashboard', function () {
    $role = Role::create(['name' => 'doctor']);
    $user = User::factory()->create([
        'email' => 'doctor@omconnect.com',
        'password' => Hash::make('Password123!'),
    ]);
    $user->roles()->attach($role->id);

    $dashboardResponse = $this->actingAs($user)->get('/dashboard');
    $dashboardResponse->assertRedirect(route('doctor.dashboard'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout from web session', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
