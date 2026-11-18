<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductBatchController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductBatch::with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('batch_no', 'like', "%{$request->search}%");
        }

        $batches = $query->latest()->get();

        return view('admin.product-batches.index', compact('batches'));
    }

    public function create(): View
    {
        $products = Product::pluck('brand_name', 'id');

        return view('admin.product-batches.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'batch_no' => ['required', 'string', 'max:255'],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:manufacture_date'],
            'status' => ['nullable', 'string', 'in:active,inactive,expired'],
        ]);

        ProductBatch::create($validated);

        return to_route('admin.product-batches.index')->with('success', __('Batch created successfully.'));
    }

    public function show(ProductBatch $productBatch): View
    {
        $productBatch->load('product');

        return view('admin.product-batches.show', compact('productBatch'));
    }

    public function edit(ProductBatch $productBatch): View
    {
        $products = Product::pluck('brand_name', 'id');

        return view('admin.product-batches.edit', compact('productBatch', 'products'));
    }

    public function update(Request $request, ProductBatch $productBatch): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'batch_no' => ['required', 'string', 'max:255'],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:manufacture_date'],
            'status' => ['nullable', 'string', 'in:active,inactive,expired'],
        ]);

        $productBatch->update($validated);

        return to_route('admin.product-batches.index')->with('success', __('Batch updated successfully.'));
    }

    public function destroy(ProductBatch $productBatch): RedirectResponse
    {
        $productBatch->delete();

        return to_route('admin.product-batches.index')->with('success', __('Batch deleted successfully.'));
    }
}
