<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\RewardRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardRuleController extends Controller
{
    public function index(Request $request): View
    {
        $query = RewardRule::with(['product', 'illnessCategory']);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $rules = $query->latest()->get();

        return view('admin.reward-rules.index', compact('rules'));
    }

    public function create(): View
    {
        $products = Product::pluck('brand_name', 'id');
        $illnessCategories = IllnessCategory::pluck('name', 'id');

        return view('admin.reward-rules.create', compact('products', 'illnessCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_type' => ['required', 'string', 'in:shareholder,pharmacy,doctor'],
            'basis_type' => ['required', 'string', 'in:qty,amount,hybrid'],
            'product_id' => ['nullable', 'exists:products,id'],
            'illness_category_id' => ['nullable', 'exists:illness_categories,id'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'point_rate' => ['required', 'numeric', 'min:0'],
            'bonus_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'bonus_value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        RewardRule::create($validated);

        return to_route('admin.reward-rules.index')->with('success', __('Reward rule created successfully.'));
    }

    public function show(RewardRule $rewardRule): View
    {
        $rewardRule->load(['product', 'illnessCategory', 'transactions' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.reward-rules.show', compact('rewardRule'));
    }

    public function edit(RewardRule $rewardRule): View
    {
        $products = Product::pluck('brand_name', 'id');
        $illnessCategories = IllnessCategory::pluck('name', 'id');

        return view('admin.reward-rules.edit', compact('rewardRule', 'products', 'illnessCategories'));
    }

    public function update(Request $request, RewardRule $rewardRule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_type' => ['required', 'string', 'in:shareholder,pharmacy,doctor'],
            'basis_type' => ['required', 'string', 'in:qty,amount,hybrid'],
            'product_id' => ['nullable', 'exists:products,id'],
            'illness_category_id' => ['nullable', 'exists:illness_categories,id'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'point_rate' => ['required', 'numeric', 'min:0'],
            'bonus_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'bonus_value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        $rewardRule->update($validated);

        return to_route('admin.reward-rules.index')->with('success', __('Reward rule updated successfully.'));
    }

    public function destroy(RewardRule $rewardRule): RedirectResponse
    {
        $rewardRule->delete();

        return to_route('admin.reward-rules.index')->with('success', __('Reward rule deleted successfully.'));
    }
}
