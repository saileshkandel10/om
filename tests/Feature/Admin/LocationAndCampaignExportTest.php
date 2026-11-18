<?php

use App\Models\Campaign;
use App\Models\District;
use App\Models\Notice;
use App\Models\Province;
use App\Models\PurchaseBill;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->adminRole = Role::firstOrCreate(['name' => 'admin'], [
        'display_name' => 'Super Administrator',
        'is_system' => true,
    ]);

    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole);

    $this->province = Province::firstOrCreate(
        ['name' => 'Bagmati Province'],
        ['code' => 'P3', 'status' => 'active']
    );

    $this->district = District::firstOrCreate(
        ['name' => 'Kathmandu'],
        ['province_id' => $this->province->id, 'code' => 'KTM', 'status' => 'active']
    );
});

test('admin can export 77 districts in xlsx and csv', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.settings.locations.districts.export', ['type' => 'districts', 'format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.settings.locations.districts.export', ['type' => 'districts', 'format' => 'csv']));

    $responseCsv->assertOk();
});

test('admin can export unmapped location bills in xlsx and csv', function () {
    PurchaseBill::factory()->create([
        'user_id' => $this->admin->id,
        'district_id' => null,
    ]);

    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.settings.locations.districts.export', ['type' => 'unmapped_bills', 'format' => 'xlsx']));

    $responseXlsx->assertOk();

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.settings.locations.districts.export', ['type' => 'unmapped_bills', 'format' => 'csv']));

    $responseCsv->assertOk();
});

test('admin can export campaigns in xlsx and csv', function () {
    Campaign::create([
        'name' => 'Health Awareness 2026',
        'campaign_type' => 'awareness',
        'status' => 'active',
    ]);

    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.campaigns.export', ['format' => 'xlsx']));

    $responseXlsx->assertOk();

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.campaigns.export', ['format' => 'csv']));

    $responseCsv->assertOk();
});

test('admin can export notices in xlsx and csv', function () {
    Notice::create([
        'title' => 'Annual General Meeting 2026',
        'notice_type' => 'agm',
        'audience_type' => 'shareholder',
        'body' => 'AGM Announcement details...',
        'status' => 'published',
    ]);

    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.notices.export', ['format' => 'xlsx']));

    $responseXlsx->assertOk();

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.notices.export', ['format' => 'csv']));

    $responseCsv->assertOk();
});

test('campaign and notice permissions are available on roles', function () {
    $groups = Role::availablePermissionGroups();
    expect($groups)->toHaveKey('Campaigns & Communication');
    expect($groups['Campaigns & Communication'])->toHaveKey('campaigns.view');
    expect($groups['Campaigns & Communication'])->toHaveKey('notices.view');

    $adminRole = Role::where('name', 'admin')->first();
    expect($adminRole->hasPermission('campaigns.view'))->toBeTrue();
    expect($adminRole->hasPermission('notices.manage'))->toBeTrue();
});
