<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\IllnessCategoryResource;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * List active products with search and category filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::where('is_active', true)
            ->with(['category', 'illnessCategory']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->integer('category_id'));
        }

        if ($request->filled('illness_category_id')) {
            $query->where('illness_category_id', $request->integer('illness_category_id'));
        }

        return ProductResource::collection(
            $query->orderBy('brand_name')->paginate($request->integer('per_page', 20))
        );
    }

    /**
     * Show a single product with its batches.
     */
    public function show(Product $product): ProductResource
    {
        $product->load(['category', 'illnessCategory', 'batches']);

        return new ProductResource($product);
    }

    /**
     * List all product categories.
     */
    public function categories(): AnonymousResourceCollection
    {
        return ProductCategoryResource::collection(
            ProductCategory::orderBy('name')->get()
        );
    }

    /**
     * List all illness categories.
     */
    public function illnessCategories(): AnonymousResourceCollection
    {
        return IllnessCategoryResource::collection(
            IllnessCategory::orderBy('name')->get()
        );
    }
}
