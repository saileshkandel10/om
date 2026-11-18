<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AdvertisementResource;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdvertisementController extends Controller
{
    /**
     * Get active role-based advertisements for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $targetRole = 'all';

        if ($user) {
            if ($user->hasRole('doctor')) {
                $targetRole = 'doctor';
            } elseif ($user->hasRole('shareholder')) {
                $targetRole = 'shareholder';
            } elseif ($user->hasRole('pharmacy')) {
                $targetRole = 'pharmacy';
            } elseif ($user->hasRole('customer') || $user->hasRole('patient')) {
                $targetRole = 'patient';
            }
        }

        $advertisements = Advertisement::active()
            ->forRole($targetRole)
            ->orderBy('display_order', 'asc')
            ->orderByDesc('id')
            ->get();

        return AdvertisementResource::collection($advertisements);
    }

    /**
     * Record an impression for an advertisement.
     */
    public function recordImpression(Advertisement $advertisement): JsonResponse
    {
        $advertisement->recordImpression();

        return response()->json([
            'success' => true,
            'message' => 'Impression recorded successfully.',
            'impressions_count' => $advertisement->impressions_count,
        ]);
    }

    /**
     * Record a click for an advertisement.
     */
    public function recordClick(Advertisement $advertisement): JsonResponse
    {
        $advertisement->recordClick();

        return response()->json([
            'success' => true,
            'message' => 'Click recorded successfully.',
            'clicks_count' => $advertisement->clicks_count,
            'ctr' => $advertisement->ctr,
        ]);
    }
}
