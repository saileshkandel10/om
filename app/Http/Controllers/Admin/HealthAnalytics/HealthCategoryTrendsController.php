<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthCategory;
use App\Models\PatientHealthAnalytic;
use App\Services\HealthAnalytics\HealthAnalyticsAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HealthCategoryTrendsController extends Controller
{
    public function __construct(
        protected HealthAnalyticsAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->auditService->log('Viewed Health Category Trends', null, null, $request);

        $categories = HealthCategory::where('status', 'active')
            ->withCount(['patientAnalytics', 'medicineIndications'])
            ->get();

        // Monthly trends query
        $dateExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', latest_detected_date)"
            : "DATE_FORMAT(latest_detected_date, '%Y-%m')";

        $monthlyCategoryTrends = PatientHealthAnalytic::select(
            'health_categories.name as category_name',
            DB::raw("{$dateExpr} as month"),
            DB::raw('count(*) as count')
        )
            ->join('health_categories', 'patient_health_analytics.health_category_id', '=', 'health_categories.id')
            ->whereNotNull('latest_detected_date')
            ->groupBy('category_name', 'month')
            ->orderBy('month')
            ->get();

        return view('admin.health-analytics.trends.index', compact('categories', 'monthlyCategoryTrends'));
    }
}
