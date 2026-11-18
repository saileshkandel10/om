<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthAnalyticEvidence;
use App\Models\Medicine;
use App\Models\PatientHealthAnalytic;
use App\Models\Prescription;
use App\Models\PurchaseBill;
use App\Services\HealthAnalytics\HealthAnalyticsAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HealthAnalyticsDashboardController extends Controller
{
    public function __construct(
        protected HealthAnalyticsAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->auditService->log('Viewed Health Analytics Dashboard', null, null, $request);

        // Real Database KPIs
        $totalPatientsAnalyzed = PatientHealthAnalytic::distinct('patient_id')->count('patient_id');
        $totalBillsAnalyzed = PurchaseBill::whereNotNull('ocr_status')->count();
        $totalPrescriptionsAnalyzed = Prescription::count();
        $totalMedicinesIdentified = Medicine::where('status', 'active')->count();
        $healthCategoriesDetected = PatientHealthAnalytic::distinct('health_category_id')->count('health_category_id');
        $repeatedPatternsCount = PatientHealthAnalytic::where('supporting_records_count', '>=', 2)->count();
        $pendingClinicalReviews = PatientHealthAnalytic::where('status', 'pending_review')->count();

        // 1. Health Category Distribution
        $categoryDistribution = PatientHealthAnalytic::select('health_categories.name', 'health_categories.color', DB::raw('count(*) as total'))
            ->join('health_categories', 'patient_health_analytics.health_category_id', '=', 'health_categories.id')
            ->groupBy('health_categories.id', 'health_categories.name', 'health_categories.color')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // 2. Association Levels Distribution
        $associationLevels = PatientHealthAnalytic::select('association_level', DB::raw('count(*) as count'))
            ->groupBy('association_level')
            ->pluck('count', 'association_level')
            ->toArray();

        // 3. Top Detected Medicines
        $topMedicines = HealthAnalyticEvidence::select('medicine_name', DB::raw('count(*) as detections'))
            ->groupBy('medicine_name')
            ->orderByDesc('detections')
            ->take(6)
            ->get();

        // 4. Monthly Analysis Volume (Last 6 Months)
        $dateExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyVolume = PurchaseBill::selectRaw("{$dateExpr} as month, count(*) as count")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.health-analytics.dashboard', compact(
            'totalPatientsAnalyzed',
            'totalBillsAnalyzed',
            'totalPrescriptionsAnalyzed',
            'totalMedicinesIdentified',
            'healthCategoriesDetected',
            'repeatedPatternsCount',
            'pendingClinicalReviews',
            'categoryDistribution',
            'associationLevels',
            'topMedicines',
            'monthlyVolume'
        ));
    }
}
