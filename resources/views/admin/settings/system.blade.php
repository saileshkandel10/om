@extends('layouts.admin')

@section('title', __('System Settings & Verification Rules'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">System Settings & Verification Rules</h4>
                    <p class="text-muted small mb-0">Configure OCR auto-verification thresholds, loyalty point rates, and system policies.</p>
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
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title fw-bold">Verification Threshold & Reward Parameters</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.system.update') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="ocr_verification_threshold" class="form-label fw-semibold">OCR Auto-Verification Threshold (Rs)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rs</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="ocr_verification_threshold" name="ocr_verification_threshold" value="{{ old('ocr_verification_threshold', $threshold) }}" required>
                            </div>
                            <small class="text-muted">Bills with total amount equal to or above this threshold require manual admin verification. Bills below are auto-verified by OCR.</small>
                        </div>

                        <div class="mb-4">
                            <label for="default_point_rate" class="form-label fw-semibold">Default Loyalty Point Accrual Rate (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control" id="default_point_rate" name="default_point_rate" value="{{ old('default_point_rate', $pointRate) }}" required>
                                <span class="input-group-text bg-light">%</span>
                            </div>
                            <small class="text-muted">Percentage of purchase bill total awarded as loyalty reward points.</small>
                        </div>

                        <div class="mb-4">
                            <label for="shareholder_royalty_rate" class="form-label fw-semibold">Shareholder Royalty Rate (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control" id="shareholder_royalty_rate" name="shareholder_royalty_rate" value="{{ old('shareholder_royalty_rate', $royaltyRate) }}" required>
                                <span class="input-group-text bg-light">%</span>
                            </div>
                            <small class="text-muted">Percentage of purchase bill amount credited as shareholder royalty.</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" style="background-color: #CD0B6F; border-color: #CD0B6F; border-radius: 12px;">
                                <i class="las la-save me-1"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
