<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Prescription::with(['doctor.user']);

        if ($request->filled('status')) {
            $query->where('parsed_status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('search')) {
            $query->where('patient_ref_code', 'like', "%{$request->search}%");
        }

        $prescriptions = $query->latest()->get();

        return view('admin.prescriptions.index', compact('prescriptions'));
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['doctor.user', 'items.product', 'items.illnessCategory', 'uploadedFile']);

        return view('admin.prescriptions.show', compact('prescription'));
    }

    public function destroy(Prescription $prescription): RedirectResponse
    {
        $prescription->delete();

        return to_route('admin.prescriptions.index')->with('success', __('Prescription deleted successfully.'));
    }
}
