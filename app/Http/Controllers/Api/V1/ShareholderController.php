<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shareholder\VerifyBoidRequest;
use App\Http\Resources\Api\V1\ShareholderAccountResource;
use App\Models\BoidValidationLog;
use App\Models\PurchaseBill;
use App\Models\RewardWallet;
use App\Models\ShareholderAccount;
use App\Models\ShareholderMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareholderController extends Controller
{
    /**
     * Shareholder dashboard with KPIs.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalBills = PurchaseBill::where('user_id', $user->id)->count();
        $approvedBills = PurchaseBill::where('user_id', $user->id)
            ->where('validation_status', 'approved')
            ->count();
        $pendingBills = PurchaseBill::where('user_id', $user->id)
            ->where('ocr_status', 'pending')
            ->orWhere(fn ($q) => $q->where('user_id', $user->id)->where('ocr_status', 'processing'))
            ->count();

        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        $account = ShareholderAccount::where('user_id', $user->id)->first();

        return response()->json([
            'total_bills' => $totalBills,
            'approved_bills' => $approvedBills,
            'pending_bills' => $pendingBills,
            'total_points' => $wallet->total_points,
            'total_bonus' => $wallet->total_bonus,
            'total_royalty' => $wallet->total_royalty,
            'boid_verified' => $account?->verification_status === 'verified',
        ]);
    }

    /**
     * Verify BOID against the shareholder master records.
     */
    public function verifyBoid(VerifyBoidRequest $request): JsonResponse
    {
        $user = $request->user();
        $boid = $request->validated('boid');

        $master = ShareholderMaster::where('boid', $boid)->first();

        BoidValidationLog::create([
            'requested_by_user_id' => $user->id,
            'boid' => $boid,
            'result' => $master ? 'matched' : 'not_found',
            'message' => $master ? 'BOID matched to shareholder master record.' : 'BOID not found in master records.',
        ]);

        if (! $master) {
            return response()->json([
                'message' => 'BOID not found in our records. Please contact support.',
                'verified' => false,
            ], 422);
        }

        $account = ShareholderAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'shareholder_master_id' => $master->id,
                'boid' => $boid,
                'shares_owned' => 0,
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'BOID verified successfully.',
            'verified' => true,
            'account' => new ShareholderAccountResource($account->load('master')),
        ]);
    }

    /**
     * Get shareholder account details.
     */
    public function account(Request $request): JsonResponse
    {
        $account = ShareholderAccount::where('user_id', $request->user()->id)
            ->with('master')
            ->first();

        if (! $account) {
            return response()->json([
                'message' => 'No shareholder account found. Please verify your BOID.',
                'account' => null,
            ]);
        }

        return response()->json([
            'account' => new ShareholderAccountResource($account),
        ]);
    }
}
