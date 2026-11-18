<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\OcrReviewQueue;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\RewardRedemption;
use App\Models\RewardWallet;
use App\Models\ShareholderMaster;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function profile(): View
    {
        $user = Auth::user()->load(['roles', 'userProfile']);

        $totalBills = PurchaseBill::count();
        $totalUsers = User::count();
        $totalRedemptions = RewardRedemption::count();
        $recentAuditLogs = AuditLog::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.profile', compact('user', 'totalBills', 'totalUsers', 'totalRedemptions', 'recentAuditLogs'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return to_route('admin.profile')->with('success', __('Profile updated successfully.'));
    }

    public function index()
    {
        // Core counts
        $totalUsers = User::count();
        $totalPharmacies = Pharmacy::count();
        $totalDoctors = Doctor::count();
        $totalShareholders = ShareholderMaster::count();
        $totalProducts = Product::count();
        $totalPurchaseBills = PurchaseBill::count();

        // OCR reviews
        $pendingOcrReviews = OcrReviewQueue::where('review_status', 'pending')->count();
        $completedOcrReviews = OcrReviewQueue::where('review_status', 'completed')->count();
        $failedOcrReviews = OcrReviewQueue::where('review_status', 'failed')->count();

        // Rewards
        $totalRewardPoints = RewardWallet::sum('total_points');
        $totalBonusPoints = RewardWallet::sum('total_bonus');
        $totalRoyaltyPoints = RewardWallet::sum('total_royalty');
        $totalRewardRedemptions = RewardRedemption::count();
        $pendingRedemptions = RewardRedemption::where('status', 'pending')->count();
        $approvedRedemptions = RewardRedemption::where('status', 'approved')->count();

        // Bills breakdown
        $verifiedBills = PurchaseBill::where('validation_status', 'verified')->count();
        $pendingBills = PurchaseBill::where('validation_status', 'pending')->count();
        $rejectedBills = PurchaseBill::where('validation_status', 'rejected')->count();
        $fraudFlaggedBills = PurchaseBill::where('fraud_flag', true)->count();

        // Monthly bill amounts for chart (last 6 months)
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExpr = $isSqlite
            ? "strftime('%Y-%m', invoice_date)"
            : "DATE_FORMAT(invoice_date, '%Y-%m')";

        $monthlyBillData = PurchaseBill::select(
            DB::raw("{$monthExpr} as month"),
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('invoice_date')
            ->where('invoice_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent records
        $recentPharmacies = Pharmacy::with('user')->latest()->take(5)->get();
        $recentBills = PurchaseBill::with('pharmacy')->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();
        $recentRedemptions = RewardRedemption::with('user')->latest()->take(5)->get();

        // User role distribution
        $userByStatus = User::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Wallet stats
        $walletCount = RewardWallet::count();

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalPharmacies',
            'totalDoctors',
            'totalShareholders',
            'totalProducts',
            'totalPurchaseBills',
            'pendingOcrReviews',
            'completedOcrReviews',
            'failedOcrReviews',
            'totalRewardPoints',
            'totalBonusPoints',
            'totalRoyaltyPoints',
            'totalRewardRedemptions',
            'pendingRedemptions',
            'approvedRedemptions',
            'verifiedBills',
            'pendingBills',
            'rejectedBills',
            'fraudFlaggedBills',
            'monthlyBillData',
            'recentPharmacies',
            'recentBills',
            'recentUsers',
            'recentRedemptions',
            'userByStatus',
            'walletCount',
        ));
    }
}
