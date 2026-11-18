<?php

use App\Models\Role;
use App\Models\ShareholderMaster;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);
});

test('admin can view shareholders index with pagination', function () {
    ShareholderMaster::factory()->count(15)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.shareholders.index', ['per_page' => 1000]));

    $response->assertOk();
    $response->assertViewHas('shareholders');
    $response->assertViewHas('perPage', 1000);
});

test('admin can download sample xlsx and csv template', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.shareholders.sample', ['format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-disposition', 'attachment; filename=shareholders_template.xlsx');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.shareholders.sample', ['format' => 'csv']));

    $responseCsv->assertOk();
    $responseCsv->assertHeader('content-disposition', 'attachment; filename=shareholders_template.csv');
});

test('admin can import shareholders from csv file and filter duplicates', function () {
    // Create an existing shareholder in DB
    ShareholderMaster::create([
        'boid' => '1301020000000001',
        'shareholder_name' => 'Existing Holder',
        'mobile' => '9800000000',
        'status' => 'active',
    ]);

    $tempCsv = tempnam(sys_get_temp_dir(), 'test_import_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tempCsv);
    $writer->addRow(Row::fromValues(['BOID', 'Shareholder Name', 'Mobile', 'Email', 'Status', 'Source Note']));
    $writer->addRow(Row::fromValues(['1301020000000001', 'Duplicate Name', '9811111111', 'dup@test.com', 'active', 'Batch 1']));
    $writer->addRow(Row::fromValues(['1301020000000002', 'New User One', '9822222222', 'user1@test.com', 'active', 'Batch 1']));
    $writer->addRow(Row::fromValues(['1301020000000003', 'New User Two', '9833333333', 'user2@test.com', 'active', 'Batch 1']));
    $writer->addRow(Row::fromValues(['1301020000000002', 'Repeated in file', '9844444444', 'dup2@test.com', 'active', 'Batch 1']));
    $writer->addRow(Row::fromValues(['1301020000000004', '', '9855555555', '', 'active', '']));
    $writer->close();

    $uploadedFile = new UploadedFile($tempCsv, 'shareholders.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.shareholders.import'), [
            'file' => $uploadedFile,
            'on_duplicate' => 'skip',
            'default_status' => 'active',
        ]);

    $response->assertRedirect(route('admin.shareholders.index'));
    $response->assertSessionHas('success');
    $response->assertSessionHas('import_stats', function ($stats) {
        return $stats['total_rows'] === 5
            && $stats['imported'] === 2
            && $stats['duplicates_skipped'] === 2
            && $stats['invalid_skipped'] === 1;
    });

    expect(ShareholderMaster::where('boid', '1301020000000002')->first()->shareholder_name)->toBe('New User One');
    expect(ShareholderMaster::where('boid', '1301020000000003')->first()->shareholder_name)->toBe('New User Two');
    expect(ShareholderMaster::where('boid', '1301020000000001')->first()->shareholder_name)->toBe('Existing Holder');

    @unlink($tempCsv);
});

test('admin can import shareholders and update existing records when selected', function () {
    ShareholderMaster::create([
        'boid' => '1301020000000099',
        'shareholder_name' => 'Old Name',
        'mobile' => '9800000000',
        'status' => 'pending',
    ]);

    $tempCsv = tempnam(sys_get_temp_dir(), 'test_update_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tempCsv);
    $writer->addRow(Row::fromValues(['BOID', 'Shareholder Name', 'Mobile', 'Email', 'Status']));
    $writer->addRow(Row::fromValues(['1301020000000099', 'Updated Name', '9899999999', 'updated@test.com', 'active']));
    $writer->close();

    $uploadedFile = new UploadedFile($tempCsv, 'shareholders.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.shareholders.import'), [
            'file' => $uploadedFile,
            'on_duplicate' => 'update',
        ]);

    $response->assertRedirect(route('admin.shareholders.index'));
    $response->assertSessionHas('import_stats', function ($stats) {
        return $stats['updated'] === 1 && $stats['imported'] === 0;
    });

    $updated = ShareholderMaster::where('boid', '1301020000000099')->first();
    expect($updated->shareholder_name)->toBe('Updated Name');
    expect($updated->mobile)->toBe('9899999999');
    expect($updated->email)->toBe('updated@test.com');
    expect($updated->status)->toBe('active');

    @unlink($tempCsv);
});

test('admin can import shareholders from xlsx file', function () {
    $tempXlsx = tempnam(sys_get_temp_dir(), 'test_xlsx_').'.xlsx';
    $writer = new XlsxWriter;
    $writer->openToFile($tempXlsx);
    $writer->addRow(Row::fromValues(['BOID', 'Shareholder Name', 'Mobile', 'Email', 'Status']));
    $writer->addRow(Row::fromValues(['1301020000000777', 'XLSX Shareholder', '9877777777', 'xlsx@test.com', 'active']));
    $writer->close();

    $uploadedFile = new UploadedFile($tempXlsx, 'shareholders.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.shareholders.import'), [
            'file' => $uploadedFile,
            'on_duplicate' => 'skip',
        ]);

    $response->assertRedirect(route('admin.shareholders.index'));
    $response->assertSessionHas('import_stats', function ($stats) {
        return $stats['imported'] === 1;
    });

    expect(ShareholderMaster::where('boid', '1301020000000777')->exists())->toBeTrue();

    @unlink($tempXlsx);
});

test('admin can export all shareholders as xlsx and csv', function () {
    ShareholderMaster::factory()->count(10)->create();

    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.shareholders.export', ['scope' => 'all', 'format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.shareholders.export', ['scope' => 'all', 'format' => 'csv']));

    $responseCsv->assertOk();
    $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('admin can export current page shareholders only', function () {
    ShareholderMaster::factory()->count(10)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.shareholders.export', [
            'scope' => 'current',
            'format' => 'csv',
            'page' => 1,
            'per_page' => 5,
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
