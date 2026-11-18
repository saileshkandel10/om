@extends('layouts.shareholder')

@section('title', __('Patient Dashboard'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Patient Dashboard</h4>
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
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-purple-subtle text-purple d-flex align-items-center justify-content-center">
                            <i class="iconoir-scan fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Bills Logged</p>
                            <h4 class="mb-0 fw-semibold">{{ $totalBills }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 thumb-xl rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center">
                            <i class="iconoir-clock fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Pending OCR</p>
                            <h4 class="mb-0 fw-semibold">{{ $pendingBills }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role-Based Advertisement Banner Carousel (Below Loyalty Wallet Balance) --}}
        <x-advertisement-banner role="patient" />

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Recent Purchase Bills</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Points Earned</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBills as $bill)
                                        <tr>
                                            <td><span class="ps-2 fw-medium">{{ $bill->invoice_number ?? '-' }}</span></td>
                                            <td>{{ $bill->invoice_date ? \Carbon\Carbon::parse($bill->invoice_date)->format('M d, Y') : '-' }}</td>
                                            <td>Rs {{ number_format($bill->total_amount, 2) }}</td>
                                            <td><span class="text-success fw-medium">+{{ number_format($bill->points_awarded, 2) }}</span></td>
                                            <td>
                                                @if($bill->validation_status === 'approved')
                                                    <span class="badge bg-success-subtle text-success">Approved</span>
                                                @elseif($bill->validation_status === 'rejected')
                                                    <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No recent bills found. Start uploading bills to earn loyalty rewards!</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Recent Reward Transactions</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @forelse($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <h6 class="m-0 fs-13">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</h6>
                                                <small class="text-muted">{{ $transaction->created_at->format('M d, Y, h:i A') }}</small>
                                            </td>
                                            <td class="text-end">
                                                @if($transaction->transaction_type === 'credit' || $transaction->transaction_type === 'bill_reward')
                                                    <span class="text-success fw-medium">+{{ number_format($transaction->points_earned ?? $transaction->points ?? 0, 2) }}</span>
                                                @else
                                                    <span class="text-danger fw-medium">-{{ number_format($transaction->points, 2) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4">No recent transactions found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Featured OM Medicines</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($featuredProducts as $product)
                                <div class="col-md-4 col-lg-2">
                                    <div class="border rounded p-3 text-center h-100 d-flex flex-column align-items-center">
                                        <div class="bg-primary-subtle rounded-circle p-3 mb-3">
                                            <i class="iconoir-capsule fs-3 text-primary"></i>
                                        </div>
                                        <h6 class="mb-1">{{ $product->brand_name }}</h6>
                                        <p class="text-muted small mb-0">{{ $product->generic_name }}</p>
                                        <span class="badge bg-light text-dark mt-auto border">Rs {{ $product->mrp }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-center text-muted">No featured products available.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
