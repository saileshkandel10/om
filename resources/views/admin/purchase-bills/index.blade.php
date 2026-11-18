@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                    <div>
                        <h4 class="page-title fw-bold text-dark mb-1">Purchase Bills & OCR Management</h4>
                        <p class="text-muted small mb-0">Review, verify, and audit scanned invoices and purchase bills (Threshold ≥ Rs 5,000 requires manual approval).</p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title fw-bold">All Uploaded Purchase Bills</h4>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-light text-dark fs-12 px-3 py-2 border">{{ $bills->count() }} total bills</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.purchase-bills.index') }}" class="row gy-2 gx-3 mb-4 align-items-end p-3 bg-light rounded-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Search Invoice</label>
                                <input type="search" name="search" class="form-control" placeholder="Search invoice #..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">OCR Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="processing" @selected(request('status') === 'processing')>Processing</option>
                                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="fraudFlag" name="fraud_flag" value="1" @checked(request('fraud_flag')) onchange="this.form.submit()">
                                    <label class="form-check-label fw-semibold text-danger" for="fraudFlag">Fraud Flagged Only</label>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button type="submit" class="btn btn-primary px-3" style="background-color: #CD0B6F; border-color: #CD0B6F;">Filter</button>
                                <a href="{{ route('admin.purchase-bills.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive" style="overflow: visible !important;">
                            <table class="table table-hover align-middle mb-0" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice No.</th>
                                        <th>User</th>
                                        <th>Pharmacy</th>
                                        <th>Total Amount</th>
                                        <th>OCR Status</th>
                                        <th>Validation</th>
                                        <th>Fraud Check</th>
                                        <th>Date</th>
                                        <th class="text-end">Action / Verify</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bills as $bill)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.purchase-bills.show', $bill) }}" class="fw-bold text-dark">
                                                    <code>{{ $bill->invoice_number ?? 'INV-'.$bill->id }}</code>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $bill->user?->name ?? '—' }}</div>
                                                <small class="text-muted">{{ $bill->user?->email }}</small>
                                            </td>
                                            <td>{{ $bill->pharmacy_name ?? $bill->pharmacy?->store_name ?? '—' }}</td>
                                            <td><span class="fw-bold text-success">Rs {{ number_format($bill->total_amount, 2) }}</span></td>
                                            <td>
                                                @php
                                                    $statusColor = match ($bill->ocr_status) {
                                                        'completed' => 'success',
                                                        'processing' => 'info',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-2 py-1 text-capitalize">{{ $bill->ocr_status ?? 'pending' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $valColor = match ($bill->validation_status) {
                                                        'approved' => 'success',
                                                        'pending' => 'warning',
                                                        'rejected' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $valColor }}-subtle text-{{ $valColor }} px-2 py-1 text-capitalize">{{ $bill->validation_status ?? 'pending' }}</span>
                                            </td>
                                            <td>
                                                @if ($bill->fraud_flag)
                                                    <span class="badge bg-danger-subtle text-danger"><i class="las la-exclamation-triangle me-1"></i> Flagged</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success">Clean</span>
                                                @endif
                                            </td>
                                            <td>{{ $bill->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1 align-items-center">
                                                    <a href="{{ route('admin.purchase-bills.show', $bill) }}" class="btn btn-sm btn-light shadow-sm" title="View Details">
                                                        <i class="las la-eye text-primary"></i>
                                                    </a>
                                                    @if ($bill->validation_status !== 'approved')
                                                        <form method="POST" action="{{ route('admin.purchase-bills.verify', $bill) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success shadow-sm px-2 fw-semibold" title="Manually Verify and Approve">
                                                                <i class="las la-check-circle me-1"></i> Verify
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.purchase-bills.destroy', $bill) }}" class="d-inline" onsubmit="return confirm('Delete this purchase bill?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light text-danger shadow-sm" title="Delete">
                                                            <i class="las la-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="las la-receipt fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                                No purchase bills recorded yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
