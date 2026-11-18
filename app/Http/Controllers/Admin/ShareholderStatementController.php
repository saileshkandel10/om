<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareholderStatementController extends Controller
{
    public function generate(Request $request, User $user): View
    {
        $user->load(['shareholderAccount.master', 'rewardWallet', 'purchaseBills' => fn ($q) => $q->latest()->get()]);
        $wallet = $user->rewardWallet;
        $account = $user->shareholderAccount;
        $master = $account?->master;
        $bills = $user->purchaseBills;

        return view('admin.reports.statement-print', compact('user', 'wallet', 'account', 'master', 'bills'));
    }
}
