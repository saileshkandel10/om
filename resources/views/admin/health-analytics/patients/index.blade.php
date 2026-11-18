@extends('layouts.admin')

@section('title', __('Patient Health Analytics'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Patient Health Analytics</h4>
                    <p class="text-muted small mb-0">Discover and review medication-based health patterns detected from uploaded bills and prescriptions.</p>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-4" role="alert">
        <div class="bg-info text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="iconoir-info-circle fs-5"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold mb-0">Medical Safety Notice</h6>
            <p class="mb-0 small">This module provides medication-based analytical insights. It does not confirm a medical diagnosis and should not replace professional clinical judgment.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fw-bold">Analyzed Patient Records</h4>
                </div>
                <div class="card-body pt-0">
                    <form method="GET" action="{{ route('admin.health-analytics.patients.index') }}" class="row gy-2 gx-2 mb-4 align-items-end p-3 bg-light rounded-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Search Patient</label>
                            <input type="search" name="search" class="form-control form-control-sm" placeholder="Name, ID, or Email..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Health Category</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Association</label>
                            <select name="association_level" class="form-select form-select-sm">
                                <option value="">All Levels</option>
                                <option value="Strong Association" @selected(request('association_level') == 'Strong Association')>Strong</option>
                                <option value="Moderate Association" @selected(request('association_level') == 'Moderate Association')>Moderate</option>
                                <option value="Possible Association" @selected(request('association_level') == 'Possible Association')>Possible</option>
                                <option value="Weak Association" @selected(request('association_level') == 'Weak Association')>Weak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Review Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="pending_review" @selected(request('status') == 'pending_review')>Pending</option>
                                <option value="confirmed_pattern" @selected(request('status') == 'confirmed_pattern')>Confirmed</option>
                                <option value="needs_review" @selected(request('status') == 'needs_review')>Needs Review</option>
                                <option value="not_applicable" @selected(request('status') == 'not_applicable')>Not Applicable</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold" style="background-color: #CD0B6F; border-color: #CD0B6F;">Filter</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient</th>
                                    <th>Detected Category</th>
                                    <th>Association Level</th>
                                    <th>Evidence Base</th>
                                    <th>Last Detected</th>
                                    <th>Review Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($analytics as $analytic)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:34px;height:34px;font-size:12px;background-color:#CD0B6F;">
                                                    {{ strtoupper(substr($analytic->patient->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.health-analytics.patients.show', $analytic->patient) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $analytic->patient->name }}
                                                    </a>
                                                    <div class="text-muted small">P-{{ $analytic->patient->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $analytic->healthCategory->color }}20; color: {{ $analytic->healthCategory->color }}; border: 1px solid {{ $analytic->healthCategory->color }}40;">
                                                <i class="{{ $analytic->healthCategory->icon }} me-1"></i> {{ $analytic->healthCategory->name }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $assocColor = match ($analytic->association_level) {
                                                    'Strong Association' => 'danger',
                                                    'Moderate Association' => 'warning',
                                                    'Possible Association' => 'info',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="text-{{ $assocColor }} fw-semibold"><i class="las la-chart-bar me-1"></i>{{ str_replace(' Association', '', $analytic->association_level) }}</span>
                                        </td>
                                        <td>
                                            <div><strong>{{ $analytic->supporting_medicines_count }}</strong> Meds</div>
                                            <div class="text-muted small">across {{ $analytic->supporting_records_count }} doc(s)</div>
                                        </td>
                                        <td>{{ $analytic->latest_detected_date?->format('M d, Y') ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColor = match ($analytic->status) {
                                                    'confirmed_pattern' => 'success',
                                                    'not_applicable' => 'secondary',
                                                    'needs_review' => 'danger',
                                                    default => 'warning'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} text-capitalize px-2 py-1">
                                                {{ str_replace('_', ' ', $analytic->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.health-analytics.patients.show', $analytic->patient) }}" class="btn btn-sm btn-outline-primary" style="color: #CD0B6F; border-color: #CD0B6F;">
                                                View Insight
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="iconoir-activity fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            No patient health analytics records found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $analytics->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
