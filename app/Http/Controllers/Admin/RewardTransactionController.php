<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = RewardTransaction::with(['user', 'purchaseBill', 'rewardRule']);

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $transactions = $query->latest()->get();

        return view('admin.reward-transactions.index', compact('transactions'));
    }

    public function show(RewardTransaction $rewardTransaction): View
    {
        $rewardTransaction->load(['user', 'purchaseBill', 'rewardRule']);

        return view('admin.reward-transactions.show', compact('rewardTransaction'));
    }
}
