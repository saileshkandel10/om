<?php

use App\Models\IllnessCategory;
use App\Models\OtherProduct;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use App\Services\OtherProductMappingService;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);

    $this->gastricCat = IllnessCategory::firstOrCreate(
        ['name' => 'Gastric & Acid Peptic Disorders'],
        ['description' => 'Acidity, gastritis, and peptic ulcer diseases', 'status' => 'active']
    );

    $this->pharmaCat = ProductCategory::firstOrCreate(
        ['name' => 'Pharmaceuticals'],
        ['description' => 'Prescription and OTC medications']
    );
});

test('admin can view other products index with tabs and filters', function () {
    OtherProduct::create([
        'brand_name' => 'Pantocid 40',
        'generic_name' => 'Pantoprazole',
        'category_id' => $this->pharmaCat->id,
        'illness_category_id' => $this->gastricCat->id,
        'status' => 'active',
        'is_verified' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.other-products.index'));

    $response->assertOk();
    $response->assertViewHas('products');
    $response->assertViewHas('unmappedProducts');
    $response->assertViewHas('stats');
    $response->assertSee('Pantocid 40');
});

test('admin can create a new other product manually', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.other-products.store'), [
            'brand_name' => 'Glycomet 500',
            'generic_name' => 'Metformin',
            'category_id' => $this->pharmaCat->id,
            'illness_category_id' => $this->gastricCat->id,
            'description' => 'Market antidiabetic drug',
            'status' => 'active',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('other_products', [
        'brand_name' => 'Glycomet 500',
        'generic_name' => 'Metformin',
        'status' => 'active',
        'is_verified' => true,
    ]);
});

test('admin can edit and update other product', function () {
    $product = OtherProduct::create([
        'brand_name' => 'Old Brand',
        'generic_name' => 'Old Generic',
        'status' => 'unmapped',
        'is_verified' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.other-products.update', $product), [
            'brand_name' => 'Updated Brand 20',
            'generic_name' => 'Updated Generic',
            'category_id' => $this->pharmaCat->id,
            'illness_category_id' => $this->gastricCat->id,
            'description' => 'Updated description',
            'status' => 'active',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('other_products', [
        'id' => $product->id,
        'brand_name' => 'Updated Brand 20',
        'status' => 'active',
        'is_verified' => true,
    ]);
});

test('admin can delete other product', function () {
    $product = OtherProduct::create([
        'brand_name' => 'Delete Me 10',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.other-products.destroy', $product));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('other_products', [
        'id' => $product->id,
    ]);
});

test('admin can download sample template in xlsx and csv', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.other-products.sample-template', ['format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-disposition', 'attachment; filename=other_products_sample_template.xlsx');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.other-products.sample-template', ['format' => 'csv']));

    $responseCsv->assertOk();
    $responseCsv->assertHeader('content-disposition', 'attachment; filename=other_products_sample_template.csv');
});

test('admin can bulk import other products from csv file', function () {
    $tempCsv = tempnam(sys_get_temp_dir(), 'test_other_prod_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tempCsv);
    $writer->addRow(Row::fromValues(['Brand Name', 'Generic Name', 'Category', 'Illness Category', 'Description']));
    $writer->addRow(Row::fromValues(['Pantocid 40', 'Pantoprazole', 'Pharmaceuticals', 'Gastric & Acid Peptic Disorders', 'Market antacid']));
    $writer->addRow(Row::fromValues(['Pan 20', 'Pantoprazole', 'Pharmaceuticals', 'Gastric & Acid Peptic Disorders', 'Acidity tablet']));
    $writer->addRow(Row::fromValues(['', 'No Brand Item', 'Pharmaceuticals', '', ''])); // invalid row
    $writer->close();

    $uploadedFile = new UploadedFile($tempCsv, 'other_products.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.other-products.import'), [
            'file' => $uploadedFile,
            'on_duplicate' => 'skip',
            'default_status' => 'active',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('other_products', [
        'brand_name' => 'Pantocid 40',
        'generic_name' => 'Pantoprazole',
        'status' => 'active',
        'is_verified' => true,
    ]);

    $this->assertDatabaseHas('other_products', [
        'brand_name' => 'Pan 20',
        'generic_name' => 'Pantoprazole',
    ]);
});

test('suggestion engine suggests gastric condition for pantoprazole brand', function () {
    $mappingService = app(OtherProductMappingService::class);
    $suggestions = $mappingService->suggestIllnessCategories('Pantocid 40mg', 'Pantoprazole', 3);

    expect($suggestions)->not->toBeEmpty();
    expect($suggestions[0]['illness_category_id'])->toBe($this->gastricCat->id);
    expect($suggestions[0]['confidence_score'])->toBeGreaterThanOrEqual(70.0);
});

test('admin can map single unmapped product and batch map multiple products', function () {
    $p1 = OtherProduct::create([
        'brand_name' => 'Omee 20',
        'generic_name' => 'Omeprazole',
        'status' => 'unmapped',
        'is_verified' => false,
    ]);

    $p2 = OtherProduct::create([
        'brand_name' => 'Razo 20',
        'generic_name' => 'Rabeprazole',
        'status' => 'unmapped',
        'is_verified' => false,
    ]);

    // Single map
    $responseSingle = $this->actingAs($this->admin)
        ->post(route('admin.other-products.map', $p1), [
            'illness_category_id' => $this->gastricCat->id,
        ]);

    $responseSingle->assertRedirect();
    expect($p1->fresh()->status)->toBe('active');
    expect($p1->fresh()->is_verified)->toBeTrue();
    expect($p1->fresh()->illness_category_id)->toBe($this->gastricCat->id);

    // Batch map
    $responseBatch = $this->actingAs($this->admin)
        ->post(route('admin.other-products.batch-map'), [
            'ids' => [$p2->id],
            'illness_category_id' => $this->gastricCat->id,
        ]);

    $responseBatch->assertRedirect();
    expect($p2->fresh()->status)->toBe('active');
    expect($p2->fresh()->is_verified)->toBeTrue();
});

test('admin can get real-time ajax suggestions for brand query', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.other-products.suggestions', [
            'brand_name' => 'Pantocid DSR',
            'generic_name' => 'Pantoprazole + Domperidone',
        ]));

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'suggestions' => [
            '*' => ['illness_category_id', 'illness_category_name', 'confidence_score', 'matched_by'],
        ],
    ]);
});
