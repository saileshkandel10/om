<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IllnessCategory;
use App\Models\OtherProduct;
use App\Models\ProductCategory;
use App\Services\OtherProductExportService;
use App\Services\OtherProductImportService;
use App\Services\OtherProductMappingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OtherProductController extends Controller
{
    public function __construct(
        protected OtherProductMappingService $mappingService,
        protected OtherProductImportService $importService,
        protected OtherProductExportService $exportService
    ) {}

    /**
     * Display the Other Products index with tabs for Master Directory and Unmapped Queue.
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'catalog');

        // Product Categories & Illness Categories for dropdowns
        $categories = ProductCategory::orderBy('name')->get();
        $illnessCategories = IllnessCategory::orderBy('name')->get();

        // Metrics Summary
        $stats = [
            'total' => OtherProduct::count(),
            'active' => OtherProduct::where('status', 'active')->count(),
            'unmapped' => OtherProduct::unmapped()->count(),
            'illness_categories_count' => OtherProduct::whereNotNull('illness_category_id')->distinct('illness_category_id')->count('illness_category_id'),
        ];

        // 1. Catalog Query
        $catalogQuery = OtherProduct::with(['category', 'illnessCategory'])->latest('id');

        if ($request->filled('search') && $tab === 'catalog') {
            $catalogQuery->search($request->search);
        }

        if ($request->filled('category_id') && $tab === 'catalog') {
            $catalogQuery->where('category_id', $request->category_id);
        }

        if ($request->filled('illness_category_id') && $tab === 'catalog') {
            $catalogQuery->where('illness_category_id', $request->illness_category_id);
        }

        if ($request->filled('status') && $tab === 'catalog') {
            if ($request->status === 'unmapped') {
                $catalogQuery->unmapped();
            } else {
                $catalogQuery->where('status', $request->status);
            }
        }

        $products = $catalogQuery->paginate(50, ['*'], 'catalog_page')->withQueryString();

        // 2. Unmapped Queue Query
        $unmappedQuery = OtherProduct::with(['category', 'illnessCategory', 'suggestedIllnessCategory'])
            ->unmapped()
            ->latest('id');

        if ($request->filled('search') && $tab === 'unmapped') {
            $unmappedQuery->search($request->search);
        }

        $unmappedProducts = $unmappedQuery->paginate(25, ['*'], 'unmapped_page')->withQueryString();

        // Compute top 3 real-time suggestions for each unmapped item on current page
        $unmappedRows = [];
        foreach ($unmappedProducts->items() as $item) {
            $suggestions = $this->mappingService->suggestIllnessCategories($item->brand_name, $item->generic_name, 3);
            $unmappedRows[] = [
                'model' => $item,
                'suggestions' => $suggestions,
            ];
        }

        return view('admin.other-products.index', compact(
            'products',
            'unmappedProducts',
            'unmappedRows',
            'categories',
            'illnessCategories',
            'stats',
            'tab'
        ));
    }

    /**
     * Store a newly created OtherProduct manually.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'illness_category_id' => ['nullable', 'exists:illness_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:active,unmapped,flagged'],
        ]);

        $hasIllness = ! empty($validated['illness_category_id']);
        $status = $validated['status'] ?? ($hasIllness ? 'active' : 'unmapped');
        $isVerified = ($status === 'active' && $hasIllness);

        // Calculate suggestion if illness category not set
        $suggestedId = null;
        $confidence = null;
        if (! $hasIllness) {
            $suggestions = $this->mappingService->suggestIllnessCategories($validated['brand_name'], $validated['generic_name'] ?? null, 1);
            if (! empty($suggestions)) {
                $suggestedId = $suggestions[0]['illness_category_id'];
                $confidence = $suggestions[0]['confidence_score'];
            }
        }

        OtherProduct::create([
            'brand_name' => $validated['brand_name'],
            'generic_name' => $validated['generic_name'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'illness_category_id' => $validated['illness_category_id'] ?? null,
            'suggested_illness_category_id' => $suggestedId,
            'confidence_score' => $confidence,
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'is_verified' => $isVerified,
            'source' => 'manual',
            'user_id' => auth()->id(),
        ]);

        if ($hasIllness) {
            app(\App\Services\PatientIllnessService::class)->backfillFromHistoricalData();
        }

        return back()->with('success', __('Other product ":name" added successfully.', ['name' => $validated['brand_name']]));
    }

    /**
     * Update the specified OtherProduct.
     */
    public function update(Request $request, OtherProduct $otherProduct): RedirectResponse
    {
        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'illness_category_id' => ['nullable', 'exists:illness_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', 'in:active,unmapped,flagged'],
        ]);

        $hasIllness = ! empty($validated['illness_category_id']);
        $validated['is_verified'] = ($validated['status'] === 'active' && $hasIllness);

        $otherProduct->update($validated);

        if ($hasIllness) {
            app(\App\Services\PatientIllnessService::class)->backfillFromHistoricalData();
        }

        return back()->with('success', __('Other product ":name" updated successfully.', ['name' => $otherProduct->brand_name]));
    }

    /**
     * Remove the specified OtherProduct from storage.
     */
    public function destroy(OtherProduct $otherProduct): RedirectResponse
    {
        $name = $otherProduct->brand_name;
        $otherProduct->delete();

        return back()->with('success', __('Other product ":name" deleted successfully.', ['name' => $name]));
    }

    /**
     * Map a single unmapped product to an Illness Category.
     */
    public function map(Request $request, OtherProduct $otherProduct): RedirectResponse
    {
        $validated = $request->validate([
            'illness_category_id' => ['required', 'exists:illness_categories,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->mappingService->mapSingle(
            $otherProduct,
            (int) $validated['illness_category_id'],
            ! empty($validated['category_id']) ? (int) $validated['category_id'] : null,
            $validated['generic_name'] ?? null,
            $validated['description'] ?? null
        );

        app(\App\Services\PatientIllnessService::class)->backfillFromHistoricalData();

        return back()->with('success', __('Product ":brand" successfully mapped to :category.', [
            'brand' => $otherProduct->brand_name,
            'category' => $otherProduct->fresh()->illnessCategory?->name ?? 'Illness Category',
        ]));
    }

    /**
     * Batch map multiple unmapped products to an Illness Category.
     */
    public function batchMap(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'exists:other_products,id'],
            'illness_category_id' => ['required', 'exists:illness_categories,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
        ]);

        $count = $this->mappingService->batchMap(
            $validated['ids'],
            (int) $validated['illness_category_id'],
            ! empty($validated['category_id']) ? (int) $validated['category_id'] : null
        );

        app(\App\Services\PatientIllnessService::class)->backfillFromHistoricalData();

        $category = IllnessCategory::find($validated['illness_category_id']);

        return back()->with('success', __('Successfully mapped :count products to :category in bulk.', [
            'count' => $count,
            'category' => $category?->name ?? 'Illness Category',
        ]));
    }

    /**
     * Auto scan and compute suggestions for all unmapped products.
     */
    public function autoSuggestAll(): RedirectResponse
    {
        $results = $this->mappingService->autoScanAndSuggestUnmapped();

        return back()->with('success', __('Scanned :total unmapped products. Computed suggestions for :suggested items (:high with high confidence).', [
            'total' => $results['total_scanned'],
            'suggested' => $results['suggested_count'],
            'high' => $results['high_confidence_count'],
        ]));
    }

    /**
     * AJAX endpoint to fetch real-time suggestions for a given brand/generic query.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $brand = $request->input('brand_name', '');
        $generic = $request->input('generic_name', '');

        $suggestions = $this->mappingService->suggestIllnessCategories($brand, $generic, 5);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Preview import file rows before committing to DB.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:51200'],
        ]);

        $file = $request->file('file');
        $preview = $this->importService->preview($file->getRealPath(), 10);

        return response()->json($preview);
    }

    /**
     * Handle bulk Excel / CSV import.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:51200'],
            'on_duplicate' => ['nullable', 'string', 'in:skip,update'],
            'default_status' => ['nullable', 'string', 'in:active,unmapped'],
        ]);

        $file = $request->file('file');
        $options = [
            'on_duplicate' => $request->input('on_duplicate', 'skip'),
            'default_status' => $request->input('default_status', 'active'),
            'user_id' => auth()->id(),
        ];

        $stats = $this->importService->import($file->getRealPath(), $options);
        
        if ($stats['imported'] > 0 || $stats['updated'] > 0) {
            app(\App\Services\PatientIllnessService::class)->backfillFromHistoricalData();
        }

        $msg = "Bulk import completed. Total rows: {$stats['total_rows']}, Imported: {$stats['imported']}, Updated: {$stats['updated']}, Duplicates Skipped: {$stats['duplicates_skipped']}, Invalid Skipped: {$stats['invalid_skipped']}.";

        if ($stats['imported'] === 0 && $stats['updated'] === 0 && ! empty($stats['errors'])) {
            return back()->with('error', 'Import failed: '.implode(' | ', array_slice($stats['errors'], 0, 5)));
        }

        if (! empty($stats['errors'])) {
            return back()->with('success', $msg)->with('warning', 'Some rows had issues: '.implode(' | ', array_slice($stats['errors'], 0, 5)));
        }

        return back()->with('success', $msg);
    }

    /**
     * Export products to Excel/CSV.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $format = $request->input('format', 'xlsx');
        $filename = 'other_products_export_'.date('Y_m_d_His').'.'.$format;
        $tempPath = storage_path('app/temp_'.$filename);

        $this->exportService->export($tempPath, $request->all());

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Download sample Excel template for bulk import.
     */
    public function sampleTemplate(Request $request): BinaryFileResponse
    {
        $format = $request->input('format', 'xlsx');
        $filename = 'other_products_sample_template.'.$format;
        $tempPath = storage_path('app/sample_'.$filename);

        $this->exportService->generateSampleTemplate($tempPath, $format);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
