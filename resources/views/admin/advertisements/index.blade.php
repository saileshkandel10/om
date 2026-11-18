@extends('layouts.admin')

@section('title', 'Advertisement & Banner Management')

@push('styles')
<style>
    .banner-preview-thumb {
        width: 140px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background-size: cover;
        background-position: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
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
                        <i class="las la-ad text-brand me-1"></i> Role-Based Advertisements &amp; Banners
                    </h4>
                    <p class="text-muted small mb-0">
                        Marketing &amp; Campaigns &gt; Manage targeted dashboard promotional banners and campaigns across user roles.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <a href="{{ route('admin.advertisements.create') }}" class="btn btn-sm btn-brand">
                        <i class="las la-plus-circle me-1"></i> New Advertisement
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

    {{-- KPI Analytics Cards --}}
    <div class="row mb-3 g-3">
        <div class="col-md-2 col-sm-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Total Banners</span>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ number_format($stats['total']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #10B981 !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Active Now</span>
                    <h4 class="mb-0 fw-bold text-success mt-1">{{ number_format($stats['active']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #3B82F6 !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Total Impressions</span>
                    <h4 class="mb-0 fw-bold text-primary mt-1">{{ number_format($stats['total_impressions']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #F59E0B !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Total Clicks</span>
                    <h4 class="mb-0 fw-bold text-warning mt-1">{{ number_format($stats['total_clicks']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-12 col-12">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #8B5CF6 !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Overall CTR</span>
                    <h4 class="mb-0 fw-bold text-purple mt-1">{{ $stats['avg_ctr'] }}%</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Advertisements Table Card --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-brand-soft py-3 border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-images text-brand me-1"></i> Configured Advertisements &amp; Banners
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-white text-brand border border-brand-subtle px-3 py-2 fw-semibold">
                        {{ $advertisements->total() }} Total Configured
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter & Search Bar --}}
            <form method="GET" action="{{ route('admin.advertisements.index') }}" class="row g-2 mb-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search title, description, CTA..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All Target Roles</option>
                        <option value="all" @selected(request('role') === 'all')>All Users (Global)</option>
                        <option value="patient" @selected(request('role') === 'patient' || request('role') === 'customer')>Patient / Customer</option>
                        <option value="doctor" @selected(request('role') === 'doctor')>Doctor</option>
                        <option value="pharmacy" @selected(request('role') === 'pharmacy')>Pharmacy</option>
                        <option value="shareholder" @selected(request('role') === 'shareholder')>Shareholder</option>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="image" @selected(request('type') === 'image')>Image Banner</option>
                        <option value="text" @selected(request('type') === 'text')>Text Advertisement</option>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active / Live</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive / Expired</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-brand">Filter</button>
                    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 160px;">Banner Preview</th>
                            <th>Title &amp; CTA</th>
                            <th>Target Role</th>
                            <th>Type</th>
                            <th>Schedule Window</th>
                            <th>Performance</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($advertisements as $ad)
                            <tr>
                                <td>
                                    @if ($ad->type === 'image' && $ad->image_url)
                                        <div class="banner-preview-thumb" style="background-image: url('{{ $ad->image_url }}');"></div>
                                    @else
                                        <div class="banner-preview-thumb p-2 text-center" style="background: {{ $ad->background_color ?: '#1E3A5F' }}; color: {{ $ad->text_color ?: '#FFFFFF' }};">
                                            <span class="fs-10 fw-bold text-truncate w-100">{{ $ad->title }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $ad->title }}</div>
                                    @if ($ad->description)
                                        <p class="mb-0 text-muted fs-12 text-truncate" style="max-width: 280px;">{{ $ad->description }}</p>
                                    @endif
                                    @if ($ad->cta_text)
                                        <span class="badge bg-light text-dark border mt-1 fs-11">
                                            <i class="las la-external-link-alt me-1"></i> {{ $ad->cta_text }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $roleColor = match(strtolower($ad->target_role)) {
                                            'patient', 'customer' => 'success',
                                            'doctor' => 'info',
                                            'pharmacy' => 'warning',
                                            'shareholder' => 'purple',
                                            default => 'primary'
                                        };
                                        $roleLabel = match(strtolower($ad->target_role)) {
                                            'patient', 'customer' => 'Patient',
                                            'doctor' => 'Doctor',
                                            'pharmacy' => 'Pharmacy',
                                            'shareholder' => 'Shareholder',
                                            default => 'All Users'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} border border-{{ $roleColor }}-subtle px-2 py-1 fs-12 fw-semibold">
                                        <i class="las la-user-tag me-1"></i> {{ $roleLabel }}
                                    </span>
                                </td>
                                <td>
                                    @if ($ad->type === 'image')
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="las la-image text-primary me-1"></i> Image
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="las la-font text-brand me-1"></i> Text Ad
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fs-12">
                                        @if ($ad->start_date && $ad->end_date)
                                            <span>{{ $ad->start_date->format('M d, Y') }} &rarr; {{ $ad->end_date->format('M d, Y') }}</span>
                                        @elseif ($ad->start_date)
                                            <span>From {{ $ad->start_date->format('M d, Y') }}</span>
                                        @elseif ($ad->end_date)
                                            <span>Until {{ $ad->end_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-muted">Always Active</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fs-12">
                                        <div><i class="las la-eye text-muted me-1"></i> {{ number_format($ad->impressions_count) }} views</div>
                                        <div><i class="las la-mouse-pointer text-muted me-1"></i> {{ number_format($ad->clicks_count) }} clicks <strong class="text-brand">({{ $ad->ctr }}%)</strong></div>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.advertisements.toggle-active', $ad) }}">
                                        @csrf
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" @checked($ad->is_active)>
                                        </div>
                                    </form>
                                    @if ($ad->isCurrentlyActive())
                                        <span class="badge bg-success-subtle text-success fs-10 mt-1">Live Now</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-10 mt-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.advertisements.show', $ad) }}" class="btn btn-outline-brand" title="Preview & Analytics">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.advertisements.edit', $ad) }}" class="btn btn-outline-secondary" title="Edit Advertisement">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.advertisements.destroy', $ad) }}" class="d-inline" onsubmit="return confirm('Delete advertisement banner?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="las la-bullhorn fs-48 text-muted d-block mb-2"></i>
                                    No advertisement banners found matching your filters.
                                    <div class="mt-2">
                                        <a href="{{ route('admin.advertisements.create') }}" class="btn btn-sm btn-brand">Create First Banner</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $advertisements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
