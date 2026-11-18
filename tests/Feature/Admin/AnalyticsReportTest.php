<?php

use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->role = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->syncWithoutDetaching([$this->role->id]);
});

test('admin can view reports page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/analytics/reports');

    $response->assertOk()
        ->assertSee('Executive Reports')
        ->assertSee('Available Export Datasets');
});

test('admin can view sales analytics page and only company catalogue products are counted in sales metrics', function () {
    $category = ProductCategory::create(['name' => 'Antibiotics']);
    $companyProduct = Product::create([
        'sku' => 'OM-AMX-500',
        'brand_name' => 'Omox 500mg',
        'generic_name' => 'Amoxicillin',
        'product_category_id' => $category->id,
        'mrp' => 150.00,
        'is_active' => true,
    ]);

    $patient = User::factory()->create(['name' => 'Ram Bahadur']);
    $pharmacyUser = User::factory()->create();
    $pharmacy = Pharmacy::create([
        'user_id' => $pharmacyUser->id,
        'store_name' => 'Om City Pharmacy',
        'license_no' => 'PH-9988',
        'district' => 'Kathmandu',
        'status' => 'active',
    ]);

    // Scenario: Patient invoice has 4 medicines:
    // 3 items are from other brands (competitors, product_id is NULL)
    // 1 item is from our company catalogue (product_id = $companyProduct->id)
    $bill = PurchaseBill::create([
        'user_id' => $patient->id,
        'pharmacy_id' => $pharmacy->id,
        'invoice_number' => 'INV-2026-001',
        'invoice_date' => Carbon::now()->toDateString(),
        'total_amount' => 1420.00, // Total bill across all brands
        'validation_status' => 'approved',
        'order_status' => 'delivered',
    ]);

    // Competitor 1: Paracetamol (other brand) - Rs. 100
    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => null,
        'product_name_raw' => 'Competitor Cetamol 500mg',
        'quantity' => 10,
        'unit_price' => 10.00,
        'amount' => 100.00,
    ]);

    // Competitor 2: Cough Syrup (other brand) - Rs. 220
    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => null,
        'product_name_raw' => 'Competitor Cough Syrup 100ml',
        'quantity' => 1,
        'unit_price' => 220.00,
        'amount' => 220.00,
    ]);

    // Competitor 3: Vitamin C (other brand) - Rs. 800
    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => null,
        'product_name_raw' => 'Imported Vit-C 1000mg',
        'quantity' => 2,
        'unit_price' => 400.00,
        'amount' => 800.00,
    ]);

    // OUR COMPANY PRODUCT: Omox 500mg - 2 packs @ Rs. 150 = Rs. 300
    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => $companyProduct->id,
        'product_name_raw' => 'Omox 500mg (OM Pharma)',
        'quantity' => 2,
        'unit_price' => 150.00,
        'amount' => 300.00,
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/analytics/sales');

    $response->assertOk()
        ->assertViewIs('admin.analytics.sales')
        ->assertViewHas('totalCompanyRevenue', 300.0) // ONLY 300, not 1420
        ->assertViewHas('totalCompanyUnits', 2.0) // ONLY 2 units of Omox
        ->assertViewHas('totalCompanyInvoices', 1)
        ->assertViewHas('grossBillsAmount', 1420.0)
        ->assertSee('Company Product Sales Report')
        ->assertSee('Omox 500mg')
        ->assertSee('OM-AMX-500')
        ->assertSee('Rs 300.00')
        ->assertDontSee('Competitor Cetamol 500mg')
        ->assertDontSee('Imported Vit-C 1000mg');
});

test('admin can export sales report as csv with only company catalogue products', function () {
    $category = ProductCategory::create(['name' => 'Cardiac Care']);
    $companyProduct = Product::create([
        'sku' => 'OM-ATV-10',
        'brand_name' => 'Omtor 10mg',
        'generic_name' => 'Atorvastatin',
        'product_category_id' => $category->id,
        'mrp' => 250.00,
        'is_active' => true,
    ]);

    $patient = User::factory()->create(['name' => 'Hari Shrestha']);
    $bill = PurchaseBill::create([
        'user_id' => $patient->id,
        'invoice_number' => 'INV-2026-002',
        'invoice_date' => Carbon::now()->toDateString(),
        'total_amount' => 1500.00,
        'validation_status' => 'approved',
    ]);

    // Non-company item
    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => null,
        'product_name_raw' => 'Competitor Statin',
        'quantity' => 10,
        'unit_price' => 100.00,
        'amount' => 1000.00,
    ]);

    // Our company item
    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => $companyProduct->id,
        'product_name_raw' => 'Omtor 10mg',
        'quantity' => 2,
        'unit_price' => 250.00,
        'amount' => 500.00,
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/analytics/export?type=sales');

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)
        ->toContain('Omtor 10mg')
        ->toContain('OM-ATV-10')
        ->toContain('500')
        ->not->toContain('Competitor Statin');
});

test('admin can export rewards report as csv', function () {
    $response = $this->actingAs($this->admin)->get('/admin/analytics/export?type=rewards');

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
