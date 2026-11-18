@extends('layouts.shareholder')

@section('title', __('Pharmacy Dashboard'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Pharmacy Dashboard</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">{{ config('system.name') }}</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
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

        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                            <i class="iconoir-wallet fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Reward Points</p>
                            <h4 class="mb-0 fw-semibold">{{ number_format($wallet->total_points, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center">
                            <i class="iconoir-gift fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Bonus Credits</p>
                            <h4 class="mb-0 fw-semibold">{{ number_format($wallet->total_bonus, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center">
                            <i class="iconoir-delivery-truck fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Sourcing Records</p>
                            <h4 class="mb-0 fw-semibold">{{ $totalSourcing }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-purple-subtle text-purple d-flex align-items-center justify-content-center">
                            <i class="iconoir-shop fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Store Status</p>
                            <h5 class="mb-0 fw-semibold text-capitalize text-success">{{ $pharmacy->status ?? 'Approved' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role-Based Advertisement Banner Carousel (Below Loyalty Wallet Balance) --}}
        <x-advertisement-banner role="pharmacy" />

        <div class="row">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Recent Sourcing & Inventory Records</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product / Medicine</th>
                                        <th>Batch #</th>
                                        <th>Qty</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSourcing as $record)
                                        <tr>
                                            <td><span class="ps-2 fw-medium">{{ $record->product?->brand_name ?? 'OM Medicine' }}</span></td>
                                            <td>{{ $record->batch_number ?? '-' }}</td>
                                            <td>{{ $record->quantity ?? 0 }}</td>
                                            <td>{{ $record->sourcing_date ? \Carbon\Carbon::parse($record->sourcing_date)->format('M d, Y') : '-' }}</td>
                                            <td><span class="badge bg-success-subtle text-success">Verified</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No sourcing records registered yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Pharmacy Details</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width: 40%;">Store Name:</th>
                                <td class="fw-semibold">{{ $pharmacy->store_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">PAN / VAT:</th>
                                <td>{{ $pharmacy->pan_vat_no ?? $pharmacy->pan_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">License #:</th>
                                <td>{{ $pharmacy->license_no ?? $pharmacy->license_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Contact Person:</th>
                                <td>{{ $pharmacy->contact_person ?? $pharmacy->owner_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Address:</th>
                                <td>{{ $pharmacy->address ?? 'Kathmandu, Nepal' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
