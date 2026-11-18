<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    /**
     * List active campaigns for the user's segment.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $userRoles = $user->roles->pluck('name')->toArray();

        $query = Campaign::where('status', 'active')
            ->where(function ($q) {
                $q->where('start_date', '<=', now())
                    ->orWhereNull('start_date');
            })
            ->where(function ($q) {
                $q->where('end_date', '>=', now())
                    ->orWhereNull('end_date');
            });

        if (! empty($userRoles)) {
            $query->where(function ($q) use ($userRoles) {
                $q->whereIn('target_segment', $userRoles)
                    ->orWhere('target_segment', 'all')
                    ->orWhereNull('target_segment');
            });
        }

        return CampaignResource::collection(
            $query->latest('start_date')->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * Show a single campaign.
     */
    public function show(Campaign $campaign): CampaignResource
    {
        return new CampaignResource($campaign);
    }
}
