@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">My Profile</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body p-4 rounded text-center" style="background: linear-gradient(135deg, #CD0B6F 0%, #9E0553 100%); min-height: 120px;"></div>
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
                                        <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}" alt="" class="rounded-circle img-fluid" style="width: 128px; height: 128px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow" style="width: 128px; height: 128px; background-color: #CD0B6F;">
                                            {{ $user->initials() }}
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
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="las la-shield-alt fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Roles :</span>
                                    <span>
                                        @forelse ($user->roles as $role)
                                            <span class="badge bg-{{ match($role->name) { 'Admin' => 'danger', 'Staff' => 'primary', 'Shareholder' => 'secondary', 'Pharmacy' => 'success', 'Doctor' => 'info', 'Customer' => 'warning', default => 'secondary' } }} me-1">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted">None</span>
                                        @endforelse
                                    </span>
                                </div>
                                <div class="text-body d-flex align-items-center">
                                    <i class="las la-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php
                                        $statusColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary', 'suspended' => 'bg-danger-subtle text-danger'];
                                    @endphp
                                    <span class="badge {{ $statusColors[$user->status] ?? 'bg-warning-subtle text-warning' }}">{{ ucfirst($user->status ?? 'pending') }}</span>
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
                        <li class="mt-2">
                            <i class="las la-calendar me-2 text-secondary fs-22 align-middle"></i>
                            <b>Joined</b> : {{ $user->created_at->format('F d, Y') }}
                        </li>
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

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Update Profile & Photo</h4>
                </div>
                <div class="card-body pt-0">
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile" value="{{ old('mobile', $user->mobile) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Profile Photo / Avatar</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                            <small class="text-muted">Upload JPG, PNG or WebP image (max 2MB).</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #CD0B6F; border-color: #CD0B6F;">
                            <i class="las la-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-chart-bar fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $user->name }}'s</span>
                <span class="text-primary fw-normal">overview dashboard</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-file-invoice fs-24 align-self-center text-info me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Total Bills</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($totalBills) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-users fs-24 align-self-center text-success me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">System Users</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($totalUsers) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-gift fs-24 align-self-center text-primary me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Redemptions</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ number_format($totalRedemptions) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#activity" role="tab" aria-selected="true">
                        <i class="las la-history me-1"></i> Recent Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab" aria-selected="false">
                        <i class="las la-user-cog me-1"></i> Account Settings
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="activity" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title">Audit Logs</h4>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="las la-eye me-1"></i> View All
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentAuditLogs as $log)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ match($log->action_name) { 'created' => 'success', 'updated' => 'info', 'deleted' => 'danger', default => 'secondary' } }}">
                                                        {{ ucfirst($log->action_name) }}
                                                    </span>
                                                </td>
                                                <td class="text-truncate" style="max-width: 300px;">
                                                    {{ $log->module_name ? $log->module_name . ' - ' : '' }}{{ $log->entity_name ?? 'record' }}
                                                </td>
                                                <td data-sort="{{ $log->created_at->format('Y/m/d') }}">
                                                    {{ $log->created_at->format('M d, Y \\a\\t h:i A') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    <i class="las la-clock fs-3 d-block mb-1"></i>
                                                    No recent activity found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="settings" role="tabpanel">
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
                                    <a href="{{ route('admin.users.edit', $user) }}" class="card border border-primary bg-primary-subtle text-decoration-none h-100">
                                        <div class="card-body text-center">
                                            <i class="las la-user-edit fs-3 text-primary mb-2 d-block"></i>
                                            <h6 class="text-primary mb-1">Edit My Profile</h6>
                                            <small class="text-primary opacity-75">Update your name, mobile & status</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('admin.audit-logs.index') }}" class="card border border-info bg-info-subtle text-decoration-none h-100">
                                        <div class="card-body text-center">
                                            <i class="las la-history fs-3 text-info mb-2 d-block"></i>
                                            <h6 class="text-info mb-1">Audit Trail</h6>
                                            <small class="text-info opacity-75">View all your activity logs</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('admin.dashboard') }}" class="card border border-success bg-success-subtle text-decoration-none h-100">
                                        <div class="card-body text-center">
                                            <i class="las la-chart-pie fs-3 text-success mb-2 d-block"></i>
                                            <h6 class="text-success mb-1">Dashboard</h6>
                                            <small class="text-success opacity-75">Go back to the admin dashboard</small>
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
