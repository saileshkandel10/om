<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthCategory;
use App\Models\PatientHealthAnalytic;
use App\Models\User;
use App\Services\HealthAnalytics\HealthAnalyticsAuditService;
use App\Services\HealthAnalytics\PatternEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientAnalyticsController extends Controller
{
    public function __construct(
        protected PatternEngineService $patternEngine,
        protected HealthAnalyticsAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->auditService->log('Viewed Patient Health Analytics List', null, null, $request);

        $query = PatientHealthAnalytic::with(['patient', 'healthCategory'])
            ->select('patient_health_analytics.*');

        // Filter by Patient Name, ID, or Phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        // Filter by Health Category
        if ($request->filled('category_id')) {
            $query->where('health_category_id', $request->category_id);
        }

        // Filter by Association Level
        if ($request->filled('association_level')) {
            $query->where('association_level', $request->association_level);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $analytics = $query->latest('latest_detected_date')->paginate(15);
        $categories = HealthCategory::where('status', 'active')->orderBy('name')->get();

        return view('admin.health-analytics.patients.index', compact('analytics', 'categories'));
    }

    public function show(Request $request, User $patient): View
    {
        $this->auditService->log('Viewed Patient Health Analytics Detail', $patient->id, null, $request);

        // Run analysis on-the-fly if no records exist yet
        if ($patient->patientAnalytics()->count() === 0) {
            $this->patternEngine->analyzePatient($patient);
        }

        $analytics = PatientHealthAnalytic::where('patient_id', $patient->id)
            ->with(['healthCategory', 'evidences' => fn ($q) => $q->latest('detected_date'), 'clinicalReviews.reviewer'])
            ->get();

        // Build Chronological Medication Timeline
        $timeline = [];
        foreach ($analytics as $an) {
            foreach ($an->evidences as $ev) {
                $dateKey = $ev->detected_date ? $ev->detected_date->format('F Y') : 'Prior Records';
                $timeline[$dateKey][] = [
                    'medicine_name' => $ev->medicine_name,
                    'generic_name' => $ev->generic_name,
                    'category' => $an->healthCategory->name,
                    'category_color' => $an->healthCategory->color,
                    'source_reference' => $ev->source_reference,
                    'date' => $ev->detected_date?->format('M d, Y'),
                ];
            }
        }

        return view('admin.health-analytics.patients.show', compact('patient', 'analytics', 'timeline'));
    }

    public function reanalyze(Request $request, User $patient): RedirectResponse
    {
        $this->auditService->log('Re-analyzed Patient Health Analytics', $patient->id, null, $request);

        $this->patternEngine->analyzePatient($patient);

        return back()->with('success', __('Patient health analytics re-analyzed successfully from bills and prescriptions.'));
    }
}
