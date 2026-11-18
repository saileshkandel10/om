<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Reward\RedeemRewardRequest;
use App\Http\Resources\Api\V1\RewardRedemptionResource;
use App\Http\Resources\Api\V1\RewardTransactionResource;
use App\Http\Resources\Api\V1\RewardWalletResource;
use App\Models\RewardRedemption;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Get the authenticated user's reward wallet balance.
     */
    public function wallet(Request $request): JsonResponse
    {
        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        return response()->json([
            'wallet' => new RewardWalletResource($wallet),
        ]);
    }

    /**
     * List the authenticated user's reward transactions.
     */
    public function transactions(Request $request): AnonymousResourceCollection
    {
        $transactions = $request->user()
            ->rewardTransactions()
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return RewardTransactionResource::collection($transactions);
    }

    /**
     * Request a reward redemption.
     */
    public function redeem(RedeemRewardRequest $request): JsonResponse
    {
        $user = $request->user();
        $wallet = RewardWallet::where('user_id', $user->id)->first();
        $pointsUsed = (float) $request->validated('points_used');

        if (! $wallet || $wallet->total_points < $pointsUsed) {
            return response()->json([
                'message' => 'Insufficient points for redemption.',
            ], 422);
        }

        $redemptionCode = RewardRedemption::generateRedemptionCode();

        $redemption = DB::transaction(function () use ($user, $wallet, $pointsUsed, $redemptionCode, $request) {
            $wallet->decrement('total_points', $pointsUsed);

            RewardTransaction::create([
                'user_id' => $user->id,
                'points_earned' => -$pointsUsed,
                'bonus_amount' => 0,
                'royalty_amount' => 0,
                'transaction_type' => 'redemption',
                'status' => 'completed',
            ]);

            return RewardRedemption::create([
                'user_id' => $user->id,
                'redemption_code' => $redemptionCode,
                'redemption_type' => $request->validated('redemption_type'),
                'points_used' => $pointsUsed,
                'amount_value' => $pointsUsed, // 1:1 point-to-value ratio
                'status' => 'approved',
                'requested_at' => now(),
                'approved_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Points redeemed successfully! Your redemption & lucky draw code is '.$redemptionCode,
            'redemption_code' => $redemptionCode,
            'lucky_draw_code' => $redemptionCode,
            'redemption' => new RewardRedemptionResource($redemption),
        ], 201);
    }

    /**
     * List the authenticated user's redemption history.
     */
    public function redemptions(Request $request): AnonymousResourceCollection
    {
        $redemptions = $request->user()
            ->rewardRedemptions()
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return RewardRedemptionResource::collection($redemptions);
    }
}
