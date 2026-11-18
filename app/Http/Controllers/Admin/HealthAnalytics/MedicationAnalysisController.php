<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthAnalyticEvidence;
use App\Models\HealthCategory;
use App\Models\Medicine;
use App\Services\HealthAnalytics\HealthAnalyticsAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicationAnalysisController extends Controller
{
    public function __construct(
        protected HealthAnalyticsAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->auditService->log('Viewed Medication Analysis', null, null, $request);

        $query = Medicine::with(['indications.healthCategory']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('therapeutic_class', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('indications', function ($q) use ($request) {
                $q->where('health_category_id', $request->category_id);
            });
        }

        $medicines = $query->paginate(15);

        // Compute occurrence counts from evidence
        $evidenceStats = HealthAnalyticEvidence::select('medicine_name', DB::raw('count(*) as total_records'))
            ->groupBy('medicine_name')
            ->pluck('total_records', 'medicine_name');

        $categories = HealthCategory::where('status', 'active')->orderBy('name')->get();

        return view('admin.health-analytics.medications.index', compact('medicines', 'evidenceStats', 'categories'));
    }
}
