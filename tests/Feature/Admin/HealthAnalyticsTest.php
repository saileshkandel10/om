<?php

use App\Models\HealthCategory;
use App\Models\Medicine;
use App\Models\MedicineIndication;
use App\Models\PatientHealthAnalytic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::create(['name' => 'admin']);
    $this->patientRole = Role::create(['name' => 'customer']);

    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole->id);

    $this->patient = User::factory()->create();
    $this->patient->roles()->attach($this->patientRole->id);

    $this->category = HealthCategory::create([
        'name' => 'Blood Sugar Management',
        'slug' => 'blood-sugar-management',
        'description' => 'Diabetes & Glycemic care',
        'icon' => 'iconoir-drop',
        'color' => '#E67E22',
    ]);

    $this->medicine = Medicine::create([
        'brand_name' => 'Glucophage',
        'generic_name' => 'Metformin',
        'strength' => '500mg',
        'dosage_form' => 'Tablet',
        'therapeutic_class' => 'Antidiabetic',
    ]);

    $this->mapping = MedicineIndication::create([
        'medicine_id' => $this->medicine->id,
        'health_category_id' => $this->category->id,
        'evidence_level' => 'High',
    ]);
});

test('admin can access health analytics dashboard', function () {
    $response = $this->actingAs($this->admin)->get('/admin/health-analytics/dashboard');

    $response->assertOk()
        ->assertSee('Health Analytics Dashboard')
        ->assertSee('Analytical Insight Standard');
});

test('non-admin cannot access health analytics', function () {
    $response = $this->actingAs($this->patient)->get('/admin/health-analytics/dashboard');

    $response->assertForbidden();
});

test('admin can view patient health analytics list and details', function () {
    $analytic = PatientHealthAnalytic::create([
        'patient_id' => $this->patient->id,
        'health_category_id' => $this->category->id,
        'association_level' => 'Strong Association',
        'supporting_medicines_count' => 2,
        'supporting_records_count' => 3,
        'first_detected_date' => now()->subMonths(3)->toDateString(),
        'latest_detected_date' => now()->toDateString(),
        'explanation' => 'Observed repeated antidiabetic medications.',
        'status' => 'pending_review',
    ]);

    $listResponse = $this->actingAs($this->admin)->get('/admin/health-analytics/patients');
    $listResponse->assertOk()
        ->assertSee($this->patient->name)
        ->assertSee('Blood Sugar Management');

    $detailResponse = $this->actingAs($this->admin)->get("/admin/health-analytics/patients/{$this->patient->id}");
    $detailResponse->assertOk()
        ->assertSee('Strong Association')
        ->assertSee('This is a medication-based analytical insight and does not confirm a medical diagnosis.');
});

test('admin can submit a clinical review decision', function () {
    $analytic = PatientHealthAnalytic::create([
        'patient_id' => $this->patient->id,
        'health_category_id' => $this->category->id,
        'association_level' => 'Strong Association',
        'status' => 'pending_review',
    ]);

    $response = $this->actingAs($this->admin)->post("/admin/health-analytics/reviews/{$analytic->id}/submit", [
        'decision' => 'confirmed_pattern',
        'comments' => 'Consistent patient medication profile confirmed.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('patient_health_analytics', [
        'id' => $analytic->id,
        'status' => 'confirmed_pattern',
    ]);
    $this->assertDatabaseHas('clinical_reviews', [
        'health_analytic_id' => $analytic->id,
        'decision' => 'confirmed_pattern',
    ]);
});

test('admin can create and remove medicine condition mappings', function () {
    $newMed = Medicine::create([
        'brand_name' => 'Amaryl',
        'generic_name' => 'Glimepiride',
        'therapeutic_class' => 'Sulfonylurea',
    ]);

    $response = $this->actingAs($this->admin)->post('/admin/health-analytics/mappings', [
        'medicine_id' => $newMed->id,
        'health_category_id' => $this->category->id,
        'evidence_level' => 'High',
        'notes' => 'Antidiabetic agent',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('medicine_indications', [
        'medicine_id' => $newMed->id,
        'health_category_id' => $this->category->id,
    ]);

    $mapping = MedicineIndication::where('medicine_id', $newMed->id)->first();
    $delResponse = $this->actingAs($this->admin)->delete("/admin/health-analytics/mappings/{$mapping->id}");
    $delResponse->assertRedirect();
    $this->assertDatabaseMissing('medicine_indications', ['id' => $mapping->id]);
});
