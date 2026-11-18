@extends('layouts.admin')

@section('title', ($role->display_name ?: ucfirst($role->name)) . ' Role')

@push('styles')
<style>
    :root {
        --om-brand-pink: #CD0B6F;
        --om-brand-dark-pink: #A30958;
        --om-brand-soft-pink: #FDE8F3;
        --om-brand-light-pink: #FFF5F9;
        --om-brand-navy: #1E3A5F;
        --om-brand-border: #F8B4D9;
    }
    .btn-brand {
        background-color: var(--om-brand-pink);
        border-color: var(--om-brand-pink);
        color: #ffffff;
        transition: all 0.2s ease;
    }
    .btn-brand:hover, .btn-brand:focus, .btn-brand:active {
        background-color: var(--om-brand-dark-pink) !important;
        border-color: var(--om-brand-dark-pink) !important;
        color: #ffffff !important;
    }
    .btn-outline-brand {
        color: var(--om-brand-pink);
        border-color: var(--om-brand-pink);
    }
    .btn-outline-brand:hover {
        background-color: var(--om-brand-pink);
        color: #ffffff;
    }
    .text-brand {
        color: var(--om-brand-pink) !important;
    }
    .bg-brand-soft {
        background-color: var(--om-brand-soft-pink) !important;
    }
    .badge-soft-pink {
        background-color: #FDE8F3;
        color: #CD0B6F;
        border: 1px solid #F8B4D9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center mb-3">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-user-tag text-brand me-1"></i> {{ $role->display_name ?: ucfirst($role->name) }}
                    </h4>
                    <p class="text-muted small mb-0">
                        System &amp; Settings &gt; <a href="{{ route('admin.roles.index') }}" class="text-brand text-decoration-underline">Roles</a> &gt; {{ $role->display_name ?: ucfirst($role->name) }}
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    {{-- Export Role Users Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-brand dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export Users
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 shadow">
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'role_users', 'role_id' => $role->id, 'format' => 'xlsx']) }}"><i class="las la-file-excel text-success me-2"></i> Export to Excel (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'role_users', 'role_id' => $role->id, 'format' => 'csv']) }}"><i class="las la-file-csv text-primary me-2"></i> Export to CSV (.csv)</a></li>
                        </ul>
                    </div>

                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-brand">
                        <i class="las la-edit me-1"></i> Edit Role &amp; Permissions
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-check-circle me-1 fs-18 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Role Summary Card --}}
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-brand-soft text-brand d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 80px; height: 80px;">
                        <i class="las la-user-shield fs-1"></i>
                    </div>
                    <h5 class="mt-3 mb-1 fw-bold text-dark">{{ $role->display_name ?: ucfirst($role->name) }}</h5>
                    <code class="text-muted d-block small mb-2">{{ $role->name }}</code>
                    <p class="text-muted small mb-0">{{ $role->description ?: 'No specific description provided.' }}</p>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Role Type</span>
                            @if($role->is_system)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Core System</span>
                            @else
                                <span class="badge bg-brand-soft text-brand border border-brand-subtle px-2 py-1">Custom Role</span>
                            @endif
                        </div>
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Assigned Users</span>
                            <span class="badge bg-brand-soft text-brand border border-brand-subtle fs-12 fw-bold px-2 py-1">{{ $role->users_count }} Users</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Capabilities Granted</span>
                            <span class="badge bg-light text-dark border px-2 py-1">{{ count($role->permissions ?? []) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Created Date</span>
                            <span class="small">{{ $role->created_at ? $role->created_at->format('M d, Y') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Granted Permissions Summary --}}
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold fs-13 text-dark"><i class="las la-key text-brand me-1"></i> Active Capabilities</h6>
                </div>
                <div class="card-body p-3" style="max-height: 350px; overflow-y: auto;">
                    @forelse ($role->permissions ?? [] as $perm)
                        <span class="badge bg-brand-soft text-brand border border-brand-subtle m-1 small py-1 px-2">
                            <i class="las la-check text-brand me-1"></i>{{ $perm }}
                        </span>
                    @empty
                        <span class="text-muted small d-block p-2">No specific permissions granted.</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Assigned Users Table --}}
        <div class="col-md-8 mb-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-brand-soft d-flex justify-content-between align-items-center py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-users text-brand me-1"></i> Assigned Users ({{ $role->users_count }})
                    </h5>
                    <form method="GET" action="{{ route('admin.roles.show', $role) }}" class="d-flex gap-1">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <input type="text" name="search" class="form-control" placeholder="Search user..." value="{{ request('search') }}">
                            <button class="btn btn-brand" type="submit"><i class="las la-search"></i></button>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>All Roles</th>
                                    <th>Registered</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <small class="text-muted">USR-{{ 1000 + $user->id }}</small>
                                        </td>
                                        <td>
                                            <div class="small text-dark">{{ $user->email }}</div>
                                            <div class="small text-muted">{{ $user->mobile ?: ($user->profile?->phone ?: '—') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ ($user->status ?? 'active') === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' }} px-2 py-1">
                                                {{ ucfirst($user->status ?? 'Active') }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach ($user->roles as $r)
                                                <span class="badge bg-brand-soft text-brand border border-brand-subtle" style="font-size: 10.5px;">{{ $r->display_name ?: ucfirst($r->name) }}</span>
                                            @endforeach
                                        </td>
                                        <td class="small">{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-outline-brand py-0 px-2">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No users currently assigned to this role.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
