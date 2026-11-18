<?php

use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DemoUserSeeder::class);
});

test('admin can login and redirect to admin dashboard', function () {
    $response = $this->post(route('login'), [
        'email' => 'admin@omconnect.com',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticated();
});

test('doctor can login and redirect to doctor dashboard', function () {
    $response = $this->post(route('login'), [
        'email' => 'doctor@omconnect.com',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('doctor.dashboard'));
    $this->assertAuthenticated();
});

test('pharmacy can login and redirect to pharmacy dashboard', function () {
    $response = $this->post(route('login'), [
        'email' => 'pharmacy@omconnect.com',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('pharmacy.dashboard'));
    $this->assertAuthenticated();
});

test('shareholder can login and redirect to shareholder dashboard', function () {
    $response = $this->post(route('login'), [
        'email' => 'shareholder@omconnect.com',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('shareholder.dashboard'));
    $this->assertAuthenticated();
});

test('customer can login and redirect to patient dashboard', function () {
    $response = $this->post(route('login'), [
        'email' => 'customer@omconnect.com',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('patient.dashboard'));
    $this->assertAuthenticated();
});
