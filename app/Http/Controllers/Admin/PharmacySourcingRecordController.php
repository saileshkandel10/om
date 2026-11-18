<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PharmacySourcingRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacySourcingRecordController extends Controller
{
    public function index(Request $request): View
    {
        $query = PharmacySourcingRecord::with('pharmacy');

        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        if ($request->filled('search')) {
            $query->where('supplier_name', 'like', "%{$request->search}%")
                ->orWhere('source_invoice_no', 'like', "%{$request->search}%");
        }

        $records = $query->latest()->get();

        return view('admin.sourcing-records.index', compact('records'));
    }

    public function create(): View
    {
        $pharmacies = Pharmacy::pluck('store_name', 'id');

        return view('admin.sourcing-records.create', compact('pharmacies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pharmacy_id' => ['required', 'exists:pharmacies,id'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'source_invoice_no' => ['nullable', 'string', 'max:255'],
            'source_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        PharmacySourcingRecord::create($validated);

        return to_route('admin.sourcing-records.index')->with('success', __('Sourcing record created successfully.'));
    }

    public function show(PharmacySourcingRecord $sourcingRecord): View
    {
        $sourcingRecord->load('pharmacy');

        return view('admin.sourcing-records.show', compact('sourcingRecord'));
    }

    public function edit(PharmacySourcingRecord $sourcingRecord): View
    {
        $pharmacies = Pharmacy::pluck('store_name', 'id');

        return view('admin.sourcing-records.edit', compact('sourcingRecord', 'pharmacies'));
    }

    public function update(Request $request, PharmacySourcingRecord $sourcingRecord): RedirectResponse
    {
        $validated = $request->validate([
            'pharmacy_id' => ['required', 'exists:pharmacies,id'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'source_invoice_no' => ['nullable', 'string', 'max:255'],
            'source_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $sourcingRecord->update($validated);

        return to_route('admin.sourcing-records.index')->with('success', __('Sourcing record updated successfully.'));
    }

    public function destroy(PharmacySourcingRecord $sourcingRecord): RedirectResponse
    {
        $sourcingRecord->delete();

        return to_route('admin.sourcing-records.index')->with('success', __('Sourcing record deleted successfully.'));
    }
}
