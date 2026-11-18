@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Fraud Detection</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">OCR & Bills</a></li>
                            <li class="breadcrumb-item active">Fraud Detection</li>
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
                                <h4 class="card-title">Flagged Bills</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $flaggedBills->count() }} flagged</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.fraud-detection') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search invoice..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.fraud-detection') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice No.</th>
                                        <th>User</th>
                                        <th>Pharmacy</th>
                                        <th>Total Amount</th>
                                        <th>OCR Status</th>
                                        <th>Fraud Flag</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($flaggedBills as $bill)
                                        <tr class="table-danger">
                                            <td><code>{{ $bill->invoice_number }}</code></td>
                                            <td>{{ $bill->user?->name ?? '—' }}</td>
                                            <td>{{ $bill->pharmacy?->store_name ?? '—' }}</td>
                                            <td class="fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
                                            <td>
                                                @php($statusColor = match ($bill->ocr_status) {
                                                    'completed' => 'success',
                                                    'processing' => 'info',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $bill->ocr_status ?? 'pending' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger"><i class="las la-exclamation-triangle me-1"></i>Flagged</span>
                                            </td>
                                            <td data-sort="{{ $bill->created_at->format('Y/m/d') }}">{{ $bill->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.purchase-bills.show', $bill) }}">Review</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="las la-check-circle fs-4 text-success me-1"></i> No flagged bills found.
                                            </td>
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

