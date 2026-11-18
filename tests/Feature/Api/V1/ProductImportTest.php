<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;

uses(RefreshDatabase::class);

function createAdminForProductImport(): User
{
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    $admin->roles()->syncWithoutDetaching([$role->id]);
    return $admin;
}

test('admin can bulk import products from valid excel file', function () {
    $admin = createAdminForProductImport();
    Storage::fake('public');

    // Create a dummy Excel file using OpenSpout
    $path = tempnam(sys_get_temp_dir(), 'test_import') . '.xlsx';
    $writer = new XlsxWriter();
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues([
        'SKU', 'Brand Name', 'Generic Name', 
        'Category', 'MRP', 'Status'
    ]));
    $writer->addRow(Row::fromValues([
        'SKU001', 'Paracetamol 500mg', 'Paracetamol', 
        'Painkiller', 10.00, 'active'
    ]));
    $writer->close();

    $file = new UploadedFile($path, 'products.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($admin)
        ->post(route('admin.products.import'), [
            'file' => $file,
            'on_duplicate' => 'update',
            'default_status' => 'active',
            'auto_create_categories' => 1,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', ['sku' => 'SKU001', 'brand_name' => 'Paracetamol 500mg']);
    $this->assertDatabaseHas('product_categories', ['name' => 'Painkiller']);
});
