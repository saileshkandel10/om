@extends('layouts.admin')

@section('title', __('Admin Dashboard'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Executive Administration Dashboard</h4>
                    <p class="text-muted small mb-0">OM Connect • Om Megashree Pharmaceuticals Limited ("स्वास्थ्य र राष्ट्रका लागि एक सङ्कल्प")</p>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <a href="{{ route('admin.advertisements.create') }}" class="btn btn-sm text-white shadow-sm px-3 py-2 fw-semibold" style="background-color: #CD0B6F; border-color: #CD0B6F; border-radius: 10px;">
                        <i class="las la-ad me-1"></i> New Ad Banner
                    </a>
                    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-sm btn-light bg-white shadow-sm px-3 py-2 fw-semibold text-dark border" style="border-radius: 10px;">
                        <i class="las la-images me-1 text-brand"></i> Manage Banners
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light bg-white shadow-sm px-3 py-2 fw-semibold text-dark border" style="border-radius: 10px;">
                        <i class="iconoir-group me-1"></i> Users
                    </a>
                    <a href="{{ route('admin.purchase-bills.index') }}" class="btn btn-sm btn-light bg-white shadow-sm px-3 py-2 fw-semibold text-dark border" style="border-radius: 10px;">
                        <i class="iconoir-receipt me-1"></i> OCR Bills
                    </a>
                    <a href="{{ route('admin.analytics.reports') }}" class="btn btn-sm btn-light bg-white shadow-sm px-3 py-2 fw-semibold text-dark border" style="border-radius: 10px;">
                        <i class="iconoir-reports me-1"></i> Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($fraudFlaggedBills > 0 || $pendingOcrReviews > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between p-3" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="iconoir-warning-triangle fs-5"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Attention Required</h6>
                            <p class="mb-0 small text-muted">You have <strong>{{ $pendingOcrReviews }} pending OCR reviews</strong> and <strong>{{ $fraudFlaggedBills }} fraud-flagged bills</strong> awaiting moderation.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.purchase-bills.index') }}?fraud_flag=1" class="btn btn-sm btn-warning fw-bold px-3">Review Now</a>
                </div>
            </div>
        </div>
    @endif

    {{-- Role-Based Advertisement Banner Carousel --}}
    <x-advertisement-banner role="all" />

    {{-- Stat Cards Row --}}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Total Users</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalUsers) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 text-white d-flex align-items-center justify-content-center shadow-sm" style="background-color: #CD0B6F; width: 48px; height: 48px;">
                            <i class="iconoir-user fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-success fw-semibold"><i class="iconoir-arrow-up"></i> Active</span> accounts in system</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Pharmacies</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalPharmacies) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-info-subtle text-info d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-first-aid-kit fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-info fw-semibold">Verified</span> retail stores</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Purchase Bills</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalPurchaseBills) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-warning-subtle text-warning d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-receipt fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-warning fw-semibold">OCR</span> scanned receipts</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Pending OCR</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($pendingOcrReviews) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-danger-subtle text-danger d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-eye fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-danger fw-semibold">Action</span> required</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row: Extra Stats --}}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Shareholders</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalShareholders) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-success-subtle text-success d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-group fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-success fw-semibold">Verified</span> BOID members</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Doctors</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalDoctors) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-hospital fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-primary fw-semibold">NMC</span> registered</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Products</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalProducts) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-success-subtle text-success d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-package fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-success fw-semibold">Catalog</span> medicines</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1 fw-bold fs-12 text-uppercase tracking-wider">Redemptions</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalRewardRedemptions) }}</h3>
                        </div>
                        <div class="thumb-md rounded-4 bg-purple-subtle text-purple d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="iconoir-gift fs-4"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small"><span class="text-purple fw-semibold">{{ $pendingRedemptions }}</span> pending</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title fw-bold">Monthly Bill Amounts</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @if ($monthlyBillData->count() > 0)
                        <div style="height: 290px;">
                            <canvas id="monthlyBillChart"></canvas>
                        </div>
                        @push('scripts')
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var ctx = document.getElementById('monthlyBillChart').getContext('2d');
                                var monthlyData = @json($monthlyBillData);
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: monthlyData.map(function(d) { return d.month; }),
                                        datasets: [{
                                            label: 'Total Amount',
                                            data: monthlyData.map(function(d) { return d.total; }),
                                            backgroundColor: '#CD0B6F',
                                            borderColor: '#9E0553',
                                            borderWidth: 1,
                                            borderRadius: 8,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: false },
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) { return 'Rs ' + value.toLocaleString(); }
                                                }
                                            }
                                        }
                                    }
                                });
                            });
                        </script>
                        @endpush
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="iconoir-chart fs-1 d-block mb-2 text-muted"></i>
                            <span>No bill data available yet. Start uploading purchase bills to see trends.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fw-bold">Bills Overview</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="vstack gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Verified</span>
                            <span class="fw-bold text-success">{{ number_format($verifiedBills) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Pending</span>
                            <span class="fw-bold text-warning">{{ number_format($pendingBills) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Rejected</span>
                            <span class="fw-bold text-danger">{{ number_format($rejectedBills) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Fraud Flagged</span>
                            <span class="fw-bold text-danger">{{ number_format($fraudFlaggedBills) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fw-bold">Rewards Summary</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="vstack gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Total Points</span>
                            <span class="fw-bold" style="color: #CD0B6F;">{{ number_format($totalRewardPoints, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Total Bonus</span>
                            <span class="fw-bold text-info">{{ number_format($totalBonusPoints, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                            <span class="text-muted fw-medium">Total Royalty</span>
                            <span class="fw-bold text-success">{{ number_format($totalRoyaltyPoints, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Tables Row --}}
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title fw-bold">Recent Users</h4>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary" style="color: #CD0B6F; border-color: #CD0B6F;">View All</a>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @forelse ($recentUsers as $recentUser)
                                    <tr>
                                        <td class="px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 thumb-sm rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:34px;height:34px;font-size:12px;background-color:#CD0B6F;">
                                                    {{ strtoupper(substr($recentUser->name, 0, 1)) }}
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="m-0 text-truncate fs-13 fw-semibold">{{ $recentUser->name }}</h6>
                                                    <small class="text-muted">{{ $recentUser->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-0 py-2 text-end">
                                            <span class="badge bg-success-subtle text-success px-2 py-1">
                                                {{ $recentUser->status ?? 'active' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="px-0 text-muted">No users yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title fw-bold">Recent Pharmacies</h4>
                    <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-sm btn-outline-primary" style="color: #CD0B6F; border-color: #CD0B6F;">View All</a>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @forelse ($recentPharmacies as $pharmacy)
                                    <tr>
                                        <td class="px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 text-truncate">
                                                    <h6 class="m-0 text-truncate fw-semibold">{{ $pharmacy->store_name }}</h6>
                                                    <small class="text-muted">{{ $pharmacy->user?->email ?? '—' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-0 py-2 text-end">
                                            <span class="badge bg-success-subtle text-success px-2 py-1">
                                                {{ $pharmacy->status ?? 'active' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="px-0 text-muted">No pharmacies yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title fw-bold">Recent Redemptions</h4>
                    <a href="{{ route('admin.redemptions.index') }}" class="btn btn-sm btn-outline-primary" style="color: #CD0B6F; border-color: #CD0B6F;">View All</a>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @forelse ($recentRedemptions as $redemption)
                                    <tr>
                                        <td class="px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 text-truncate">
                                                    <h6 class="m-0 text-truncate fw-semibold">{{ $redemption->user?->name ?? 'N/A' }}</h6>
                                                    <small class="text-muted">{{ $redemption->redemption_type }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-0 py-2 text-end">
                                            <span class="fw-bold text-dark">{{ number_format($redemption->points_used, 0) }} pts</span>
                                        </td>
                                        <td class="px-0 py-2 text-end">
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1">
                                                {{ $redemption->status ?? 'pending' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="px-0 text-muted">No redemptions yet.</td></tr>
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
