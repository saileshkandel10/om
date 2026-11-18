<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductCategory::withCount(['products', 'otherProducts']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->orderBy('name')->get();

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.product-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductCategory::create($validated);

        return to_route('admin.product-categories.index')->with('success', __('Category created successfully.'));
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('admin.product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name,'.$productCategory->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $productCategory->update($validated);

        return to_route('admin.product-categories.index')->with('success', __('Category updated successfully.'));
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        if ($productCategory->products()->count() > 0) {
            return back()->with('error', __('Cannot delete category with associated products.'));
        }

        $productCategory->delete();

        return to_route('admin.product-categories.index')->with('success', __('Category deleted successfully.'));
    }
}
