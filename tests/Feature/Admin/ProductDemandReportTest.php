<?php

use App\Models\District;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Province;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductDemandAnalyticsService;
use Carbon\Carbon;
use Database\Seeders\NepalLocationSeeder;

beforeEach(function () {
    $this->seed(NepalLocationSeeder::class);

    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);
});

test('all 77 districts and 7 provinces of nepal exist in master database', function () {
    expect(Province::count())->toBe(7);
    expect(District::count())->toBe(77);

    $bagmati = Province::where('code', 'P3')->first();
    expect($bagmati)->not->toBeNull();
    expect($bagmati->districts()->count())->toBe(13);

    $ktm = District::where('name', 'Kathmandu')->first();
    expect($ktm)->not->toBeNull();
    expect($ktm->province_id)->toBe($bagmati->id);
});

test('admin can access product demand report page with summary metrics', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.reports.product-demand'));

    $response->assertStatus(200);
    $response->assertSee('Product Demand');
    $response->assertSee('Total Orders');
    $response->assertSee('Total Products Sold');
    $response->assertSee('Unique Products');
    $response->assertSee('Repeat Customers');
});

test('product demand report accurately calculates trending products and growth', function () {
    $cat = ProductCategory::firstOrCreate(['name' => 'Pharmaceuticals']);
    $prodA = Product::create(['brand_name' => 'Test Product Alpha', 'sku' => 'TEST-A', 'product_category_id' => $cat->id, 'mrp' => 100, 'is_active' => true]);
    $prodB = Product::create(['brand_name' => 'Test Product Beta', 'sku' => 'TEST-B', 'product_category_id' => $cat->id, 'mrp' => 200, 'is_active' => true]);

    $ktm = District::where('name', 'Kathmandu')->first();
    $cust = User::factory()->create(['name' => 'Test Customer']);

    $billCurr = PurchaseBill::create([
        'user_id' => $cust->id,
        'district_id' => $ktm->id,
        'province_id' => $ktm->province_id,
        'invoice_date' => Carbon::now()->toDateString(),
        'total_amount' => 500,
        'order_status' => 'delivered',
        'validation_status' => 'approved',
    ]);
    PurchaseBillItem::create(['purchase_bill_id' => $billCurr->id, 'product_id' => $prodA->id, 'product_name_raw' => 'Test Product Alpha', 'quantity' => 10, 'amount' => 1000]);
    PurchaseBillItem::create(['purchase_bill_id' => $billCurr->id, 'product_id' => $prodB->id, 'product_name_raw' => 'Test Product Beta', 'quantity' => 5, 'amount' => 1000]);

    $service = app(ProductDemandAnalyticsService::class);
    $report = $service->getDemandReportData(['date_preset' => 'last_30_days']);

    $trending = collect($report['trending_products']);
    $alpha = $trending->firstWhere('name', 'Test Product Alpha');
    expect($alpha)->not->toBeNull();
    expect($alpha['units_sold'])->toBe(10.0);
    expect($alpha['orders_count'])->toBe(1);
    expect($alpha['demand_score'])->toBeGreaterThan(0);
});

test('geographic district and province demand filtering works correctly', function () {
    $ktm = District::where('name', 'Kathmandu')->first();
    $jhapa = District::where('name', 'Jhapa')->first();

    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    $b1 = PurchaseBill::create([
        'user_id' => $u1->id,
        'district_id' => $ktm->id,
        'province_id' => $ktm->province_id,
        'invoice_date' => Carbon::now()->toDateString(),
        'total_amount' => 1200,
        'order_status' => 'delivered',
        'validation_status' => 'approved',
    ]);

    $b2 = PurchaseBill::create([
        'user_id' => $u2->id,
        'district_id' => $jhapa->id,
        'province_id' => $jhapa->province_id,
        'invoice_date' => Carbon::now()->toDateString(),
        'total_amount' => 800,
        'order_status' => 'delivered',
        'validation_status' => 'approved',
    ]);

    $service = app(ProductDemandAnalyticsService::class);

    $ktmData = $service->getDemandReportData(['district_id' => $ktm->id]);
    expect($ktmData['summary']['total_orders'])->toBe(1);
    expect($ktmData['summary']['total_revenue'])->toBe(1200.0);

    $koshiData = $service->getDemandReportData(['province_id' => $jhapa->province_id]);
    expect($koshiData['summary']['total_orders'])->toBe(1);
    expect($koshiData['summary']['total_revenue'])->toBe(800.0);
});

