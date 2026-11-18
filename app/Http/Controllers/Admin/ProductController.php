<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\PatientIllnessService;
use App\Services\ProductExportService;
use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductImportService $importService,
        protected ProductExportService $exportService
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with(['category', 'illnessCategory']);

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('illness_category_id')) {
            $query->where('illness_category_id', $request->illness_category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('brand_name', 'like', "%{$request->search}%")
                    ->orWhere('generic_name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        $products = $query->latest()->get();
        $categories = ProductCategory::pluck('name', 'id');
        $illnessCategories = IllnessCategory::pluck('name', 'id');

        return view('admin.products.index', compact('products', 'categories', 'illnessCategories'));
    }

    public function create(): View
    {
        $categories = ProductCategory::pluck('name', 'id');
        $illnessCategories = IllnessCategory::pluck('name', 'id');

        return view('admin.products.create', compact('categories', 'illnessCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'brand_name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'illness_category_id' => ['nullable', 'exists:illness_categories,id'],
            'dosage_form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'pack_size' => ['nullable', 'string', 'max:50'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';
        unset($validated['status']);

        Product::create($validated);

        if (! empty($validated['illness_category_id'])) {
            try {
                app(PatientIllnessService::class)->backfillFromHistoricalData();
            } catch (\Exception $e) {
            }
        }

        return to_route('admin.products.index')->with('success', __('Product created successfully.'));
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'illnessCategory', 'batches', 'purchaseBillItems' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = ProductCategory::pluck('name', 'id');
        $illnessCategories = IllnessCategory::pluck('name', 'id');

        return view('admin.products.edit', compact('product', 'categories', 'illnessCategories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.$product->id],
            'brand_name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'illness_category_id' => ['nullable', 'exists:illness_categories,id'],
            'dosage_form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'pack_size' => ['nullable', 'string', 'max:50'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';
        unset($validated['status']);

        $oldIllnessCategory = $product->illness_category_id;
        $product->update($validated);

        if ($oldIllnessCategory !== $product->illness_category_id) {
            try {
                app(PatientIllnessService::class)->backfillFromHistoricalData();
            } catch (\Exception $e) {
            }
        }

        return to_route('admin.products.index')->with('success', __('Product updated successfully.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return to_route('admin.products.index')->with('success', __('Product deleted successfully.'));
    }

    /**
     * Export Own Catalogue Products to Excel (.xlsx) or CSV (.csv).
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $filename = 'om_connect_product_catalogue_'.now()->format('Y_m_d_His').".{$format}";

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
     * Download standard Sample Import Template.
     */
    public function sampleTemplate(Request $request): BinaryFileResponse
    {
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $filename = "om_connect_product_import_template.{$format}";

        $tempDir = storage_path('app/templates');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir.'/'.$filename;
        $this->exportService->generateSampleTemplate($filePath, $format);

        return response()->download($filePath, $filename, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * AJAX endpoint to preview the first 10 rows of uploaded Excel/CSV file.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $previewData = $this->importService->preview($path, 10);

        return response()->json($previewData);
    }

    /**
     * Process bulk import of Products from CSV or Excel file.
     */
    public function import(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'on_duplicate' => ['nullable', 'string', 'in:update,skip'],
            'auto_create_categories' => ['nullable'],
            'default_status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $options = [
            'on_duplicate' => $request->input('on_duplicate', 'update'),
            'auto_create_categories' => $request->boolean('auto_create_categories', true),
            'default_status' => $request->input('default_status', 'active'),
        ];

        $stats = $this->importService->import($path, $options);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'message' => "Import complete: {$stats['imported']} new products created, {$stats['updated']} updated, {$stats['duplicates_skipped']} skipped duplicates.",
            ]);
        }

        $msg = "Catalogue Import complete: {$stats['imported']} created, {$stats['updated']} updated, {$stats['duplicates_skipped']} skipped duplicates, {$stats['invalid_skipped']} invalid rows skipped.";

        if (! empty($stats['errors'])) {
            return to_route('admin.products.index')->with('warning', $msg.' Some rows encountered notices: '.implode(' ', array_slice($stats['errors'], 0, 3)));
        }

        return to_route('admin.products.index')->with('success', $msg);
    }
}
