<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IllnessCategory;
use App\Models\Product;
use App\Services\PatientIllnessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IllnessCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = IllnessCategory::withCount(['products', 'otherProducts'])
            ->with([
                'products' => fn ($q) => $q->with('category')->latest(),
                'otherProducts' => fn ($q) => $q->with('category')->latest(),
            ]);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->latest()->get();
        $unassignedProducts = Product::whereNull('illness_category_id')->orderBy('brand_name')->get();

        return view('admin.illness-categories.index', compact('categories', 'unassignedProducts'));
    }

    public function create(): View
    {
        return view('admin.illness-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:illness_categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        IllnessCategory::create($validated);

        return to_route('admin.illness-categories.index')->with('success', __('Illness category created successfully.'));
    }

    public function edit(IllnessCategory $illnessCategory): View
    {
        return view('admin.illness-categories.edit', compact('illnessCategory'));
    }

    public function update(Request $request, IllnessCategory $illnessCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:illness_categories,name,'.$illnessCategory->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $illnessCategory->update($validated);

        return to_route('admin.illness-categories.index')->with('success', __('Illness category updated successfully.'));
    }

    public function destroy(IllnessCategory $illnessCategory): RedirectResponse
    {
        if ($illnessCategory->products()->count() > 0) {
            return back()->with('error', __('Cannot delete category with associated products.'));
        }

        $illnessCategory->delete();

        return to_route('admin.illness-categories.index')->with('success', __('Illness category deleted successfully.'));
    }

    /**
     * Exclude / Unlink a product from this illness category.
     */
    public function unlinkProduct(IllnessCategory $illnessCategory, Product $product, PatientIllnessService $illnessService): RedirectResponse
    {
        if ($product->illness_category_id === $illnessCategory->id) {
            $product->update(['illness_category_id' => null]);
            $illnessService->syncProductExclusion($product);
        }

        return back()->with('success', __(':product was successfully excluded from :category.', [
            'product' => $product->brand_name,
            'category' => $illnessCategory->name,
        ]));
    }

    /**
     * Link products to this illness category.
     */
    public function linkProducts(Request $request, IllnessCategory $illnessCategory, PatientIllnessService $illnessService): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        Product::whereIn('id', $validated['product_ids'])->update(['illness_category_id' => $illnessCategory->id]);
        $illnessService->backfillFromHistoricalData();

        return back()->with('success', __('Products successfully linked to :category.', [
            'category' => $illnessCategory->name,
        ]));
    }
}
