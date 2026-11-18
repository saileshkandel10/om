<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardRedemption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardRedemptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = RewardRedemption::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('redemption_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('redemption_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $redemptions = $query->latest()->get();

        return view('admin.redemptions.index', compact('redemptions'));
    }

    public function show(RewardRedemption $redemption): View
    {
        $redemption->load('user');

        return view('admin.redemptions.show', compact('redemption'));
    }

    public function update(Request $request, RewardRedemption $redemption): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected,completed'],
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'approved') {
            $data['approved_at'] = now();
        }

        $redemption->update($data);

        return to_route('admin.redemptions.index')->with('success', __('Redemption updated successfully.'));
    }
}
