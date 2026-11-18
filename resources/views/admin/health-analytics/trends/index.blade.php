@extends('layouts.admin')

@section('title', __('Health Category Trends'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Health Category Trends</h4>
                    <p class="text-muted small mb-0">Macro therapeutic frequency, patient prevalence, and analytical category growth.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($categories as $cat)
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40;">
                                <i class="{{ $cat->icon }} me-1"></i> {{ $cat->name }}
                            </span>
                            <span class="fw-bold fs-18 text-dark">{{ $cat->patient_analytics_count }}</span>
                        </div>
                        <p class="small text-muted mb-2">{{ Str::limit($cat->description, 60) }}</p>
                        <div class="d-flex justify-content-between text-muted small border-top pt-2">
                            <span>Mapped Meds: <strong>{{ $cat->medicine_indications_count }}</strong></span>
                            <span>Status: <strong class="text-success text-capitalize">{{ $cat->status }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Monthly Category Trends Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title fw-bold mb-0">Monthly Detection Trends</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Health Category</th>
                            <th>Month</th>
                            <th class="text-end">Detected Patients</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthlyCategoryTrends as $trend)
                            <tr>
                                <td class="fw-semibold">{{ $trend->category_name }}</td>
                                <td>{{ $trend->month }}</td>
                                <td class="text-end fw-bold text-primary">{{ $trend->count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No monthly trend data available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
