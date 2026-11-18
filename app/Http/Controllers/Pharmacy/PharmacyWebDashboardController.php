<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PurchaseBill;
use App\Models\RewardWallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyWebDashboardController extends Controller
{
    /**
     * Display the pharmacy web dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        $totalBills = PurchaseBill::where('pharmacy_id', $pharmacy?->id)->count();
        $totalSourcing = $pharmacy?->sourcingRecords()->count() ?? 0;
        $recentSourcing = $pharmacy?->sourcingRecords()->with('product')->latest()->take(5)->get() ?? collect();

        $recentBills = PurchaseBill::where('pharmacy_id', $pharmacy?->id)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboards.pharmacy', compact(
            'user',
            'pharmacy',
            'wallet',
            'totalBills',
            'totalSourcing',
            'recentSourcing',
            'recentBills'
        ));
    }
}
