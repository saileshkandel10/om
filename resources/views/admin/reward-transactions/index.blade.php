@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Reward Transactions</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Rewards & Loyalty</a></li>
                            <li class="breadcrumb-item active">Transactions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Reward Transactions</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $transactions->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.reward-transactions.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="earned" @selected(request('type') === 'earned')>Earned</option>
                                    <option value="redeemed" @selected(request('type') === 'redeemed')>Redeemed</option>
                                    <option value="expired" @selected(request('type') === 'expired')>Expired</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.reward-transactions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Points</th>
                                        <th>Bonus</th>
                                        <th>Royalty</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactions as $txn)
                                        <tr>
                                            <td><code>#{{ $txn->id }}</code></td>
                                            <td>{{ $txn->user?->name ?? '—' }}</td>
                                            <td><span class="badge bg-info">{{ ucfirst($txn->transaction_type) }}</span></td>
                                            <td>{{ number_format($txn->points_earned, 2) }}</td>
                                            <td>{{ $txn->bonus_amount ? number_format($txn->bonus_amount, 2) : '—' }}</td>
                                            <td>{{ $txn->royalty_amount ? number_format($txn->royalty_amount, 2) : '—' }}</td>
                                            <td>
                                                @php($statusColor = match ($txn->status) {
                                                    'completed' => 'success',
                                                    'pending' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $txn->status }}</span>
                                            </td>
                                            <td data-sort="{{ $txn->created_at->format('Y/m/d') }}">{{ $txn->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reward-transactions.show', $txn) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">No transactions found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

