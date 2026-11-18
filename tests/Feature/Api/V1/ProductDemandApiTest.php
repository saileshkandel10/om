<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\NepalLocationSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(NepalLocationSeeder::class);

    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);
    Sanctum::actingAs($this->admin);
});

test('api can fetch product demand summary and trending products', function () {
    $response = $this->getJson(route('api.v1.reports.product-demand'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'summary' => [
                'total_orders',
                'total_units_sold',
                'unique_products_purchased',
                'total_customers',
                'repeat_customers',
                'average_order_value',
                'most_demanded_product',
                'top_purchasing_district',
                'total_revenue',
            ],
            'time_series_demand',
        ]);
});

test('api can fetch trending products and top districts', function () {
    $resTrend = $this->getJson(route('api.v1.reports.trending-products'));
    $resTrend->assertStatus(200)->assertJson(['success' => true]);

    $resDist = $this->getJson(route('api.v1.reports.top-districts'));
    $resDist->assertStatus(200)->assertJson(['success' => true]);

    $resProv = $this->getJson(route('api.v1.reports.province-demand'));
    $resProv->assertStatus(200)->assertJson(['success' => true]);
});

test('api can fetch customer behaviour and nepal district coverage', function () {
    $resBehav = $this->getJson(route('api.v1.reports.customer-behaviour'));
    $resBehav->assertStatus(200)->assertJson(['success' => true]);

    $resCov = $this->getJson(route('api.v1.reports.district-coverage'));
    $resCov->assertStatus(200)
        ->assertJsonPath('coverage.total_districts', 77);

    $resTime = $this->getJson(route('api.v1.reports.purchase-time-analysis'));
    $resTime->assertStatus(200)->assertJson(['success' => true]);
});
