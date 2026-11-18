<?php

use App\Models\IllnessCategory;
use App\Models\PatientIllness;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\PatientIllnessService;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);
});

test('admin can access illness categories report page', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.reports.illness-categories'));

    $response->assertStatus(200);
    $response->assertSee('Total Unique Patients');
    $response->assertSee('Total Illness Records');
    $response->assertSee('Illness Category');
});

test('patient with multiple illness categories appears correctly under each category report', function () {
    $gastric = IllnessCategory::firstOrCreate(['name' => 'Gastric']);
    $diabetes = IllnessCategory::firstOrCreate(['name' => 'Diabetes']);
    $hypertension = IllnessCategory::firstOrCreate(['name' => 'Hypertension']);

    $patient = User::factory()->create(['name' => 'Ram Sharma']);
    UserProfile::create(['user_id' => $patient->id, 'dob' => '1981-05-10', 'gender' => 'male', 'district' => 'Kathmandu']);

    PatientIllness::create(['user_id' => $patient->id, 'illness_category_id' => $gastric->id, 'status' => 'active', 'detected_at' => now()]);
    PatientIllness::create(['user_id' => $patient->id, 'illness_category_id' => $diabetes->id, 'status' => 'active', 'detected_at' => now()]);
    PatientIllness::create(['user_id' => $patient->id, 'illness_category_id' => $hypertension->id, 'status' => 'active', 'detected_at' => now()]);

    // 1. Filter by Gastric -> Patient must appear
    $resGastric = $this->actingAs($this->admin)->get(route('admin.reports.illness-categories', ['illness_category_id' => $gastric->id]));
    $resGastric->assertStatus(200);
    $resGastric->assertSee('Ram Sharma');

    // 2. Filter by Diabetes -> Patient must also appear
    $resDiabetes = $this->actingAs($this->admin)->get(route('admin.reports.illness-categories', ['illness_category_id' => $diabetes->id]));
    $resDiabetes->assertStatus(200);
    $resDiabetes->assertSee('Ram Sharma');

    // 3. Filter by Hypertension -> Patient must also appear
    $resHypertension = $this->actingAs($this->admin)->get(route('admin.reports.illness-categories', ['illness_category_id' => $hypertension->id]));
    $resHypertension->assertStatus(200);
    $resHypertension->assertSee('Ram Sharma');
});

test('multi category match supports ANY and ALL logic without duplicate patient counting', function () {
    $gastric = IllnessCategory::firstOrCreate(['name' => 'Gastric']);
    $diabetes = IllnessCategory::firstOrCreate(['name' => 'Diabetes']);

    $p1 = User::factory()->create(['name' => 'Both Conditions Patient']);
    UserProfile::create(['user_id' => $p1->id, 'gender' => 'male']);
    PatientIllness::create(['user_id' => $p1->id, 'illness_category_id' => $gastric->id, 'status' => 'active', 'detected_at' => now()]);
    PatientIllness::create(['user_id' => $p1->id, 'illness_category_id' => $diabetes->id, 'status' => 'active', 'detected_at' => now()]);

    $p2 = User::factory()->create(['name' => 'Gastric Only Patient']);
    UserProfile::create(['user_id' => $p2->id, 'gender' => 'female']);
    PatientIllness::create(['user_id' => $p2->id, 'illness_category_id' => $gastric->id, 'status' => 'active', 'detected_at' => now()]);

    $service = app(PatientIllnessService::class);

    // ANY logic: p1 and p2 both returned
    $anyData = $service->getReportData([
        'illness_categories' => [$gastric->id, $diabetes->id],
        'match_type' => 'any',
    ]);
    $anyNames = collect($anyData['patient_rows'])->pluck('name');
    expect($anyNames)->toContain('Both Conditions Patient');
    expect($anyNames)->toContain('Gastric Only Patient');
    expect($anyData['total_unique_patients'])->toBeGreaterThanOrEqual(2);

    // ALL logic: only p1 returned
    $allData = $service->getReportData([
        'illness_categories' => [$gastric->id, $diabetes->id],
        'match_type' => 'all',
    ]);
    $allNames = collect($allData['patient_rows'])->pluck('name');
    expect($allNames)->toContain('Both Conditions Patient');
    expect($allNames)->not->toContain('Gastric Only Patient');
});

test('bill containing categorized product auto-flags patient with that illness', function () {
    $gastric = IllnessCategory::firstOrCreate(['name' => 'Gastric']);
    $corex = Product::updateOrCreate(
        ['brand_name' => 'Corex Cough Syrup'],
        ['sku' => 'TEST-COR-1', 'illness_category_id' => $gastric->id, 'is_active' => true]
    );

    $customer = User::factory()->create(['name' => 'Sailesh Test']);
    UserProfile::create(['user_id' => $customer->id, 'gender' => 'male']);

    $bill = PurchaseBill::create([
        'user_id' => $customer->id,
        'invoice_number' => '121343202-TEST',
        'invoice_date' => now(),
        'total_amount' => 500,
        'ocr_status' => 'completed',
        'validation_status' => 'approved',
    ]);

    PurchaseBillItem::create([
        'purchase_bill_id' => $bill->id,
        'product_id' => $corex->id,
        'product_name_raw' => 'Corex Cough Syrup',
        'quantity' => 1,
        'amount' => 500,
    ]);

    $service = app(PatientIllnessService::class);
    $service->syncFromPurchaseBill($bill);

    expect(PatientIllness::where('user_id', $customer->id)->where('illness_category_id', $gastric->id)->exists())->toBeTrue();
});

test('admin can export report to excel and csv', function () {
    $responseExcel = $this->actingAs($this->admin)->get(route('admin.reports.illness-categories.export', ['format' => 'xlsx', 'export_type' => 'detailed']));
    $responseExcel->assertStatus(200);
    $responseExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $responseCsv = $this->actingAs($this->admin)->get(route('admin.reports.illness-categories.export', ['format' => 'csv', 'export_type' => 'summary']));
    $responseCsv->assertStatus(200);
    $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
