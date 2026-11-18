@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Pharmacy Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.pharmacies.index') }}">Pharmacies</a></li>
                        <li class="breadcrumb-item active">{{ $pharmacy->store_name }}</li>
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
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                        <i class="las la-pharmacy fs-1"></i>
                                    </div>
                                    @if ($pharmacy->status === 'active')
                                        <div class="position-absolute top-50 start-100 translate-middle">
                                            <span class="rounded-circle border border-3 border-white d-inline-block bg-success" style="width: 18px; height: 18px;"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $pharmacy->store_name }}</h5>
                                    <p class="text-muted mb-0">{{ $pharmacy->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="iconoir-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php $sColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary', 'suspended' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                                    <span class="badge {{ $sColors[$pharmacy->status] ?? 'bg-warning-subtle text-warning' }}">{{ ucfirst($pharmacy->status) }}</span>
                                </div>
                                @if ($pharmacy->license_no)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-id-card fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">License :</span>
                                        <span>{{ $pharmacy->license_no }}</span>
                                    </div>
                                @endif
                                @if ($pharmacy->pan_vat_no)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-database fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">PAN/VAT :</span>
                                        <span>{{ $pharmacy->pan_vat_no }}</span>
                                    </div>
                                @endif
                                @if ($pharmacy->owner_name)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-user fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Owner :</span>
                                        <span>{{ $pharmacy->owner_name }}</span>
                                    </div>
                                @endif
                                @if ($pharmacy->district)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-map-pin fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">District :</span>
                                        <span>{{ $pharmacy->district }}</span>
                                    </div>
                                @endif
                                @if ($pharmacy->address)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-navigate fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Address :</span>
                                        <span>{{ $pharmacy->address }}</span>
                                    </div>
                                @endif
                                @if ($pharmacy->approved_at)
                                    <div class="text-body d-flex align-items-center">
                                        <i class="iconoir-calendar fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Approved :</span>
                                        <span>{{ $pharmacy->approved_at->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('admin.pharmacies.edit', $pharmacy) }}" class="btn btn-primary btn-sm"><i class="las la-edit me-1"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" class="d-inline" onsubmit="return confirm('Delete this pharmacy?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="las la-trash-alt me-1"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-chart-bar fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $pharmacy->store_name }}</span>
                <span class="text-primary fw-normal">activity overview</span>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-receipt fs-24 align-self-center text-info me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Purchase Bills</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $pharmacy->purchaseBills->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-truck fs-24 align-self-center text-success me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Sourcing Records</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $pharmacy->sourcingRecords->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-user fs-24 align-self-center text-primary me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Owner</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold fs-14">{{ $pharmacy->owner_name ?? '—' }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($pharmacy->purchaseBills->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Recent Purchase Bills</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.purchase-bills.index') }}" class="btn btn-primary btn-sm"><i class="las la-eye me-1"></i> View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Amount</th>
                                        <th>OCR Status</th>
                                        <th>Validation</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pharmacy->purchaseBills as $bill)
                                        <tr>
                                            <td><a href="{{ route('admin.purchase-bills.show', $bill) }}" class="text-primary">{{ $bill->invoice_number ?? '#' . $bill->id }}</a></td>
                                            <td>Rs {{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                            <td>
                                                @php $oClasses = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                                                <span class="badge {{ $oClasses[$bill->ocr_status] ?? 'bg-secondary-subtle text-secondary' }}">{{ $bill->ocr_status ?? 'pending' }}</span>
                                            </td>
                                            <td>
                                                @php $vClasses = ['verified' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger']; @endphp
                                                <span class="badge {{ $vClasses[$bill->validation_status] ?? 'bg-secondary-subtle text-secondary' }}">{{ $bill->validation_status ?? 'pending' }}</span>
                                            </td>
                                            <td>{{ $bill->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

