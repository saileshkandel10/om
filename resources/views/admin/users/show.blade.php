@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">User Details</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">{{ $user->name }}</li>
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
                                            <div class="rounded-circle bg-black text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                                {{ $user->initials() }}
                                            </div>
                                        @endif
                                        @if ($user->status === 'active')
                                            <div class="position-absolute top-50 start-100 translate-middle">
                                                <span class="rounded-circle thumb-sm border border-3 border-white d-inline-block bg-success" style="width: 18px; height: 18px;"></span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                        <h5 class="m-0 fs-3 fw-bold">{{ $user->name }}</h5>
                                        <p class="text-muted mb-0">Joined {{ $user->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="mb-2">
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-primary-subtle text-primary me-1">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                    <div class="text-muted mb-2 d-flex align-items-center">
                                        <i class="iconoir-mail-out fs-20 me-1"></i>
                                        <span class="text-body fw-semibold me-1">Email :</span>
                                        <span>{{ $user->email }}</span>
                                    </div>
                                    @if ($user->mobile)
                                        <div class="text-body mb-2 d-flex align-items-center">
                                            <i class="iconoir-phone fs-20 me-1 text-muted"></i>
                                            <span class="text-body fw-semibold me-1">Phone :</span>
                                            <span>{{ $user->mobile }}</span>
                                        </div>
                                    @endif
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-check-circle fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Status :</span>
                                        @php
                                            $statusClasses = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-warning-subtle text-warning', 'suspended' => 'bg-danger-subtle text-danger'];
                                            $statusClass = $statusClasses[$user->status] ?? 'bg-secondary-subtle text-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($user->status ?? 'unknown') }}</span>
                                    </div>
                                    @if ($user->email_verified_at)
                                        <div class="text-body mb-2 d-flex align-items-center">
                                            <i class="iconoir-badge-check fs-20 me-1 text-success"></i>
                                            <span class="text-body fw-semibold me-1">Email Verified :</span>
                                            <span>{{ $user->email_verified_at->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                    @if ($user->userProfile)
                                        @if ($user->userProfile->gender)
                                            <div class="text-body mb-2 d-flex align-items-center">
                                                <i class="iconoir-user fs-20 me-1 text-muted"></i>
                                                <span class="text-body fw-semibold me-1">Gender :</span>
                                                <span>{{ ucfirst($user->userProfile->gender) }}</span>
                                            </div>
                                        @endif
                                        @if ($user->userProfile->dob)
                                            <div class="text-body mb-2 d-flex align-items-center">
                                                <i class="iconoir-birthday-cake fs-20 me-1 text-muted"></i>
                                                <span class="text-body fw-semibold me-1">DOB :</span>
                                                <span>{{ $user->userProfile->dob->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                        @if ($user->userProfile->province || $user->userProfile->district)
                                            <div class="text-body mb-2 d-flex align-items-center">
                                                <i class="iconoir-map-pin fs-20 me-1 text-muted"></i>
                                                <span class="text-body fw-semibold me-1">Location :</span>
                                                <span>{{ collect([$user->userProfile->district, $user->userProfile->province])->filter()->implode(', ') }}</span>
                                            </div>
                                        @endif
                                    @endif
                                    @if ($user->pharmacy)
                                        <hr>
                                        <h6 class="fw-semibold mb-2">Pharmacy</h6>
                                        <div class="text-body mb-1 d-flex align-items-center">
                                            <i class="iconoir-pharmacy-cross-circle fs-20 me-1 text-muted"></i>
                                            <span>{{ $user->pharmacy->store_name }}</span>
                                        </div>
                                        @if ($user->pharmacy->license_no)
                                            <div class="text-body mb-1 d-flex align-items-center">
                                                <span class="text-body fw-semibold me-1">License:</span>
                                                <span>{{ $user->pharmacy->license_no }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm"><i class="iconoir-edit-pencil me-1"></i> Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="iconoir-trash me-1"></i> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                    <i class="iconoir-star fs-5 text-primary"></i>
                    <span class="text-primary fw-semibold">{{ $user->name }}'s</span>
                    <span class="text-primary fw-normal">account overview</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="iconoir-paste-clipboard fs-24 align-self-center text-info me-2"></i>
                                    <div class="flex-grow-1 text-truncate">
                                        <p class="text-dark mb-0 fw-semibold fs-13">Total Bills</p>
                                        <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $user->purchaseBills->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="iconoir-coins fs-24 align-self-center text-blue me-2"></i>
                                    <div class="flex-grow-1 text-truncate">
                                        <p class="text-dark mb-0 fw-semibold fs-13">Reward Points</p>
                                        <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($user->rewardWallet?->total_points ?? 0) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="iconoir-check-circle fs-24 align-self-center text-primary me-2"></i>
                                    <div class="flex-grow-1 text-truncate">
                                        <p class="text-dark mb-0 fw-semibold fs-13">Completed</p>
                                        <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $user->purchaseBills->where('validation_status', 'verified')->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="iconoir-warning-triangle fs-24 align-self-center text-danger me-2"></i>
                                    <div class="flex-grow-1 text-truncate">
                                        <p class="text-dark mb-0 fw-semibold fs-13">Flagged</p>
                                        <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $user->purchaseBills->where('fraud_flag', true)->count() }}</h3>
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
                                <h4 class="card-title">Purchase Bills</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.purchase-bills.index') }}" class="btn btn-primary btn-sm"><i class="iconoir-eye me-1"></i> View All</a>
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
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>OCR Status</th>
                                        <th>Validation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($user->purchaseBills as $bill)
                                        <tr>
                                            <td><a href="{{ route('admin.purchase-bills.show', $bill) }}" class="text-primary">{{ $bill->invoice_number }}</a></td>
                                            <td>{{ $bill->pharmacy?->store_name ?? 'N/A' }}</td>
                                            <td>{{ $bill->invoice_date?->format('d/m/Y') }}</td>
                                            <td>{{ number_format($bill->total_amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $ocrClasses = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning'];
                                                    $ocrClass = $ocrClasses[$bill->ocr_status] ?? 'bg-secondary-subtle text-secondary';
                                                @endphp
                                                <span class="badge {{ $ocrClass }}">{{ ucfirst($bill->ocr_status) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $valClasses = ['verified' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger'];
                                                    $valClass = $valClasses[$bill->validation_status] ?? 'bg-secondary-subtle text-secondary';
                                                @endphp
                                                <span class="badge {{ $valClass }}">{{ ucfirst($bill->validation_status) }}</span>
                                                @if ($bill->fraud_flag)
                                                    <span class="badge bg-danger-subtle text-danger ms-1"><i class="iconoir-warning-triangle me-1"></i>Fraud</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No purchase bills found.</td>
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

