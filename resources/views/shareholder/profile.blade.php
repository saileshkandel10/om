@extends('layouts.shareholder')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">My Profile</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('shareholder.dashboard') }}">{{ config('system.name') }}</a></li>
                        <li class="breadcrumb-item active">Profile</li>
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
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="" class="rounded-circle img-fluid" style="width: 128px; height: 128px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                            <i class="las la-user-tie fs-1"></i>
                                        </div>
                                    @endif
                                    @if ($user->email_verified_at)
                                        <div class="position-absolute top-50 start-100 translate-middle">
                                            <span class="rounded-circle border border-3 border-white d-inline-block bg-success" style="width: 18px; height: 18px;" title="Verified"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $user->name }}</h5>
                                    <p class="text-muted mb-0">@ {{ strtolower(str_replace(' ', '', $user->name)) }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="las la-envelope fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Email :</span>
                                    <span>{{ $user->email }}</span>
                                </div>
                                @if ($user->mobile)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="las la-phone fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Mobile :</span>
                                        <span>{{ $user->mobile }}</span>
                                    </div>
                                @endif
                                @if ($shareholder)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="las la-id-card fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">BOID :</span>
                                        <code>{{ $shareholder->boid }}</code>
                                    </div>
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="las la-user fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Shareholder Name :</span>
                                        <span>{{ $shareholder->shareholder_name }}</span>
                                    </div>
                                @endif
                                @if ($account?->shares_owned)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="las la-chart-line fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Shares Owned :</span>
                                        <span>{{ number_format($account->shares_owned) }}</span>
                                    </div>
                                @endif
                                <div class="text-body d-flex align-items-center">
                                    <i class="las la-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php
                                        $sColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary', 'pending' => 'bg-warning-subtle text-warning'];
                                    @endphp
                                    <span class="badge {{ $sColors[$shareholder?->status ?? 'pending'] ?? 'bg-warning-subtle text-warning' }}">{{ ucfirst($shareholder?->status ?? 'pending') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Account Information</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-unstyled mb-0">
                        <li class="">
                            <i class="las la-user me-2 text-secondary fs-22 align-middle"></i>
                            <b>Full Name</b> : {{ $user->name }}
                        </li>
                        <li class="mt-2">
                            <i class="las la-envelope me-2 text-secondary fs-22 align-middle"></i>
                            <b>Email</b> : {{ $user->email }}
                        </li>
                        @if ($user->mobile)
                            <li class="mt-2">
                                <i class="las la-phone me-2 text-secondary fs-22 align-middle"></i>
                                <b>Mobile</b> : {{ $user->mobile }}
                            </li>
                        @endif
                        @if ($shareholder)
                            <li class="mt-2">
                                <i class="las la-id-card me-2 text-secondary fs-22 align-middle"></i>
                                <b>BOID</b> : {{ $shareholder->boid }}
                            </li>
                        @endif
                        <li class="mt-2">
                            <i class="las la-calendar me-2 text-secondary fs-22 align-middle"></i>
                            <b>Joined</b> : {{ $user->created_at->format('F d, Y') }}
                        </li>
                        @if ($account?->verified_at)
                            <li class="mt-2">
                                <i class="las la-check-circle text-success fs-22 align-middle me-2"></i>
                                <b>Verified</b> : {{ $account->verified_at->format('F d, Y') }}
                            </li>
                        @endif
                        @if ($user->email_verified_at)
                            <li class="mt-2">
                                <i class="las la-check-circle text-success fs-22 align-middle me-2"></i>
                                <b>Email Verified</b> : {{ $user->email_verified_at->format('F d, Y') }}
                            </li>
                        @else
                            <li class="mt-2">
                                <i class="las la-exclamation-circle text-warning fs-22 align-middle me-2"></i>
                                <b>Email Verified</b> : <span class="text-warning">Not verified</span>
                            </li>
                        @endif
                        <li class="mt-2">
                            <i class="las la-sync me-2 text-secondary fs-22 align-middle"></i>
                            <b>Last Updated</b> : {{ $user->updated_at->format('F d, Y \\a\\t h:i A') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-chart-bar fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $shareholder?->shareholder_name ?? $user->name }}'s</span>
                <span class="text-primary fw-normal">shareholder overview</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-wallet fs-24 align-self-center text-info me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Reward Points</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($wallet?->total_points ?? 0, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-gift fs-24 align-self-center text-success me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Bonus</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($wallet?->total_bonus ?? 0, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-receipt fs-24 align-self-center text-primary me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Bills Uploaded</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($totalBills) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link fw-medium active" data-bs-toggle="tab" href="#overview" role="tab" aria-selected="true">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" data-bs-toggle="tab" href="#links" role="tab" aria-selected="false">Quick Links</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="overview" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title">Shareholder Details</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            @if ($shareholder)
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block mb-1">Shareholder Name</small>
                                            <span class="fw-semibold">{{ $shareholder->shareholder_name }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block mb-1">BOID</small>
                                            <code class="fw-semibold">{{ $shareholder->boid }}</code>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block mb-1">Verification Status</small>
                                            @php
                                                $vColors = ['verified' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger'];
                                            @endphp
                                            <span class="badge {{ $vColors[$account?->verification_status ?? 'pending'] ?? 'bg-warning-subtle text-warning' }}">
                                                {{ ucfirst($account?->verification_status ?? 'pending') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block mb-1">Shares Owned</small>
                                            <span class="fw-semibold">{{ $account?->shares_owned ? number_format($account->shares_owned) : '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block mb-1">Source</small>
                                            <span class="fw-semibold">{{ $shareholder->source_note ?? 'Imported' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block mb-1">Account Status</small>
                                            <span class="badge {{ $sColors[$shareholder->status ?? 'pending'] ?? 'bg-warning-subtle text-warning' }}">{{ ucfirst($shareholder->status ?? 'pending') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="las la-user-tie fs-3 d-block mb-2"></i>
                                    <p class="mb-0">No shareholder record linked to your account yet.</p>
                                    <small>Contact an administrator if you believe this is an error.</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="links" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title">Quick Links</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="{{ route('shareholder.dashboard') }}" class="card border border-primary bg-primary-subtle text-decoration-none h-100">
                                        <div class="card-body text-center">
                                            <i class="las la-chart-pie fs-3 text-primary mb-2 d-block"></i>
                                            <h6 class="text-primary mb-1">Dashboard</h6>
                                            <small class="text-primary opacity-75">Go to your shareholder dashboard</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('admin.reward-wallets.index') }}" class="card border border-success bg-success-subtle text-decoration-none h-100">
                                        <div class="card-body text-center">
                                            <i class="las la-wallet fs-3 text-success mb-2 d-block"></i>
                                            <h6 class="text-success mb-1">Reward Wallet</h6>
                                            <small class="text-success opacity-75">View your reward points & bonus</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('admin.exclusive-rewards') }}" class="card border border-info bg-info-subtle text-decoration-none h-100">
                                        <div class="card-body text-center">
                                            <i class="las la-star fs-3 text-info mb-2 d-block"></i>
                                            <h6 class="text-info mb-1">Exclusive Rewards</h6>
                                            <small class="text-info opacity-75">Browse shareholder-exclusive offers</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <form method="POST" action="{{ route('logout') }}" class="h-100">
                                        @csrf
                                        <button type="submit" class="card border border-danger bg-danger-subtle text-decoration-none h-100 w-100 text-start" style="cursor: pointer;">
                                            <div class="card-body text-center">
                                                <i class="las la-sign-out-alt fs-3 text-danger mb-2 d-block"></i>
                                                <h6 class="text-danger mb-1">Sign Out</h6>
                                                <small class="text-danger opacity-75">Securely end your session</small>
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
