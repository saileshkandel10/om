<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pharmacy\UpdatePharmacyRequest;
use App\Http\Resources\Api\V1\PharmacyResource;
use App\Http\Resources\Api\V1\PharmacySourcingRecordResource;
use App\Models\Pharmacy;
use App\Models\PurchaseBill;
use App\Models\RewardWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PharmacyController extends Controller
{
    /**
     * Pharmacy dashboard with KPIs.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        $totalBills = PurchaseBill::where('pharmacy_id', $pharmacy?->id)->count();

        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        $totalSourcingRecords = $pharmacy?->sourcingRecords()->count() ?? 0;

        return response()->json([
            'store_name' => $pharmacy?->store_name,
            'status' => $pharmacy?->status,
            'total_bills' => $totalBills,
            'total_sourcing_records' => $totalSourcingRecords,
            'total_points' => $wallet->total_points,
            'total_bonus' => $wallet->total_bonus,
            'total_royalty' => $wallet->total_royalty,
        ]);
    }

    /**
     * Get pharmacy store profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $pharmacy = Pharmacy::where('user_id', $request->user()->id)->first();

        if (! $pharmacy) {
            return response()->json([
                'message' => 'No pharmacy profile found.',
                'pharmacy' => null,
            ]);
        }

        return response()->json([
            'pharmacy' => new PharmacyResource($pharmacy),
        ]);
    }

    /**
     * Update pharmacy store profile.
     */
    public function updateProfile(UpdatePharmacyRequest $request): JsonResponse
    {
        $pharmacy = Pharmacy::where('user_id', $request->user()->id)->first();

        if (! $pharmacy) {
            $pharmacy = Pharmacy::create([
                'user_id' => $request->user()->id,
                ...$request->validated(),
                'status' => 'pending',
            ]);
        } else {
            $pharmacy->update($request->validated());
        }

        return response()->json([
            'message' => 'Pharmacy profile updated.',
            'pharmacy' => new PharmacyResource($pharmacy),
        ]);
    }

    /**
     * List pharmacy sourcing records.
     */
    public function sourcingRecords(Request $request): AnonymousResourceCollection
    {
        $pharmacy = Pharmacy::where('user_id', $request->user()->id)->first();

        if (! $pharmacy) {
            return PharmacySourcingRecordResource::collection(collect());
        }

        return PharmacySourcingRecordResource::collection(
            $pharmacy->sourcingRecords()->latest()->paginate($request->integer('per_page', 15))
        );
    }
}
