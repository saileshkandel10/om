<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardWallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardWalletController extends Controller
{
    public function index(Request $request): View
    {
        $query = RewardWallet::with('user');

        if ($request->filled('search')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"));
        }

        $wallets = $query->latest()->get();

        return view('admin.reward-wallets.index', compact('wallets'));
    }

    public function show(RewardWallet $rewardWallet): View
    {
        $rewardWallet->load(['user', 'user.rewardTransactions' => fn ($q) => $q->latest()->limit(20)]);

        return view('admin.reward-wallets.show', compact('rewardWallet'));
    }
}
