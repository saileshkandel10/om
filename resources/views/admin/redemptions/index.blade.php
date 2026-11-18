@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Redemptions</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Rewards & Loyalty</a></li>
                            <li class="breadcrumb-item active">Redemptions</li>
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
                                <h4 class="card-title">All Redemptions</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $redemptions->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.redemptions.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Code / User..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="cashback" @selected(request('type') === 'cashback')>Cashback</option>
                                    <option value="voucher" @selected(request('type') === 'voucher')>Voucher</option>
                                    <option value="product" @selected(request('type') === 'product')>Product</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.redemptions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Redemption / Lucky Draw Code</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Points Used</th>
                                        <th>Amount Value</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                        <th>Approved</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($redemptions as $redemption)
                                        <tr>
                                            <td><code>#{{ $redemption->id }}</code></td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-purple-subtle text-purple font-monospace fs-12 px-2 py-1">
                                                        <i class="las la-ticket-alt me-1"></i>{{ $redemption->redemption_code ?? 'RDM-'.$redemption->id }}
                                                    </span>
                                                    <span class="text-muted fs-11 mt-1"><i class="las la-gift me-1 text-warning"></i>Lucky Draw Eligible</span>
                                                </div>
                                            </td>
                                            <td>{{ $redemption->user?->name ?? '—' }}</td>
                                            <td><span class="badge bg-primary">{{ ucfirst($redemption->redemption_type) }}</span></td>
                                            <td><strong>{{ number_format($redemption->points_used, 2) }} pts</strong></td>
                                            <td>Rs {{ number_format($redemption->amount_value, 2) }}</td>
                                            <td>
                                                @php($statusColor = match ($redemption->status) {
                                                    'completed' => 'success',
                                                    'approved' => 'info',
                                                    'rejected' => 'danger',
                                                    'pending' => 'warning',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $redemption->status }}</span>
                                            </td>
                                            <td>{{ $redemption->requested_at?->format('M d, Y') ?? '—' }}</td>
                                            <td>{{ $redemption->approved_at?->format('M d, Y') ?? '—' }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.redemptions.show', $redemption) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">No redemptions found.</td>
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

