@extends('layouts.admin')

@section('title', 'Edit ' . ($role->display_name ?: ucfirst($role->name)) . ' Role')

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
    .bg-brand-light {
        background-color: var(--om-brand-light-pink) !important;
    }
    .hover-bg:hover {
        background-color: var(--om-brand-light-pink);
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
                        <i class="las la-cog text-brand me-1"></i> Configure Role &amp; Permissions
                    </h4>
                    <p class="text-muted small mb-0">
                        System &amp; Settings &gt; <a href="{{ route('admin.roles.index') }}" class="text-brand text-decoration-underline">Roles</a> &gt; Edit {{ $role->display_name ?: ucfirst($role->name) }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Role View
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Role Metadata Card --}}
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top: 80px;">
                    <div class="card-header bg-brand-soft py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="las la-info-circle text-brand me-1"></i> Role Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role Slug (System Key)</label>
                            <input type="text" name="name" class="form-control bg-light" value="{{ $role->name }}" {{ $role->is_system ? 'readonly' : '' }}>
                            <small class="text-muted">
                                @if($role->is_system)
                                    <i class="las la-lock text-danger me-1"></i> Core system slug locked.
                                @else
                                    Unique internal slug identifier.
                                @endif
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $role->display_name ?: ucfirst($role->name)) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role Tag Color</label>
                            <select name="color" class="form-select">
                                <option value="pink" {{ $role->color === 'pink' ? 'selected' : '' }}>Pink (Brand Magenta)</option>
                                <option value="primary" {{ $role->color === 'primary' ? 'selected' : '' }}>Primary (Navy Blue)</option>
                                <option value="secondary" {{ $role->color === 'secondary' ? 'selected' : '' }}>Secondary (Gray)</option>
                                <option value="success" {{ $role->color === 'success' ? 'selected' : '' }}>Success (Green)</option>
                                <option value="danger" {{ $role->color === 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                <option value="warning" {{ $role->color === 'warning' ? 'selected' : '' }}>Warning (Yellow/Amber)</option>
                                <option value="info" {{ $role->color === 'info' ? 'selected' : '' }}>Info (Purple/Indigo)</option>
                                <option value="dark" {{ $role->color === 'dark' ? 'selected' : '' }}>Dark (Slate)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
                        </div>

                        <div class="alert bg-brand-soft border-0 small text-dark mb-3">
                            <i class="las la-users me-1 text-brand"></i> Currently assigned to <strong class="text-brand">{{ $role->users_count }} user(s)</strong>.
                        </div>

                        <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
                            <i class="las la-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>

            {{-- Permissions Matrix Configuration --}}
            <div class="col-md-8 mb-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-brand-soft py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="las la-key text-brand me-1"></i> Configure Module Permissions
                            </h5>
                            <small class="text-muted">Select the capabilities and modules accessible by users with this role.</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-brand" onclick="toggleAllPermissions(true)">Select All</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleAllPermissions(false)">Clear All</button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        @php($currentPerms = $role->permissions ?? [])
                        @foreach ($permissionGroups as $groupName => $perms)
                            <div class="card border rounded-3 mb-3">
                                <div class="card-header bg-brand-light py-2 d-flex justify-content-between align-items-center">
                                    <strong class="text-dark fs-13"><i class="las la-folder-open text-brand me-1"></i> {{ $groupName }}</strong>
                                    <div>
                                        <button type="button" class="btn btn-link btn-sm py-0 text-decoration-none text-brand fw-semibold" onclick="toggleGroupCheckboxes(this, true)">Select All</button>
                                        <span class="text-muted">|</span>
                                        <button type="button" class="btn btn-link btn-sm py-0 text-decoration-none text-muted" onclick="toggleGroupCheckboxes(this, false)">Clear</button>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row g-2">
                                        @foreach ($perms as $permKey => $permDesc)
                                            @php($isChecked = in_array($permKey, $currentPerms, true) || in_array('*', $currentPerms, true))
                                            <div class="col-md-6">
                                                <div class="form-check p-2 rounded hover-bg">
                                                    <input class="form-check-input group-check" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="perm_{{ str_replace('.', '_', $permKey) }}" {{ $isChecked ? 'checked' : '' }}>
                                                    <label class="form-check-label small d-block" for="perm_{{ str_replace('.', '_', $permKey) }}">
                                                        <span class="fw-semibold text-dark">{{ $permDesc }}</span>
                                                        <code class="d-block text-muted" style="font-size: 10.5px;">{{ $permKey }}</code>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-brand px-4 py-2 fw-semibold">
                                <i class="las la-save me-1"></i> Save Role Permissions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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

    function toggleAllPermissions(check) {
        document.querySelectorAll('.group-check').forEach(cb => cb.checked = check);
    }
</script>
@endpush
