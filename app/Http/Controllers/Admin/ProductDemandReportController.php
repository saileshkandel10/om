<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Services\ProductDemandAnalyticsService;
use App\Services\ProductDemandExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductDemandReportController extends Controller
{
    public function __construct(
        protected ProductDemandAnalyticsService $analyticsService,
        protected ProductDemandExportService $exportService
    ) {}

    /**
     * Display the main Product Demand & Purchasing Behaviour Report page.
     */
    public function index(Request $request): View
    {
        $filters = $request->all();
        $reportData = $this->analyticsService->getDemandReportData($filters);

        return view('admin.reports.product-demand', compact('reportData'));
    }

    /**
     * Export demand report data to Excel (.xlsx) or CSV (.csv).
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
     * Render a clean, print-friendly report page.
     */
    public function print(Request $request): View
    {
        $filters = $request->all();
        $reportData = $this->analyticsService->getDemandReportData($filters);

        return view('admin.reports.product-demand-print', compact('reportData'));
    }

    /**
     * Drill-down modal for a specific product.
     */
    public function productDrilldown(Request $request, string $productIdentifier): JsonResponse|View
    {
        $product = is_numeric($productIdentifier) && (int) $productIdentifier > 0
            ? Product::with('category')->find($productIdentifier)
            : Product::with('category')->where('brand_name', $productIdentifier)->first();

        $productName = $product?->brand_name ?? $productIdentifier;

        // Districts where this product is purchased
        $districtBreakdown = DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->leftJoin('districts', 'purchase_bills.district_id', '=', 'districts.id')
            ->leftJoin('provinces', 'districts.province_id', '=', 'provinces.id')
            ->where(function ($q) use ($product, $productName) {
                if ($product) {
                    $q->where('purchase_bill_items.product_id', $product->id);
                }
                $q->orWhere('purchase_bill_items.product_name_raw', $productName);
            })
            ->where('purchase_bills.order_status', '!=', 'cancelled')
            ->selectRaw('
                COALESCE(districts.name, "Unassigned District") as district_name,
                COALESCE(provinces.name, "Nepal") as province_name,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count,
                COUNT(DISTINCT purchase_bills.user_id) as customers_count,
                SUM(COALESCE(purchase_bill_items.amount, COALESCE(purchase_bill_items.quantity, 1) * COALESCE(purchase_bill_items.unit_price, 0))) as revenue
            ')
            ->groupBy('district_name', 'province_name')
            ->orderByDesc('units_sold')
            ->get();

        $totalUnits = $districtBreakdown->sum('units_sold');
        $totalOrders = $districtBreakdown->sum('orders_count');
        $totalRev = $districtBreakdown->sum('revenue');

        return view('admin.reports.partials.product-drilldown-modal', compact(
            'product',
            'productName',
            'districtBreakdown',
            'totalUnits',
            'totalOrders',
            'totalRev'
        ));
    }

    /**
     * Drill-down modal for a specific district.
     */
    public function districtDrilldown(Request $request, District $district): JsonResponse|View
    {
        $district->load('province');

        $bills = PurchaseBill::where('district_id', $district->id)
            ->where('order_status', '!=', 'cancelled')
            ->with(['user', 'items.product'])
            ->latest('id')
            ->get();

        $billIds = $bills->pluck('id')->toArray();

        $productsBreakdown = empty($billIds) ? collect() : DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->whereIn('purchase_bill_items.purchase_bill_id', $billIds)
            ->selectRaw('
                COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count,
                SUM(COALESCE(purchase_bill_items.amount, COALESCE(purchase_bill_items.quantity, 1) * COALESCE(purchase_bill_items.unit_price, 0))) as revenue
            ')
            ->groupBy('product_name')
            ->orderByDesc('units_sold')
            ->get();

        $totalOrders = $bills->count();
        $totalRevenue = $bills->sum('total_amount');
        $totalCustomers = $bills->whereNotNull('user_id')->pluck('user_id')->unique()->count();

        return view('admin.reports.partials.district-drilldown-modal', compact(
            'district',
            'bills',
            'productsBreakdown',
            'totalOrders',
            'totalRevenue',
            'totalCustomers'
        ));
    }
}
