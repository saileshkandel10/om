<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthCategory;
use App\Models\Medicine;
use App\Models\MedicineIndication;
use App\Services\HealthAnalytics\HealthAnalyticsAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineMappingController extends Controller
{
    public function __construct(
        protected HealthAnalyticsAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->auditService->log('Viewed Medicine-Condition Mappings', null, null, $request);

        $query = MedicineIndication::with(['medicine', 'healthCategory']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('medicine', function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('health_category_id', $request->category_id);
        }

        $mappings = $query->paginate(15);
        $medicines = Medicine::where('status', 'active')->orderBy('brand_name')->get();
        $categories = HealthCategory::where('status', 'active')->orderBy('name')->get();

        return view('admin.health-analytics.mappings.index', compact('mappings', 'medicines', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => ['required', 'exists:medicines,id'],
            'health_category_id' => ['required', 'exists:health_categories,id'],
            'evidence_level' => ['required', 'in:High,Moderate,Low'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $mapping = MedicineIndication::updateOrCreate(
            [
                'medicine_id' => $validated['medicine_id'],
                'health_category_id' => $validated['health_category_id'],
            ],
            [
                'evidence_level' => $validated['evidence_level'],
                'notes' => $validated['notes'],
                'status' => 'active',
            ]
        );

        $this->auditService->log('Created Medicine-Condition Mapping', null, ['mapping_id' => $mapping->id], $request);

        return back()->with('success', __('Medicine indication mapping saved successfully.'));
    }

    public function destroy(Request $request, MedicineIndication $mapping): RedirectResponse
    {
        $this->auditService->log('Deleted Medicine-Condition Mapping', null, ['mapping_id' => $mapping->id], $request);
        $mapping->delete();

        return back()->with('success', __('Mapping removed successfully.'));
    }
}
