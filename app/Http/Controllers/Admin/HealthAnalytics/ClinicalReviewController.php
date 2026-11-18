<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\ClinicalReview;
use App\Models\PatientHealthAnalytic;
use App\Services\HealthAnalytics\HealthAnalyticsAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClinicalReviewController extends Controller
{
    public function __construct(
        protected HealthAnalyticsAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->auditService->log('Viewed Clinical Review Queue', null, null, $request);

        $query = PatientHealthAnalytic::with(['patient', 'healthCategory', 'evidences']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending_review');
        }

        $analytics = $query->latest()->paginate(15);
        $pendingCount = PatientHealthAnalytic::where('status', 'pending_review')->count();
        $confirmedCount = PatientHealthAnalytic::where('status', 'confirmed_pattern')->count();
        $needsReviewCount = PatientHealthAnalytic::where('status', 'needs_review')->count();

        return view('admin.health-analytics.reviews.index', compact(
            'analytics',
            'pendingCount',
            'confirmedCount',
            'needsReviewCount'
        ));
    }

    public function submit(Request $request, PatientHealthAnalytic $analytic): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:confirmed_pattern,not_applicable,needs_review'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviewerId = Auth::id() ?? 1;

        ClinicalReview::create([
            'health_analytic_id' => $analytic->id,
            'reviewer_id' => $reviewerId,
            'decision' => $validated['decision'],
            'comments' => $validated['comments'] ?? null,
            'reviewed_at' => now(),
        ]);

        $analytic->update([
            'status' => $validated['decision'],
        ]);

        $this->auditService->log('Submitted Clinical Review', $analytic->patient_id, [
            'analytic_id' => $analytic->id,
            'decision' => $validated['decision'],
        ], $request);

        return back()->with('success', __('Clinical review decision submitted successfully.'));
    }
}
