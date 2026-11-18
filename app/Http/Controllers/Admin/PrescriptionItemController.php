<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = PrescriptionItem::with(['prescription.doctor.user', 'product', 'illnessCategory']);

        if ($request->filled('prescription_id')) {
            $query->where('prescription_id', $request->prescription_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('medicine_name_raw', 'like', "%{$request->search}%")
                    ->orWhere('generic_name', 'like', "%{$request->search}%");
            });
        }

        $items = $query->latest()->get();

        return view('admin.prescription-items.index', compact('items'));
    }

    public function show(PrescriptionItem $prescriptionItem): View
    {
        $prescriptionItem->load(['prescription.doctor.user', 'product', 'illnessCategory']);

        return view('admin.prescription-items.show', compact('prescriptionItem'));
    }
}
