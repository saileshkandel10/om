<?php

namespace App\Http\Controllers\Shareholder;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Notice;
use App\Models\PurchaseBill;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;

class ShareholderDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('shareholderAccount.master');

        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0],
        );

        $recentRewards = RewardTransaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('purchaseBill')
            ->latest()
            ->take(5)
            ->get();

        $totalBills = PurchaseBill::where('user_id', $user->id)->count();

        $recentBills = PurchaseBill::where('user_id', $user->id)
            ->with('pharmacy')
            ->latest()
            ->take(5)
            ->get();

        $notices = Notice::where('status', 'published')
            ->where(function ($q) {
                $q->where('audience_type', 'shareholder')
                    ->orWhere('audience_type', 'all');
            })
            ->latest('published_at')
            ->take(5)
            ->get();

        $campaigns = Campaign::where('status', 'active')
            ->where(function ($q) {
                $q->where('target_segment', 'shareholder')
                    ->orWhere('target_segment', 'all');
            })
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->take(4)
            ->get();

        return view('dashboards.shareholder', compact(
            'wallet',
            'recentRewards',
            'totalBills',
            'recentBills',
            'notices',
            'campaigns',
        ));
    }
}
