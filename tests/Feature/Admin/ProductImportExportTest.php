<?php

use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->syncWithoutDetaching([$adminRole->id]);

    $this->antibioticCat = ProductCategory::firstOrCreate(['name' => 'Antibiotics']);
    $this->infectionCat = IllnessCategory::firstOrCreate(['name' => 'Bacterial Infection']);
});

test('admin can view product list page with export and import buttons', function () {
    Product::create([
        'sku' => 'OM-AMX-500',
        'brand_name' => 'Omox 500mg',
        'generic_name' => 'Amoxicillin',
        'product_category_id' => $this->antibioticCat->id,
        'illness_category_id' => $this->infectionCat->id,
        'mrp' => 150.00,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

    $response->assertOk()
        ->assertSee('Company Product Catalogue')
        ->assertSee('Bulk Import')
        ->assertSee('Export Products')
        ->assertSee('Omox 500mg')
        ->assertSee('OM-AMX-500');
});

test('admin can download product sample template in csv format', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.products.sample-template', ['format' => 'csv']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('admin can preview product import file via ajax', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'prod_test_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tmpFile);

    $writer->addRow(Row::fromValues(['SKU', 'Brand Name', 'Generic Name', 'Category', 'MRP']));
    $writer->addRow(Row::fromValues(['OM-PAN-40', 'Ompan 40mg', 'Pantoprazole', 'Gastrointestinal', '120.00']));
    $writer->addRow(Row::fromValues(['', 'Invalid Row Without Name', '', '', '']));
    $writer->close();

    $uploaded = new UploadedFile($tmpFile, 'test_products.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->postJson(route('admin.products.preview-import'), [
            'file' => $uploaded,
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'rows',
            'total_previewed',
            'valid_count',
            'invalid_count',
            'total_estimated',
        ]);

    expect($response->json('valid_count'))->toBe(2);

    @unlink($tmpFile);
});

test('admin can bulk import products from csv with auto-created categories and update on duplicates', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'prod_import_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tmpFile);

    $writer->addRow(Row::fromValues([
        'SKU', 'Brand Name', 'Generic Name', 'Category', 'Illness Category', 'Strength', 'Dosage Form', 'Pack Size', 'MRP', 'Status',
    ]));

    $writer->addRow(Row::fromValues([
        'OM-TEST-1', 'Omcal D3', 'Calcium + Vitamin D3', 'Vitamins & Minerals', '', '500mg', 'Tablet', '30 Tabs', '250.00', 'Active',
    ]));

    $writer->addRow(Row::fromValues([
        'OM-TEST-2', 'Omcold Syrup', 'Paracetamol + Phenylephrine', 'Respiratory', '', '60ml', 'Syrup', '1 Bottle', '85.00', 'Active',
    ]));

    $writer->close();

    $uploaded = new UploadedFile($tmpFile, 'products.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.import'), [
            'file' => $uploaded,
            'on_duplicate' => 'update',
            'auto_create_categories' => 1,
            'default_status' => 'active',
        ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
        'sku' => 'OM-TEST-1',
        'brand_name' => 'Omcal D3',
        'generic_name' => 'Calcium + Vitamin D3',
        'mrp' => 250.00,
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('products', [
        'sku' => 'OM-TEST-2',
        'brand_name' => 'Omcold Syrup',
        'mrp' => 85.00,
    ]);

    // Check that new category "Vitamins & Minerals" was auto-created
    $this->assertDatabaseHas('product_categories', [
        'name' => 'Vitamins & Minerals',
    ]);

    @unlink($tmpFile);
});

test('admin can export own catalogue products to csv', function () {
    Product::create([
        'sku' => 'OM-EXP-01',
        'brand_name' => 'Exportable Medicine',
        'generic_name' => 'Molecule X',
        'product_category_id' => $this->antibioticCat->id,
        'mrp' => 99.00,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.products.export', ['format' => 'csv']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)
        ->toContain('Exportable Medicine')
        ->toContain('OM-EXP-01')
        ->toContain('99.00');
});
