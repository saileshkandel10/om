<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pharmacy::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('store_name', 'like', "%{$request->search}%")
                    ->orWhere('license_no', 'like', "%{$request->search}%")
                    ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $pharmacies = $query->latest()->get();

        return view('admin.pharmacies.index', compact('pharmacies'));
    }

    public function show(Pharmacy $pharmacy): View
    {
        $pharmacy->load(['user', 'sourcingRecords', 'purchaseBills' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.pharmacies.show', compact('pharmacy'));
    }

    public function edit(Pharmacy $pharmacy): View
    {
        return view('admin.pharmacies.edit', compact('pharmacy'));
    }

    public function update(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'license_no' => ['required', 'string', 'max:255'],
            'pan_vat_no' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive,suspended,pending'],
        ]);

        $pharmacy->update($validated);

        return to_route('admin.pharmacies.index')->with('success', __('Pharmacy updated successfully.'));
    }

    public function destroy(Pharmacy $pharmacy): RedirectResponse
    {
        $pharmacy->delete();

        return to_route('admin.pharmacies.index')->with('success', __('Pharmacy deleted successfully.'));
    }
}
