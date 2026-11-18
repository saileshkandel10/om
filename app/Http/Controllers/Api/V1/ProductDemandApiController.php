<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductDemandAnalyticsService;
use App\Services\ProductDemandExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductDemandApiController extends Controller
{
    public function __construct(
        protected ProductDemandAnalyticsService $analyticsService,
        protected ProductDemandExportService $exportService
    ) {}

    /**
     * Overall product demand overview & KPI metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'summary' => $data['summary'],
            'time_series_demand' => $data['time_series_demand'],
            'applied_filters' => $request->all(),
            'date_range' => $data['date_range'],
        ]);
    }

    /**
     * Trending products ranked by demand score and growth.
     */
    public function trending(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'trending_products' => $data['trending_products'],
            'fastest_growing' => $data['fastest_growing'],
            'low_demand' => $data['low_demand_products'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Top purchasing districts across Nepal.
     */
    public function topDistricts(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'top_districts' => $data['district_demand'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Province-level purchasing demand.
     */
    public function provinceDemand(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'provinces' => $data['province_demand'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Product × District cross-demand matrix.
     */
    public function productDistrictDemand(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'matrix' => $data['product_district_matrix'],
            'district_top_products' => $data['district_top_products'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Customer purchasing behaviour (New vs Returning, AOV, monthly trends).
     */
    public function customerBehaviour(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'behaviour' => $data['customer_behaviour'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Purchase frequency grouping (1, 2, 3, 4, 5+ orders).
     */
    public function purchaseFrequency(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'frequency_brackets' => $data['customer_behaviour']['frequency_brackets'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Product growth comparison.
     */
    public function productGrowth(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'fastest_growing' => $data['fastest_growing'],
            'low_demand' => $data['low_demand_products'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Geographic demand coverage of Nepal 77 districts.
     */
    public function districtCoverage(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'coverage' => $data['district_coverage'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Orders by day of week and hour of day.
     */
    public function purchaseTimeAnalysis(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDemandReportData($request->all());

        return response()->json([
            'success' => true,
            'time_analysis' => $data['time_analysis'],
            'applied_filters' => $request->all(),
        ]);
    }

    /**
     * Export product demand report.
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
}
