@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Reward Transaction Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reward-transactions.index') }}">Reward Transactions</a></li>
                        <li class="breadcrumb-item active">Txn #{{ $rewardTransaction->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-exchange-alt fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Transaction #{{ $rewardTransaction->id }}</h5>
                        <p class="text-muted mb-0">{{ $rewardTransaction->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Type</span>
                            <span class="badge bg-info-subtle text-info">{{ $rewardTransaction->transaction_type ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @php $sColors = ['completed' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'failed' => 'bg-danger-subtle text-danger']; @endphp
                            <span class="badge {{ $sColors[$rewardTransaction->status] ?? 'bg-warning-subtle text-warning' }}">{{ $rewardTransaction->status ?? 'pending' }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Points Earned</span>
                            <span class="fw-bold fs-5">{{ number_format($rewardTransaction->points_earned ?? 0, 2) }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Bonus</span>
                            <span>{{ number_format($rewardTransaction->bonus_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Royalty</span>
                            <span>{{ number_format($rewardTransaction->royalty_amount ?? 0, 2) }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">User</span>
                            <span class="fw-medium">{{ $rewardTransaction->user?->name ?? '—' }}</span>
                        </div>
                        @if ($rewardTransaction->purchaseBill)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Purchase Bill</span>
                                <span><a href="{{ route('admin.purchase-bills.show', $rewardTransaction->purchaseBill) }}" class="text-primary">{{ $rewardTransaction->purchaseBill->invoice_number ?? '#' . $rewardTransaction->purchaseBill->id }}</a></span>
                            </div>
                        @endif
                        @if ($rewardTransaction->rewardRule)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Reward Rule</span>
                                <span><a href="{{ route('admin.reward-rules.show', $rewardTransaction->rewardRule) }}" class="text-primary">{{ $rewardTransaction->rewardRule->name }}</a></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-coins fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Transaction #{{ $rewardTransaction->id }}</span>
                <span class="text-primary fw-normal">earnings breakdown</span>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-1 fs-13">Points</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($rewardTransaction->points_earned ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-1 fs-13">Bonus</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($rewardTransaction->bonus_amount ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-1 fs-13">Royalty</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($rewardTransaction->royalty_amount ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

