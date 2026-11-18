@extends('layouts.admin')

@section('title', __('Health Analytics Dashboard'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">🩺 Health Analytics Dashboard</h4>
                    <p class="text-muted small mb-0">Medication-pattern analytics and therapeutic classification from uploaded bills and prescriptions.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.health-analytics.patients.index') }}" class="btn btn-sm btn-primary fw-semibold px-3" style="background-color: #CD0B6F; border-color: #CD0B6F; border-radius: 10px;">
                        <i class="iconoir-user-badge-check me-1"></i> Patient Analytics
                    </a>
                    <a href="{{ route('admin.health-analytics.reviews.index') }}" class="btn btn-sm btn-outline-secondary">
                        Clinical Reviews ({{ $pendingClinicalReviews }})
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Medical Safety Notice -->
    <div class="alert alert-light border shadow-sm rounded-4 d-flex align-items-center p-3 mb-4" role="alert">
        <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px; background-color: #CD0B6F;">
            <i class="iconoir-info-circle fs-5"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold mb-0 text-dark">Analytical Insight Standard</h6>
            <p class="mb-0 small text-muted">Health Analytics identifies observed medication associations from billing and prescription records. It does not provide medical diagnoses or treatment recommendations.</p>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Patients Analyzed</span>
                        <div class="p-2 rounded-3 text-white" style="background-color: #CD0B6F;">
                            <i class="iconoir-user fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalPatientsAnalyzed) }}</h3>
                    <small class="text-muted">Unique patients with pattern data</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Bills Analyzed</span>
                        <div class="p-2 rounded-3 bg-info-subtle text-info">
                            <i class="iconoir-receipt fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBillsAnalyzed) }}</h3>
                    <small class="text-muted">OCR-extracted pharmacy bills</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Medicines Cataloged</span>
                        <div class="p-2 rounded-3 bg-success-subtle text-success">
                            <i class="iconoir-pill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalMedicinesIdentified) }}</h3>
                    <small class="text-muted">Canonical therapeutic mappings</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Categories Detected</span>
                        <div class="p-2 rounded-3 bg-warning-subtle text-warning">
                            <i class="iconoir-activity fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($healthCategoriesDetected) }}</h3>
                    <small class="text-muted">Active therapeutic categories</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Section -->
    <div class="row g-3 mb-4">
        <!-- Category Distribution Chart -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Health Category Distribution</h5>
                </div>
                <div class="card-body">
                    @if ($categoryDistribution->count() > 0)
                        <div style="height: 300px;">
                            <canvas id="categoryDistChart"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="iconoir-activity fs-1 d-block mb-2"></i>
                            <p class="mb-0">No health analytics data available yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Association Levels & Status -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Medication Association Levels</h5>
                </div>
                <div class="card-body">
                    @php
                        $levels = [
                            'Strong Association' => ['color' => '#E74C3C', 'badge' => 'danger'],
                            'Moderate Association' => ['color' => '#F39C12', 'badge' => 'warning'],
                            'Possible Association' => ['color' => '#3498DB', 'badge' => 'info'],
                            'Weak Association' => ['color' => '#95A5A6', 'badge' => 'secondary'],
                        ];
                    @endphp

                    <div class="vstack gap-3 mb-4">
                        @foreach ($levels as $level => $cfg)
                            @php $count = $associationLevels[$level] ?? 0; @endphp
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-{{ $cfg['badge'] }}-subtle text-{{ $cfg['badge'] }} mb-1">{{ $level }}</span>
                                    <div class="small text-muted">Recurring pattern correlation</div>
                                </div>
                                <h4 class="mb-0 fw-bold">{{ $count }}</h4>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-top pt-3 d-flex justify-content-between text-muted small">
                        <span>Repeated Medication Patterns: <strong>{{ $repeatedPatternsCount }}</strong></span>
                        <span>Pending Reviews: <strong class="text-danger">{{ $pendingClinicalReviews }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Medicines & Volume -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Top Detected Medicines</h5>
                    <a href="{{ route('admin.health-analytics.medications.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Medicine Name</th>
                                    <th class="text-end">Detection Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topMedicines as $med)
                                    <tr>
                                        <td class="fw-semibold">{{ $med->medicine_name }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-primary-subtle text-primary px-3 py-1">{{ $med->detections }} records</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">No medicines detected yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Monthly Bill Analysis Volume</h5>
                </div>
                <div class="card-body">
                    @if ($monthlyVolume->count() > 0)
                        <div style="height: 220px;">
                            <canvas id="monthlyVolumeChart"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <p class="mb-0">No monthly volume recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Category Distribution Bar Chart
        @if ($categoryDistribution->count() > 0)
            const catCtx = document.getElementById('categoryDistChart').getContext('2d');
            const catData = @json($categoryDistribution);
            new Chart(catCtx, {
                type: 'bar',
                data: {
                    labels: catData.map(d => d.name),
                    datasets: [{
                        label: 'Patients',
                        data: catData.map(d => d.total),
                        backgroundColor: catData.map(d => d.color || '#CD0B6F'),
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        @endif

        // 2. Monthly Volume Chart
        @if ($monthlyVolume->count() > 0)
            const volCtx = document.getElementById('monthlyVolumeChart').getContext('2d');
            const volData = @json($monthlyVolume);
            new Chart(volCtx, {
                type: 'line',
                data: {
                    labels: volData.map(d => d.month),
                    datasets: [{
                        label: 'Bills Analyzed',
                        data: volData.map(d => d.count),
                        borderColor: '#CD0B6F',
                        backgroundColor: 'rgba(205, 11, 111, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        @endif
    });
</script>
@endpush
@endsection
