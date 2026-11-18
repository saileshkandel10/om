@extends('layouts.admin')

@section('title', 'Roles & Permissions')

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
        box-shadow: 0 4px 12px rgba(205, 11, 111, 0.25);
    }
    .btn-outline-brand {
        color: var(--om-brand-pink);
        border-color: var(--om-brand-pink);
        transition: all 0.2s ease;
    }
    .btn-outline-brand:hover, .btn-outline-brand:focus, .btn-outline-brand:active {
        background-color: var(--om-brand-pink) !important;
        border-color: var(--om-brand-pink) !important;
        color: #ffffff !important;
    }
    .text-brand {
        color: var(--om-brand-pink) !important;
    }
    .bg-brand-soft {
        background-color: var(--om-brand-soft-pink) !important;
    }
    .bg-brand-light {
        background-color: var(--om-brand-light-pink) !important;
    }
    .border-brand-subtle {
        border-color: var(--om-brand-border) !important;
    }
    .nav-pills .nav-link.active-brand, .nav-pills .nav-link.active {
        background-color: var(--om-brand-pink) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(205, 11, 111, 0.2);
    }
    .nav-pills .nav-link {
        color: var(--om-brand-navy);
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover:not(.active) {
        color: var(--om-brand-pink);
        background-color: var(--om-brand-soft-pink);
    }
    .stat-card-accent {
        border-top: 3px solid var(--om-brand-pink) !important;
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card-accent:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06) !important;
    }
    .matrix-table th, .matrix-table td {
        vertical-align: middle;
        font-size: 12.5px;
    }
    .badge-soft-pink {
        background-color: #FDE8F3;
        color: #CD0B6F;
        border: 1px solid #F8B4D9;
    }
    .badge-soft-primary { background-color: #e0f2fe; color: #0369a1; }
    .badge-soft-secondary { background-color: #f1f5f9; color: #475569; }
    .badge-soft-success { background-color: #dcfce7; color: #15803d; }
    .badge-soft-danger { background-color: #fee2e2; color: #b91c1c; }
    .badge-soft-warning { background-color: #fef3c7; color: #b45309; }
    .badge-soft-info { background-color: #e0e7ff; color: #4338ca; }
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
                        <i class="las la-user-shield text-brand me-1"></i> Roles &amp; Permissions Management
                    </h4>
                    <p class="text-muted small mb-0">
                        System &amp; Settings &gt; Configure access roles, permissions matrix, and role-based operational capabilities across OM Connect.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    {{-- Universal Export Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-brand dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export Data
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 shadow">
                            <li class="dropdown-header text-uppercase fs-11 fw-bold text-brand">Roles Summary</li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'roles', 'format' => 'xlsx']) }}"><i class="las la-file-excel text-success me-2"></i> Roles Overview (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'roles', 'format' => 'csv']) }}"><i class="las la-file-csv text-primary me-2"></i> Roles Overview (.csv)</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-header text-uppercase fs-11 fw-bold text-brand">User Assignments</li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'user_matrix', 'format' => 'xlsx']) }}"><i class="las la-users text-info me-2"></i> User-Role Matrix (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'user_matrix', 'format' => 'csv']) }}"><i class="las la-users text-info me-2"></i> User-Role Matrix (.csv)</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-header text-uppercase fs-11 fw-bold text-brand">Capabilities Matrix</li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'permissions', 'format' => 'xlsx']) }}"><i class="las la-th-list text-warning me-2"></i> Permissions Matrix (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.roles.export', ['type' => 'permissions', 'format' => 'csv']) }}"><i class="las la-th-list text-warning me-2"></i> Permissions Matrix (.csv)</a></li>
                        </ul>
                    </div>

                    {{-- Create Custom Role Button --}}
                    <button type="button" class="btn btn-sm btn-brand shadow-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="las la-plus-circle me-1"></i> New Custom Role
                    </button>
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards with OM Connect Brand Accents --}}
    <div class="row mb-3 g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-brand-soft text-brand me-3">
                        <i class="las la-user-tag fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Total System Roles</span>
                        <h4 class="mb-0 fw-bold text-dark">{{ $stats['total_roles'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #1E3A5F !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary me-3">
                        <i class="las la-shield-alt fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Core System Roles</span>
                        <h4 class="mb-0 fw-bold" style="color: #1E3A5F;">{{ $stats['system_roles'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #0D9488 !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-success-subtle text-success me-3">
                        <i class="las la-sliders-h fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Custom Roles</span>
                        <h4 class="mb-0 fw-bold text-success">{{ $stats['custom_roles'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-brand-soft text-brand me-3">
                        <i class="las la-users fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Assigned Users</span>
                        <h4 class="mb-0 fw-bold text-brand">{{ number_format($stats['total_assigned_users']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-pills bg-white p-2 rounded-3 border shadow-sm" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'roles' || request('tab') !== 'matrix' ? 'active' : '' }}"
                       id="roles-tab" data-bs-toggle="pill" href="#rolesPane" role="tab">
                        <i class="las la-id-badge me-1 fs-16"></i>
                        System Roles Directory ({{ $roles->count() }})
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link {{ $tab === 'matrix' || request('tab') === 'matrix' ? 'active' : '' }}"
                       id="matrix-tab" data-bs-toggle="pill" href="#matrixPane" role="tab">
                        <i class="las la-th me-1 fs-16"></i>
                        Permissions Capabilities Matrix
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        {{-- ======================================================= --}}
        {{-- TAB 1: ROLES DIRECTORY --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'roles' || request('tab') !== 'matrix' ? 'show active' : '' }}" id="rolesPane" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="las la-id-card text-brand me-1"></i> Active Roles Directory
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-white text-brand border border-brand-subtle px-3 py-2 fw-semibold">
                                <i class="las la-check-circle me-1"></i> {{ $roles->count() }} Roles Configured
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Search Filter --}}
                    <form method="GET" action="{{ route('admin.roles.index') }}" class="row g-2 mb-3 align-items-center">
                        <input type="hidden" name="tab" value="roles">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search role name, description..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-brand">Search</button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>

                    {{-- Roles Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Role &amp; Identity</th>
                                    <th style="width: 35%;">Description &amp; Purpose</th>
                                    <th style="width: 12%;">Type</th>
                                    <th style="width: 13%;">Assigned Users</th>
                                    <th style="width: 15%; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle p-2 bg-brand-soft text-brand me-2 fs-18">
                                                    <i class="las la-user-tag"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.roles.show', $role) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $role->display_name ?: ucfirst($role->name) }}
                                                    </a>
                                                    <code class="d-block small text-muted">{{ $role->name }}</code>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-muted small" style="max-width: 400px;">
                                                {{ $role->description ?: 'Standard system role for '.$role->name.' accounts.' }}
                                            </p>
                                        </td>
                                        <td>
                                            @if($role->is_system)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                    <i class="las la-lock me-1"></i> Core System
                                                </span>
                                            @else
                                                <span class="badge bg-brand-soft text-brand border border-brand-subtle px-2 py-1">
                                                    <i class="las la-sliders-h me-1"></i> Custom Role
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.roles.show', $role) }}" class="badge bg-brand-soft text-brand border border-brand-subtle text-decoration-none px-3 py-1 fs-12 fw-bold">
                                                <i class="las la-users me-1"></i> {{ $role->users_count }} Users
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-brand" title="View Assigned Users">
                                                    <i class="las la-eye me-1"></i> View
                                                </a>
                                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-secondary" title="Configure Permissions">
                                                    <i class="las la-cog me-1"></i> Edit
                                                </a>

                                                @if(!$role->is_system && $role->users_count == 0)
                                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Delete role {{ $role->display_name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete Custom Role">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No system roles found matching your search.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- TAB 2: PERMISSIONS MATRIX --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'matrix' || request('tab') === 'matrix' ? 'show active' : '' }}" id="matrixPane" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft d-flex justify-content-between align-items-center py-3 border-0">
                    <div>
                        <h5 class="card-title mb-1 fw-bold text-dark"><i class="las la-th text-brand me-1"></i> System Permissions Capabilities Matrix</h5>
                        <p class="text-muted small mb-0">Overview of operational module access rights granted to each user role.</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.roles.export', ['type' => 'permissions', 'format' => 'xlsx']) }}" class="btn btn-sm btn-brand">
                            <i class="las la-file-excel me-1"></i> Export Matrix (.xlsx)
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover matrix-table mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width: 250px;">Module &amp; Permission Capability</th>
                                    @foreach ($roles as $r)
                                        <th class="text-center" style="min-width: 100px;">
                                            <span class="badge bg-brand-soft text-brand border border-brand-subtle d-block py-1">{{ $r->display_name ?: ucfirst($r->name) }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissionGroups as $groupName => $perms)
                                    <tr style="background-color: #f8fafc;">
                                        <td colspan="{{ $roles->count() + 1 }}" class="fw-bold text-uppercase fs-12 text-dark py-2">
                                            <i class="las la-folder-open text-brand me-1"></i> {{ $groupName }}
                                        </td>
                                    </tr>
                                    @foreach ($perms as $permKey => $permDesc)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $permDesc }}</div>
                                                <code class="text-muted small">{{ $permKey }}</code>
                                            </td>
                                            @foreach ($roles as $r)
                                                @php($has = $r->hasPermission($permKey))
                                                <td class="text-center {{ $has ? 'bg-success-subtle' : '' }}">
                                                    @if ($has)
                                                        <i class="las la-check-circle fs-20 text-success" title="Permission Granted"></i>
                                                    @else
                                                        <i class="las la-times fs-18 text-muted opacity-25" title="No Access"></i>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: CREATE CUSTOM ROLE --}}
{{-- ======================================================= --}}
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-header bg-brand-soft border-0">
                    <h5 class="modal-title fw-bold text-dark" id="createRoleModalLabel">
                        <i class="las la-plus-circle text-brand me-1"></i> Create Custom System Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role Slug (System ID) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. branch_manager, billing_clerk" required>
                            <small class="text-muted">Unique alphanumeric slug (letters, numbers, underscores).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Display Name (Label) <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control" placeholder="e.g. Branch Billing Manager" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role Tag Color</label>
                            <select name="color" class="form-select">
                                <option value="pink" selected>Brand Pink (OM Connect Magenta)</option>
                                <option value="primary">Primary (Navy Blue)</option>
                                <option value="secondary">Secondary (Gray)</option>
                                <option value="success">Success (Green)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="warning">Warning (Yellow/Amber)</option>
                                <option value="info">Info (Purple/Indigo)</option>
                                <option value="dark">Dark (Slate)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief explanation of duties and access level."></textarea>
                        </div>

                        {{-- Permissions Checkbox Groups --}}
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold text-dark mb-2"><i class="las la-key text-warning me-1"></i> Assign Initial Permissions:</h6>
                            @foreach ($permissionGroups as $groupName => $perms)
                                <div class="card border mb-2">
                                    <div class="card-header bg-brand-light py-2 d-flex justify-content-between align-items-center">
                                        <strong class="text-dark fs-12"><i class="las la-folder-open text-brand me-1"></i> {{ $groupName }}</strong>
                                        <div>
                                            <button type="button" class="btn btn-link btn-sm py-0 text-decoration-none text-brand fw-semibold" onclick="toggleGroupCheckboxes(this, true)">Select All</button>
                                            <span class="text-muted">|</span>
                                            <button type="button" class="btn btn-link btn-sm py-0 text-decoration-none text-muted" onclick="toggleGroupCheckboxes(this, false)">Clear</button>
                                        </div>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="row g-2">
                                            @foreach ($perms as $permKey => $permDesc)
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input group-check" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="perm_create_{{ str_replace('.', '_', $permKey) }}">
                                                        <label class="form-check-label small" for="perm_create_{{ str_replace('.', '_', $permKey) }}">
                                                            {{ $permDesc }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand px-4">Create Custom Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleGroupCheckboxes(btn, check) {
        const card = btn.closest('.card');
        if (card) {
            const checkboxes = card.querySelectorAll('.group-check');
            checkboxes.forEach(cb => cb.checked = check);
        }
    }
</script>
@endpush