test('repeat customer and purchase frequency calculation is accurate', function () {
    $uRepeat = User::factory()->create(['name' => 'Loyal Customer']);
    $uOnce = User::factory()->create(['name' => 'Single Purchase Customer']);

    $ktm = District::where('name', 'Kathmandu')->first();

    for ($i = 0; $i < 3; $i++) {
        PurchaseBill::create([
            'user_id' => $uRepeat->id,
            'district_id' => $ktm->id,
            'province_id' => $ktm->province_id,
            'invoice_date' => Carbon::now()->subDays($i * 2)->toDateString(),
            'total_amount' => 500,
            'order_status' => 'delivered',
            'validation_status' => 'approved',
        ]);
    }

    PurchaseBill::create([
        'user_id' => $uOnce->id,
        'district_id' => $ktm->id,
        'province_id' => $ktm->province_id,
        'invoice_date' => Carbon::now()->toDateString(),
        'total_amount' => 300,
        'order_status' => 'delivered',
        'validation_status' => 'approved',
    ]);

    $service = app(ProductDemandAnalyticsService::class);
    $data = $service->getDemandReportData(['date_preset' => 'last_30_days']);

    expect($data['summary']['total_customers'])->toBeGreaterThanOrEqual(2);
    expect($data['summary']['repeat_customers'])->toBeGreaterThanOrEqual(1);
    expect($data['customer_behaviour']['frequency_brackets']['3'])->toBeGreaterThanOrEqual(1);
});

test('district coverage identifies both covered and zero demand districts out of 77', function () {
    $service = app(ProductDemandAnalyticsService::class);
    $data = $service->getDemandReportData(['date_preset' => 'last_30_days']);

    expect($data['district_coverage']['total_districts'])->toBe(77);
    expect($data['district_coverage']['covered_districts_count'] + $data['district_coverage']['zero_demand_districts_count'])->toBe(77);
    expect($data['district_coverage']['coverage_percentage'])->toBeGreaterThanOrEqual(0);
});

test('admin can export demand report to excel and csv', function () {
    $resExcel = $this->actingAs($this->admin)->get(route('admin.reports.product-demand.export', ['format' => 'xlsx', 'export_type' => 'product_demand']));
    $resExcel->assertStatus(200);
    $resExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $resCsv = $this->actingAs($this->admin)->get(route('admin.reports.product-demand.export', ['format' => 'csv', 'export_type' => 'district_demand']));
    $resCsv->assertStatus(200);
    $resCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('admin can view and update nepal districts settings', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.settings.locations.districts'));
    $response->assertStatus(200);
    $response->assertSee('Nepal 77 Districts');
    $response->assertSee('Kathmandu');

    $ktm = District::where('name', 'Kathmandu')->first();
    $updateRes = $this->actingAs($this->admin)->put(route('admin.settings.locations.districts.update', $ktm), [
        'code' => 'KTM-MOD',
        'status' => 'active',
    ]);

    $updateRes->assertSessionHas('success');
    expect($ktm->fresh()->code)->toBe('KTM-MOD');
});

test('admin can map an unmapped bill to a district via popup endpoint and action', function () {
    $ktm = District::where('name', 'Kathmandu')->first();
    $user = User::factory()->create();
    $bill = PurchaseBill::create([
        'user_id' => $user->id,
        'invoice_number' => 'UNMAPPED-101',
        'district_id' => null,
        'total_amount' => 500,
        'order_status' => 'delivered',
        'validation_status' => 'approved',
    ]);

    // Modal view
    $modalRes = $this->actingAs($this->admin)->get(route('admin.settings.locations.bills.modal', $bill));
    $modalRes->assertStatus(200);
    $modalRes->assertSee('UNMAPPED-101');

    // Submit mapping
    $mapRes = $this->actingAs($this->admin)->post(route('admin.settings.locations.bills.map', $bill), [
        'district_id' => $ktm->id,
        'notes' => 'Verified location',
    ]);

    $mapRes->assertSessionHas('success');
    expect($bill->fresh()->district_id)->toBe($ktm->id);
    expect($bill->fresh()->location_status)->toBe('mapped');
});

test('admin can skip a bill and perform bulk mapping and bulk skip actions', function () {
    $ktm = District::where('name', 'Kathmandu')->first();
    $user = User::factory()->create();

    $b1 = PurchaseBill::create(['user_id' => $user->id, 'invoice_number' => 'UNMAPPED-B1', 'district_id' => null]);
    $b2 = PurchaseBill::create(['user_id' => $user->id, 'invoice_number' => 'UNMAPPED-B2', 'district_id' => null]);
    $b3 = PurchaseBill::create(['user_id' => $user->id, 'invoice_number' => 'UNMAPPED-B3', 'district_id' => null]);

    // Single skip
    $this->actingAs($this->admin)->post(route('admin.settings.locations.bills.skip', $b1))->assertSessionHas('success');
    expect($b1->fresh()->location_status)->toBe('skipped');

    // Bulk assign b2 and b3 to Kathmandu
    $bulkRes = $this->actingAs($this->admin)->post(route('admin.settings.locations.bills.bulk'), [
        'bulk_action' => 'assign_district',
        'bulk_district_id' => $ktm->id,
        'bill_ids' => [$b2->id, $b3->id],
    ]);
    $bulkRes->assertSessionHas('success');
    expect($b2->fresh()->district_id)->toBe($ktm->id);
    expect($b3->fresh()->district_id)->toBe($ktm->id);
});
