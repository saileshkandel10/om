<?php

use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function authenticatedUser(): array
{
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

test('authenticated user can list products', function () {
    [$user, $token] = authenticatedUser();

    Product::factory()->count(5)->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

test('products can be searched by brand name', function () {
    [$user, $token] = authenticatedUser();

    Product::factory()->create(['brand_name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'is_active' => true]);
    Product::factory()->create(['brand_name' => 'Amoxicillin 250mg', 'generic_name' => 'Amoxicillin', 'is_active' => true]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/products?search=Paracetamol');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.brand_name', 'Paracetamol 500mg');
});

test('products can be filtered by category', function () {
    [$user, $token] = authenticatedUser();

    $category = ProductCategory::factory()->create();
    Product::factory()->create(['product_category_id' => $category->id, 'is_active' => true]);
    Product::factory()->create(['is_active' => true]);

    $response = $this->withToken($token)
        ->getJson("/api/v1/products?category_id={$category->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('single product can be viewed with batches', function () {
    [$user, $token] = authenticatedUser();

    $product = Product::factory()->create(['is_active' => true]);

    $response = $this->withToken($token)
        ->getJson("/api/v1/products/{$product->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonStructure(['data' => ['id', 'brand_name', 'generic_name', 'mrp', 'category']]);
});

test('product categories can be listed', function () {
    [$user, $token] = authenticatedUser();

    ProductCategory::factory()->count(3)->create();

    $response = $this->withToken($token)
        ->getJson('/api/v1/products/categories');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('illness categories can be listed', function () {
    [$user, $token] = authenticatedUser();

    IllnessCategory::factory()->count(4)->create();

    $response = $this->withToken($token)
        ->getJson('/api/v1/products/illness-categories');

    $response->assertOk()
        ->assertJsonCount(4, 'data');
});
