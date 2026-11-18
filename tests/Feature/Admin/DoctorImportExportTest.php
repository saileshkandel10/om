<?php

use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);
});

test('admin can view doctors index with pagination and filter', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.doctors.index', ['per_page' => 50]));

    $response->assertOk();
    $response->assertViewHas('doctors');
    $response->assertViewHas('perPage', 50);
});

test('admin can download doctor sample xlsx and csv template', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.doctors.sample', ['format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-disposition', 'attachment; filename=doctors_template.xlsx');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.doctors.sample', ['format' => 'csv']));

    $responseCsv->assertOk();
    $responseCsv->assertHeader('content-disposition', 'attachment; filename=doctors_template.csv');
});

test('admin can import doctors from csv file and filter duplicates', function () {
    $existingUser = User::factory()->create(['name' => 'Dr. Existing User', 'email' => 'existing.dr@example.com']);
    Doctor::create([
        'user_id' => $existingUser->id,
        'registration_no' => 'NMC-99901',
        'specialty' => 'Cardiology',
        'hospital_clinic' => 'Old Clinic',
        'status' => 'active',
    ]);

    $tempCsv = tempnam(sys_get_temp_dir(), 'test_doctor_import_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tempCsv);
    $writer->addRow(Row::fromValues(['Registration No', 'Doctor Name', 'Specialty', 'Hospital / Clinic', 'Mobile', 'Email', 'Status']));
    $writer->addRow(Row::fromValues(['NMC-99901', 'Dr. Duplicate Entry', 'Cardiology', 'New Hospital', '9811111111', 'dup@test.com', 'active']));
    $writer->addRow(Row::fromValues(['NMC-99902', 'Dr. Suman Karki', 'Gastroenterology', 'Om Mega Hospital', '9822222222', 'suman@test.com', 'active']));
    $writer->addRow(Row::fromValues(['NMC-99903', 'Dr. Gita Thapa', 'Neurology', 'Bir Hospital', '9833333333', 'gita@test.com', 'active']));
    $writer->addRow(Row::fromValues(['NMC-99902', 'Dr. Suman Repeated', 'Gastroenterology', 'Om Mega Hospital', '9844444444', 'dup2@test.com', 'active']));
    $writer->addRow(Row::fromValues(['', 'Invalid Dr With No Reg No', 'Orthopedics', 'Patan Hospital', '9855555555', '', 'active']));
    $writer->close();

    $uploadedFile = new UploadedFile($tempCsv, 'doctors.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.doctors.import'), [
            'file' => $uploadedFile,
            'on_duplicate' => 'skip',
            'default_status' => 'active',
        ]);

    $response->assertRedirect(route('admin.doctors.index'));
    $response->assertSessionHas('success');
    $response->assertSessionHas('import_stats', function ($stats) {
        return $stats['total_rows'] === 5
            && $stats['imported'] === 2
            && $stats['duplicates_skipped'] === 2
            && $stats['invalid_skipped'] === 1;
    });

    expect(Doctor::where('registration_no', 'NMC-99902')->exists())->toBeTrue();
    expect(Doctor::where('registration_no', 'NMC-99903')->exists())->toBeTrue();
    expect(Doctor::where('registration_no', 'NMC-99901')->first()->hospital_clinic)->toBe('Old Clinic');

    @unlink($tempCsv);
});

test('admin can import doctors and update existing records when selected', function () {
    $existingUser = User::factory()->create(['name' => 'Dr. Old Name', 'email' => 'old.name@example.com']);
    $doctor = Doctor::create([
        'user_id' => $existingUser->id,
        'registration_no' => 'NMC-88888',
        'specialty' => 'General Medicine',
        'hospital_clinic' => 'Old Hospital',
        'status' => 'pending',
    ]);

    $tempCsv = tempnam(sys_get_temp_dir(), 'test_doctor_update_').'.csv';
    $writer = new CsvWriter;
    $writer->openToFile($tempCsv);
    $writer->addRow(Row::fromValues(['Registration No', 'Doctor Name', 'Specialty', 'Hospital / Clinic', 'Mobile', 'Email', 'Status']));
    $writer->addRow(Row::fromValues(['NMC-88888', 'Dr. New Updated Name', 'Cardiologist', 'New Grand Hospital', '9899999999', 'updated.dr@example.com', 'active']));
    $writer->close();

    $uploadedFile = new UploadedFile($tempCsv, 'doctors.csv', 'text/csv', null, true);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.doctors.import'), [
            'file' => $uploadedFile,
            'on_duplicate' => 'update',
        ]);

    $response->assertRedirect(route('admin.doctors.index'));
    $response->assertSessionHas('import_stats', function ($stats) {
        return $stats['updated'] === 1 && $stats['imported'] === 0;
    });

    $updated = Doctor::where('registration_no', 'NMC-88888')->with('user')->first();
    expect($updated->specialty)->toBe('Cardiologist');
    expect($updated->hospital_clinic)->toBe('New Grand Hospital');
    expect($updated->status)->toBe('active');
    expect($updated->user->name)->toBe('Dr. New Updated Name');
    expect($updated->user->email)->toBe('updated.dr@example.com');

    @unlink($tempCsv);
});

test('admin can export all doctors as xlsx and csv', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.doctors.export', ['scope' => 'all', 'format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.doctors.export', ['scope' => 'all', 'format' => 'csv']));

    $responseCsv->assertOk();
    $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('admin can export filtered doctors only', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.doctors.export', [
            'scope' => 'current',
            'format' => 'csv',
            'status' => 'active',
            'page' => 1,
            'per_page' => 10,
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
