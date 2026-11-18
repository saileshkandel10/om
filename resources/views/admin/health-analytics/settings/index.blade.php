@extends('layouts.admin')

@section('title', __('Health Analytics Settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Health Analytics Configuration</h4>
                    <p class="text-muted small mb-0">Engine sensitivity, automatic analysis pipeline, and safety disclaimers.</p>
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Analytical Engine Parameters</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.health-analytics.settings.update') }}">
                        @csrf
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_analyze_bills" name="auto_analyze_bills" value="1" @checked($autoAnalyzeBills)>
                                <label class="form-check-label fw-bold" for="auto_analyze_bills">Automatic OCR Pipeline Analysis</label>
                            </div>
                            <small class="text-muted d-block mt-1">Automatically trigger pattern extraction and category mapping as soon as a purchase bill OCR job completes.</small>
                        </div>

                        <div class="mb-4">
                            <label for="min_records_pattern" class="form-label fw-semibold">Minimum Records for Recurring Pattern</label>
                            <input type="number" class="form-control form-control-sm" id="min_records_pattern" name="min_records_pattern" value="{{ $minimumRecordsForPattern }}" min="1" max="10" required>
                            <small class="text-muted">Number of separate bill/prescription occurrences required before strengthening to a recurring medication pattern.</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold" style="background-color: #CD0B6F; border-color: #CD0B6F;">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pre-defined Categories Summary -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Registered Health Categories ({{ $categories->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($categories as $cat)
                            <div class="col-md-6">
                                <div class="p-2 border rounded-3 d-flex align-items-center justify-content-between">
                                    <span class="badge" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40;">
                                        <i class="{{ $cat->icon }} me-1"></i> {{ $cat->name }}
                                    </span>
                                    <span class="badge bg-light text-muted border text-capitalize">{{ $cat->status }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
