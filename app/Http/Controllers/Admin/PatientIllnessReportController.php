<?php

namespace App\Services;

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PatientIllnessExportService;
use App\Services\PatientIllnessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientIllnessReportController extends Controller
{
    public function __construct(
        protected PatientIllnessService $illnessService,
        protected PatientIllnessExportService $exportService
    ) {}

    /**
     * Display the main Illness Category Report page.
     */
    public function index(Request $request): View
    {
        $filters = $request->all();
        $reportData = $this->illnessService->getReportData($filters);

        return view('admin.reports.illness-categories', compact('reportData'));
    }

    /**
     * Get patient profile and medical drill-down details for modal presentation.
     */
    public function patientModal(User $user): JsonResponse|View
    {
        $user->load([
            'profile',
            'illnessCategories',
            'patientIllnesses.illnessCategory',
            'patientIllnesses.product',
            'purchaseBills.pharmacy',
            'purchaseBills.items.product',
            'doctor.prescriptions.items.product',
        ]);

        $bills = $user->purchaseBills()->with(['pharmacy', 'items.product', 'uploadedFile'])->latest()->get();
        $patientIllnesses = $user->patientIllnesses()->with(['illnessCategory', 'product'])->latest('detected_at')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'user' => $user,
                'bills' => $bills,
                'illnesses' => $patientIllnesses,
            ]);
        }

        return view('admin.reports.partials.patient-modal-content', compact('user', 'bills', 'patientIllnesses'));
    }

    /**
     * Trigger bill & prescription synchronization for all historical records.
     */
    public function sync(Request $request): RedirectResponse
    {
        $results = $this->illnessService->backfillFromHistoricalData();

        return back()->with('success', __('Successfully scanned and synchronized :count patient illness records from purchase bills and prescriptions.', [
            'count' => $results['total_synced'],
        ]));
    }

    /**
     * Export report data to Excel (.xlsx) or CSV (.csv).
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $filename = $this->exportService->generateFilename($filters, $format);

        $tempDir = storage_path('app/exports');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir.'/'.$filename;
        $this->exportService->export($filePath, $filters);

        return response()->download($filePath, $filename, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Display a clean, print-friendly report page.
     */
    public function print(Request $request): View
    {
        $filters = $request->all();
        $filters['per_page'] = 500; // Load larger cohort for printing
        $reportData = $this->illnessService->getReportData($filters);

        return view('admin.reports.illness-categories-print', compact('reportData'));
    }
}
