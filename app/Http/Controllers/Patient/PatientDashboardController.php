<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientDashboardController extends Controller
{
    /**
     * Display the patient / customer dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $wallet = RewardWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0]
        );

        $totalBills = PurchaseBill::where('user_id', $user->id)->count();
        $approvedBills = PurchaseBill::where('user_id', $user->id)
            ->where('validation_status', 'approved')
            ->count();
        $pendingBills = PurchaseBill::where('user_id', $user->id)
            ->whereIn('ocr_status', ['pending', 'processing'])
            ->count();

        $recentBills = PurchaseBill::where('user_id', $user->id)
            ->with('pharmacy')
            ->latest()
            ->take(5)
            ->get();

        $recentTransactions = RewardTransaction::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $featuredProducts = Product::where('is_active', true)
            ->with('category')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboards.patient', compact(
            'user',
            'wallet',
            'totalBills',
            'approvedBills',
            'pendingBills',
            'recentBills',
            'recentTransactions',
            'featuredProducts'
        ));
    }
}
