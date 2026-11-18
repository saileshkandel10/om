<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PatientIllnessExportService;
use App\Services\PatientIllnessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientIllnessReportApiController extends Controller
{
    public function __construct(
        protected PatientIllnessService $illnessService,
        protected PatientIllnessExportService $exportService
    ) {}

    /**
     * Illness Category Summary & KPI statistics.
     * GET /api/v1/reports/illness-summary
     */
    public function summary(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->illnessService->getReportData($filters);

        return response()->json([
            'success' => true,
            'summary' => [
                'total_unique_patients' => $data['total_unique_patients'],
                'total_illness_records' => $data['total_illness_records'],
                'multiple_illness_patients_count' => $data['multiple_illness_patients_count'],
            ],
            'categories' => $data['category_summary'],
            'distribution' => $data['distribution'],
            'applied_filters' => $filters,
        ]);
    }

    /**
     * Paginated patient records for illness categories.
     * GET /api/v1/reports/illness-patients
     */
    public function patients(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->illnessService->getReportData($filters);

        return response()->json([
            'success' => true,
            'pagination' => [
                'current_page' => $data['patients']->currentPage(),
                'per_page' => $data['patients']->perPage(),
                'total' => $data['patients']->total(),
                'last_page' => $data['patients']->lastPage(),
            ],
            'patients' => $data['patient_rows'],
            'applied_filters' => $filters,
        ]);
    }

    /**
     * Multi-illness breakdown (1, 2, 3, 4+ illnesses).
     * GET /api/v1/reports/multi-illness
     */
    public function multiIllness(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->illnessService->getReportData($filters);

        return response()->json([
            'success' => true,
            'total_unique_patients' => $data['total_unique_patients'],
            'multiple_illness_patients_count' => $data['multiple_illness_patients_count'],
            'distribution' => $data['distribution'],
            'applied_filters' => $filters,
        ]);
    }

    /**
     * Top illness co-occurring combinations.
     * GET /api/v1/reports/illness-combinations
     */
    public function combinations(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->illnessService->getReportData($filters);

        return response()->json([
            'success' => true,
            'top_combinations' => $data['top_combinations'],
            'applied_filters' => $filters,
        ]);
    }

    /**
     * Monthly illness trends over time.
     * GET /api/v1/reports/illness-trends
     */
    public function trends(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->illnessService->getReportData($filters);

        return response()->json([
            'success' => true,
            'monthly_trends' => $data['monthly_trends'],
            'age_distribution' => $data['age_distribution'],
            'gender_distribution' => $data['gender_distribution'],
            'applied_filters' => $filters,
        ]);
    }

    /**
     * Export report to Excel.
     * GET /api/v1/reports/export/excel
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filters['format'] = 'xlsx';
        $filename = $this->exportService->generateFilename($filters, 'xlsx');

        $tempDir = storage_path('app/exports');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir.'/'.$filename;
        $this->exportService->export($filePath, $filters);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export report to CSV.
     * GET /api/v1/reports/export/csv
     */
    public function exportCsv(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filters['format'] = 'csv';
        $filename = $this->exportService->generateFilename($filters, 'csv');

        $tempDir = storage_path('app/exports');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir.'/'.$filename;
        $this->exportService->export($filePath, $filters);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export report to PDF / Print view.
     * GET /api/v1/reports/export/pdf
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->all();
        $filters['per_page'] = 500;
        $reportData = $this->illnessService->getReportData($filters);

        return view('admin.reports.illness-categories-print', compact('reportData'));
    }
}
