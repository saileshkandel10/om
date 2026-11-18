@extends('layouts.shareholder')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">My Purchase Bills</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('shareholder.dashboard') }}">{{ config('system.name') }}</a></li>
                        <li class="breadcrumb-item active">Purchase Bills</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">All Bills</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('shareholder.bills.create') }}" class="btn btn-primary btn-sm">
                                <i class="las la-plus me-1"></i> Upload New Bill
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form method="GET" action="{{ route('shareholder.bills.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                        <div class="col-auto">
                            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search invoice..." value="{{ request('search') }}">
                        </div>
                        <div class="col-auto">
                            <select name="ocr_status" class="form-select form-select-sm">
                                <option value="">All OCR Status</option>
                                <option value="pending" @selected(request('ocr_status') === 'pending')>Pending</option>
                                <option value="processing" @selected(request('ocr_status') === 'processing')>Processing</option>
                                <option value="completed" @selected(request('ocr_status') === 'completed')>Completed</option>
                                <option value="failed" @selected(request('ocr_status') === 'failed')>Failed</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="validation_status" class="form-select form-select-sm">
                                <option value="">All Validation</option>
                                <option value="pending" @selected(request('validation_status') === 'pending')>Pending</option>
                                <option value="verified" @selected(request('validation_status') === 'verified')>Verified</option>
                                <option value="rejected" @selected(request('validation_status') === 'rejected')>Rejected</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                            <a href="{{ route('shareholder.bills.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Pharmacy</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>OCR Status</th>
                                    <th>Validation</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bills as $bill)
                                    <tr>
                                        <td>
                                            <code>{{ $bill->invoice_number ?? '#' . $bill->id }}</code>
                                        </td>
                                        <td>{{ $bill->pharmacy?->store_name ?? '—' }}</td>
                                        <td class="fw-semibold">Rs {{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                        <td>{{ $bill->invoice_date?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $ocrColors = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning'];
                                            @endphp
                                            <span class="badge {{ $ocrColors[$bill->ocr_status] ?? 'bg-warning-subtle text-warning' }}">
                                                {{ ucfirst($bill->ocr_status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $valColors = ['verified' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning'];
                                            @endphp
                                            <span class="badge {{ $valColors[$bill->validation_status] ?? 'bg-warning-subtle text-warning' }}">
                                                {{ ucfirst($bill->validation_status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('shareholder.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="las la-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="las la-receipt fs-3 d-block mb-1"></i>
                                            <p class="mb-0">No bills found.</p>
                                            <a href="{{ route('shareholder.bills.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="las la-plus me-1"></i> Upload Your First Bill
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($bills->hasPages())
                        <div class="mt-3">
                            {{ $bills->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
