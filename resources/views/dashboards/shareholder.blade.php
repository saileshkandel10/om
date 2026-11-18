@extends('layouts.shareholder')

@section('title', __('Shareholder Dashboard'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Shareholder Dashboard</h4>
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
                            <p class="text-muted mb-1">Total Points</p>
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
                            <p class="text-muted mb-1">Bonus</p>
                            <h4 class="mb-0 fw-semibold">{{ number_format($wallet->total_bonus, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center">
                            <i class="iconoir-donate fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Royalty</p>
                            <h4 class="mb-0 fw-semibold">{{ number_format($wallet->total_royalty, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center">
                            <i class="iconoir-cloud-upload fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Bills Uploaded</p>
                            <h4 class="mb-0 fw-semibold">{{ $totalBills }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role-Based Advertisement Banner Carousel (Below Loyalty Wallet Balance) --}}
        <x-advertisement-banner role="shareholder" />

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Latest Notices</h4>
                            </div>
                            <div class="col-auto">
                                <a href="#" class="text-muted small">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @forelse ($notices as $notice)
                            <div class="d-flex align-items-start py-2 border-bottom border-dashed">
                                <div class="flex-shrink-0 thumb-md rounded-circle bg-{{ $notice->notice_type === 'agm' ? 'danger' : ($notice->notice_type === 'financial' ? 'success' : 'primary') }}-subtle text-{{ $notice->notice_type === 'agm' ? 'danger' : ($notice->notice_type === 'financial' ? 'success' : 'primary') }} d-flex align-items-center justify-content-center">
                                    <i class="iconoir-{{ $notice->notice_type === 'agm' ? 'group' : ($notice->notice_type === 'financial' ? 'stats-report' : 'announcement') }} fs-5"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-semibold">{{ $notice->title }}</h6>
                                    <small class="text-muted">{{ $notice->published_at?->format('M d, Y') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="iconoir-announcement fs-2 mb-2 d-block"></i>
                                <p class="mb-0">No notices yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Active Campaigns</h4>
                            </div>
                            <div class="col-auto">
                                <a href="#" class="text-muted small">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @forelse ($campaigns as $campaign)
                            <div class="d-flex align-items-start py-2 border-bottom border-dashed">
                                <div class="flex-shrink-0 thumb-md rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                                    <i class="iconoir-megaphone fs-5"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-semibold">{{ $campaign->name }}</h6>
                                    <small class="text-muted">{{ $campaign->campaign_type }} — ends {{ $campaign->end_date?->format('M d') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="iconoir-megaphone fs-2 mb-2 d-block"></i>
                                <p class="mb-0">No active campaigns.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Recent Reward Transactions</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('shareholder.bills.index') }}" class="text-muted small">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice #</th>
                                        <th>Points</th>
                                        <th>Bonus</th>
                                        <th>Royalty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentRewards as $reward)
                                        <tr>
                                            <td>{{ $reward->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <code>{{ $reward->purchaseBill?->invoice_number ?? '—' }}</code>
                                            </td>
                                            <td>{{ number_format($reward->points_earned, 2) }}</td>
                                            <td>{{ number_format($reward->bonus_amount, 2) }}</td>
                                            <td>{{ number_format($reward->royalty_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-success">Completed</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">No reward transactions yet. Upload bills to earn rewards!</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Recent Purchase Bills</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('shareholder.bills.index') }}" class="text-muted small">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Pharmacy</th>
                                        <th>Total</th>
                                        <th>OCR Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentBills as $bill)
                                        <tr>
                                            <td><code>{{ $bill->invoice_number ?? '—' }}</code></td>
                                            <td>{{ $bill->pharmacy?->store_name ?? '—' }}</td>
                                            <td>Rs {{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                            <td>
                                                @php($statusColor = $bill->ocr_status === 'completed' ? 'success' : ($bill->ocr_status === 'failed' ? 'danger' : 'warning'))
                                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($bill->ocr_status ?? 'pending') }}</span>
                                            </td>
                                            <td>{{ $bill->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No bills uploaded yet.</td>
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
