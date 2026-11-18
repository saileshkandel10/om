@extends('layouts.admin')

@section('title', __('Clinical Review Queue'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Clinical Review Queue</h4>
                    <p class="text-muted small mb-0">Professional medical moderation and verification of analytical patterns.</p>
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

    <!-- Review Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.health-analytics.reviews.index', ['status' => 'pending_review']) }}" class="card border-0 shadow-sm rounded-4 text-decoration-none h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Pending Review</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $pendingCount }}</h3>
                    </div>
                    <div class="p-2 rounded-3 bg-warning-subtle text-warning">
                        <i class="iconoir-clock fs-4"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.health-analytics.reviews.index', ['status' => 'confirmed_pattern']) }}" class="card border-0 shadow-sm rounded-4 text-decoration-none h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Confirmed Patterns</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $confirmedCount }}</h3>
                    </div>
                    <div class="p-2 rounded-3 bg-success-subtle text-success">
                        <i class="iconoir-check-circle fs-4"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.health-analytics.reviews.index', ['status' => 'needs_review']) }}" class="card border-0 shadow-sm rounded-4 text-decoration-none h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Needs Further Review</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $needsReviewCount }}</h3>
                    </div>
                    <div class="p-2 rounded-3 bg-danger-subtle text-danger">
                        <i class="iconoir-warning-triangle fs-4"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Review Queue Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title fw-bold mb-0">Patterns Awaiting Moderation</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Health Category</th>
                            <th>Association Level</th>
                            <th>Supporting Evidence</th>
                            <th>Current Status</th>
                            <th class="text-end">Review Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($analytics as $an)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $an->patient->name }}</div>
                                    <small class="text-muted">P-{{ $an->patient->id }} • {{ $an->patient->email }}</small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $an->healthCategory->color }}20; color: {{ $an->healthCategory->color }}; border: 1px solid {{ $an->healthCategory->color }}40;">
                                        {{ $an->healthCategory->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $an->association_level }}</span>
                                </td>
                                <td>
                                    <small>{{ $an->supporting_medicines_count }} med(s) across {{ $an->supporting_records_count }} record(s)</small>
                                </td>
                                <td>
                                    @php
                                        $statusColor = match ($an->status) {
                                            'confirmed_pattern' => 'success',
                                            'not_applicable' => 'secondary',
                                            'needs_review' => 'danger',
                                            default => 'warning'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} text-capitalize px-2 py-1">
                                        {{ str_replace('_', ' ', $an->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST" action="{{ route('admin.health-analytics.reviews.submit', $an) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="confirmed_pattern">
                                            <button type="submit" class="btn btn-sm btn-success px-2 fw-semibold" title="Confirm Pattern">
                                                <i class="iconoir-check"></i> Confirm
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.health-analytics.reviews.submit', $an) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="not_applicable">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="Mark Not Applicable">
                                                N/A
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.health-analytics.patients.show', $an->patient) }}" class="btn btn-sm btn-light border px-2" title="Inspect Full Case">
                                            <i class="iconoir-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="iconoir-check-circle fs-1 text-success mb-2 d-block"></i>
                                    No patterns currently pending clinical review.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $analytics->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
