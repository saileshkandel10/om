@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Sourcing Record Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.sourcing-records.index') }}">Sourcing Records</a></li>
                        <li class="breadcrumb-item active">Record #{{ $sourcingRecord->id }}</li>
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
                            <i class="las la-truck fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Sourcing Record #{{ $sourcingRecord->id }}</h5>
                        <p class="text-muted mb-0">{{ $sourcingRecord->supplier_name }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Supplier</span>
                            <span class="fw-medium">{{ $sourcingRecord->supplier_name ?? '—' }}</span>
                        </div>
                        @if ($sourcingRecord->source_invoice_no)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Source Invoice</span>
                                <span>{{ $sourcingRecord->source_invoice_no }}</span>
                            </div>
                        @endif
                        @if ($sourcingRecord->source_date)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Source Date</span>
                                <span>{{ $sourcingRecord->source_date->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Pharmacy</span>
                            <span class="fw-medium">{{ $sourcingRecord->pharmacy?->store_name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">License</span>
                            <span>{{ $sourcingRecord->pharmacy?->license_no ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-truck fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Sourcing Record #{{ $sourcingRecord->id }}</span>
                <span class="text-primary fw-normal">details</span>
            </div>
            @if ($sourcingRecord->notes)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Notes</h4>
                    </div>
                    <div class="card-body pt-0">
                        <p class="bg-light p-3 rounded mb-0">{{ $sourcingRecord->notes }}</p>
                    </div>
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Pharmacy Details</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.pharmacies.show', $sourcingRecord->pharmacy) }}" class="btn btn-primary btn-sm"><i class="las la-eye me-1"></i> View Pharmacy</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Store Name</th>
                                    <th>Owner</th>
                                    <th>District</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $sourcingRecord->pharmacy?->store_name ?? '—' }}</td>
                                    <td>{{ $sourcingRecord->pharmacy?->owner_name ?? '—' }}</td>
                                    <td>{{ $sourcingRecord->pharmacy?->district ?? '—' }}</td>
                                    <td>
                                        @php $sColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary', 'suspended' => 'bg-danger-subtle text-danger']; @endphp
                                        <span class="badge {{ $sColors[$sourcingRecord->pharmacy?->status] ?? 'bg-warning-subtle text-warning' }}">{{ $sourcingRecord->pharmacy?->status ?? '—' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

