<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Doctor\StorePrescriptionRequest;
use App\Http\Resources\Api\V1\DoctorResource;
use App\Http\Resources\Api\V1\PrescriptionResource;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\RewardWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * Doctor dashboard with KPIs.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        $totalPrescriptions = $doctor?->prescriptions()->count() ?? 0;

        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        return response()->json([
            'doctor' => $doctor ? new DoctorResource($doctor) : null,
            'total_prescriptions' => $totalPrescriptions,
            'total_points' => $wallet->total_points,
            'total_bonus' => $wallet->total_bonus,
        ]);
    }

    /**
     * List the doctor's prescriptions.
     */
    public function prescriptions(Request $request): AnonymousResourceCollection
    {
        $doctor = Doctor::where('user_id', $request->user()->id)->first();

        if (! $doctor) {
            return PrescriptionResource::collection(collect());
        }

        return PrescriptionResource::collection(
            $doctor->prescriptions()
                ->with('items.product')
                ->latest()
                ->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * Create a new prescription (manual entry or image upload).
     */
    public function storePrescription(StorePrescriptionRequest $request): JsonResponse
    {
        $user = $request->user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (! $doctor) {
            return response()->json([
                'message' => 'Doctor profile not found. Please complete your profile first.',
            ], 422);
        }

        $prescription = DB::transaction(function () use ($request, $doctor, $user) {
            $uploadedFileId = null;

            if ($request->hasFile('prescription_image')) {
                $file = $request->file('prescription_image');
                $path = $file->store('prescriptions/'.$user->id, 'public');

                $uploadedFile = $user->uploadedFiles()->create([
                    'module_name' => 'prescription',
                    'file_type' => 'prescription',
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_at' => now(),
                ]);

                $uploadedFileId = $uploadedFile->id;
            }

            $prescription = Prescription::create([
                'doctor_id' => $doctor->id,
                'patient_ref_code' => $request->validated('patient_ref_code'),
                'prescription_date' => $request->validated('prescription_date'),
                'source_type' => $request->validated('source_type'),
                'uploaded_file_id' => $uploadedFileId,
                'parsed_status' => $request->validated('source_type') === 'manual' ? 'completed' : 'pending',
                'notes' => $request->validated('notes'),
            ]);

            if ($request->validated('source_type') === 'manual' && $request->has('items')) {
                foreach ($request->validated('items') as $item) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        ...$item,
                    ]);
                }
            }

            return $prescription;
        });

        $prescription->load('items.product');

        return response()->json([
            'message' => 'Prescription created successfully.',
            'prescription' => new PrescriptionResource($prescription),
        ], 201);
    }

    /**
     * Show a specific prescription.
     */
    public function showPrescription(Request $request, Prescription $prescription): PrescriptionResource
    {
        $doctor = Doctor::where('user_id', $request->user()->id)->first();

        if (! $doctor || $prescription->doctor_id !== $doctor->id) {
            abort(403, 'You do not have access to this prescription.');
        }

        $prescription->load('items.product');

        return new PrescriptionResource($prescription);
    }
}
