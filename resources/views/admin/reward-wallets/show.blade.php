@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Reward Wallet Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reward-wallets.index') }}">Reward Wallets</a></li>
                        <li class="breadcrumb-item active">{{ $rewardWallet->user?->name ?? 'Wallet #'.$rewardWallet->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body p-4 rounded text-center img-bg"></div>
                <div class="position-relative">
                    <div class="shape overflow-hidden text-card-bg">
                        <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
                        </svg>
                    </div>
                </div>
                <div class="card-body mt-n6">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                        <i class="las la-wallet fs-1"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $rewardWallet->user?->name ?? 'N/A' }}</h5>
                                    <p class="text-muted mb-0">{{ $rewardWallet->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="las la-coins fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Total Points :</span>
                                    <span class="fw-bold fs-4 text-success">{{ number_format($rewardWallet->total_points ?? 0, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Bonus</span>
                                    <span class="fw-medium">{{ number_format($rewardWallet->total_bonus ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Royalty</span>
                                    <span class="fw-medium">{{ number_format($rewardWallet->total_royalty ?? 0, 2) }}</span>
                                </div>
                                <hr>
                                @php $grandTotal = ($rewardWallet->total_points ?? 0) + ($rewardWallet->total_bonus ?? 0) + ($rewardWallet->total_royalty ?? 0); @endphp
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted fw-semibold">Grand Total</span>
                                    <span class="fw-bold">{{ number_format($grandTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-coins fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $rewardWallet->user?->name ?? 'Wallet' }}'s</span>
                <span class="text-primary fw-normal">reward transactions</span>
            </div>
            @php $transactions = $rewardWallet->user?->rewardTransactions ?? collect(); @endphp
            @if ($transactions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Recent Transactions ({{ $transactions->count() }})</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Points</th>
                                        <th>Bonus</th>
                                        <th>Royalty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $txn)
                                        <tr>
                                            <td>{{ $txn->created_at->format('d/m/Y') }}</td>
                                            <td><span class="badge bg-info-subtle text-info">{{ $txn->transaction_type ?? '—' }}</span></td>
                                            <td class="fw-medium">{{ number_format($txn->points_earned ?? 0, 2) }}</td>
                                            <td>{{ number_format($txn->bonus_amount ?? 0, 2) }}</td>
                                            <td>{{ number_format($txn->royalty_amount ?? 0, 2) }}</td>
                                            <td>
                                                @php $sColors = ['completed' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'failed' => 'bg-danger-subtle text-danger']; @endphp
                                                <span class="badge {{ $sColors[$txn->status] ?? 'bg-secondary-subtle text-secondary' }}">{{ $txn->status ?? '—' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-4">
                        <i class="las la-exchange-alt fs-2 d-block mb-2"></i>
                        <span>No transactions recorded yet.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

