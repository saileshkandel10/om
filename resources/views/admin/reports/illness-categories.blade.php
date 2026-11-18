@extends('layouts.admin')

@section('title', 'Illness Category & Patient Analytics Report')

@push('styles')
<style>
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 10px;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .badge-illness {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 500;
    }
    .filter-badge-active {
        background-color: #CD0B6F !important;
        color: #fff !important;
        border-color: #CD0B6F !important;
    }
    .table-hover tbody tr {
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header & Breadcrumbs --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-notes-medical text-primary me-1"></i> Illness Category & Patient Report
                    </h4>
                    <p class="text-muted small mb-0">Multi-category epidemiological tracking, bill detection, patient analytics & multi-illness reporting</p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    {{-- Sync Bills Button --}}
                    <form method="POST" action="{{ route('admin.reports.illness-categories.sync') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Re-scan bills and map to illness categories">
                            <i class="las la-sync me-1"></i> Sync Bills
                        </button>
                    </form>

                    {{-- Export Dropdown --}}
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export Report
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">Export Format</h6></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.illness-categories.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'detailed'])) }}">
                                    <i class="las la-file-excel text-success me-2 fs-16"></i> Export Excel (.xlsx) - Full Details
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.illness-categories.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'current'])) }}">
                                    <i class="las la-file-excel text-success me-2 fs-16"></i> Export Excel (.xlsx) - Current View
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.illness-categories.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'summary'])) }}">
                                    <i class="las la-table text-primary me-2 fs-16"></i> Export Category Summary (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.illness-categories.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'records'])) }}">
                                    <i class="las la-layer-group text-warning me-2 fs-16"></i> Export Illness Records (1 row per illness)
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.illness-categories.export', array_merge(request()->all(), ['format' => 'csv', 'export_type' => 'detailed'])) }}">
                                    <i class="las la-file-csv text-info me-2 fs-16"></i> Export CSV (.csv)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.illness-categories.print', request()->all()) }}" target="_blank">
                                    <i class="las la-print text-dark me-2 fs-16"></i> Print / PDF Report
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('admin.illness-categories.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="las la-tags me-1"></i> Manage Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Session Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="las la-check-circle me-1 fs-18 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Comprehensive Filter Section --}}
    <div class="row">
        <div class="col-12">
            <div class="card border mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-filter text-primary me-1"></i> Filter & Search Patients
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $activeFilterCount = count(array_filter(request()->except(['page', '_token'])));
                        @endphp
                        @if($activeFilterCount > 0)
                            <span class="badge bg-primary">{{ $activeFilterCount }} Filter(s) Applied</span>
                        @endif
                        <a href="{{ route('admin.reports.illness-categories') }}" class="btn btn-xs btn-outline-danger">
                            <i class="las la-undo-alt me-1"></i> Reset All
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.reports.illness-categories') }}" id="filterForm">
                        <div class="row g-2">
                            {{-- Global Search --}}
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Global Patient Search</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="las la-search"></i></span>
                                    <input type="search" name="search" class="form-control form-control-sm"
                                           placeholder="ID (P-1001), Name, Phone..."
                                           value="{{ request('search') }}">
                                </div>
                            </div>

                            {{-- Illness Category Multi-Select / Single --}}
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Illness Category</label>
                                <select name="illness_category_id" class="form-select form-select-sm" id="illnessCategorySelect">
                                    <option value="">All Illness Categories</option>
                                    @foreach($reportData['categories'] as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) request('illness_category_id') === (string) $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }} ({{ $reportData['category_counts'][$cat->name] ?? 0 }} patients)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Match Logic ANY vs ALL --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Match Logic</label>
                                <select name="match_type" class="form-select form-select-sm">
                                    <option value="any" {{ request('match_type', 'any') === 'any' ? 'selected' : '' }}>ANY (Having Any Illness)</option>
                                    <option value="all" {{ request('match_type') === 'all' ? 'selected' : '' }}>ALL (Having All Illnesses)</option>
                                </select>
                            </div>

                            {{-- Date Preset --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date Period</label>
                                <select name="date_preset" class="form-select form-select-sm" id="datePresetSelect" onchange="toggleCustomDates(this.value)">
                                    <option value="all_time" {{ request('date_preset', 'all_time') === 'all_time' ? 'selected' : '' }}>All Time</option>
                                    <option value="today" {{ request('date_preset') === 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="yesterday" {{ request('date_preset') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                    <option value="this_week" {{ request('date_preset') === 'this_week' ? 'selected' : '' }}>This Week</option>
                                    <option value="this_month" {{ request('date_preset') === 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month" {{ request('date_preset') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="this_quarter" {{ request('date_preset') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                                    <option value="this_year" {{ request('date_preset') === 'this_year' ? 'selected' : '' }}>This Year</option>
                                    <option value="custom" {{ request('date_preset') === 'custom' || request('date_from') || request('date_to') ? 'selected' : '' }}>Custom Range</option>
                                </select>
                            </div>

                            {{-- Date Type --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date Type</label>
                                <select name="date_type" class="form-select form-select-sm">
                                    <option value="diagnosis_date" {{ request('date_type', 'diagnosis_date') === 'diagnosis_date' ? 'selected' : '' }}>Diagnosis Date</option>
                                    <option value="visit_date" {{ request('date_type') === 'visit_date' ? 'selected' : '' }}>Visit / Bill Date</option>
                                    <option value="registration_date" {{ request('date_type') === 'registration_date' ? 'selected' : '' }}>Registration Date</option>
                                </select>
                            </div>

                            {{-- Custom Date Range (Conditionally shown/hidden) --}}
                            <div class="col-md-3 col-sm-6 custom-date-inputs" style="{{ request('date_preset') === 'custom' || request('date_from') || request('date_to') ? '' : 'display:none;' }}">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date From</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3 col-sm-6 custom-date-inputs" style="{{ request('date_preset') === 'custom' || request('date_from') || request('date_to') ? '' : 'display:none;' }}">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date To</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                            </div>

                            {{-- Gender Filter --}}
                            <div class="col-md-2 col-sm-4">
                                <label class="form-label form-label-sm fw-semibold mb-1">Gender</label>
                                <select name="gender" class="form-select form-select-sm">
                                    <option value="">All Genders</option>
                                    <option value="male" {{ strtolower(request('gender', '')) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ strtolower(request('gender', '')) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ strtolower(request('gender', '')) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            {{-- Age Range --}}
                            <div class="col-md-2 col-sm-4">
                                <label class="form-label form-label-sm fw-semibold mb-1">Age Range</label>
                                <select name="age_range" class="form-select form-select-sm">
                                    <option value="">All Ages</option>
                                    <option value="0-18" {{ request('age_range') === '0-18' || request('age_range') === '<18' ? 'selected' : '' }}>0–18 yrs</option>
                                    <option value="19-30" {{ request('age_range') === '19-30' || request('age_range') === '18-35' ? 'selected' : '' }}>19–30 yrs</option>
                                    <option value="31-45" {{ request('age_range') === '31-45' || request('age_range') === '36-50' ? 'selected' : '' }}>31–45 yrs</option>
                                    <option value="46-60" {{ request('age_range') === '46-60' || request('age_range') === '51-65' ? 'selected' : '' }}>46–60 yrs</option>
                                    <option value="61+" {{ request('age_range') === '61+' || request('age_range') === '>65' ? 'selected' : '' }}>61+ yrs</option>
                                </select>
                            </div>

                            {{-- Doctor --}}
                            <div class="col-md-2 col-sm-4">
                                <label class="form-label form-label-sm fw-semibold mb-1">Doctor</label>
                                <select name="doctor_id" class="form-select form-select-sm">
                                    <option value="">All Doctors</option>
                                    @foreach($reportData['doctors'] as $doc)
                                        <option value="{{ $doc->id }}" {{ (string) request('doctor_id') === (string) $doc->id ? 'selected' : '' }}>
                                            Dr. {{ $doc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Branch / Pharmacy --}}
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Branch / Pharmacy</label>
                                <select name="pharmacy_id" class="form-select form-select-sm">
                                    <option value="">All Pharmacies / Branches</option>
                                    @foreach($reportData['pharmacies'] as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" {{ (string) request('pharmacy_id') === (string) $pharmacy->id ? 'selected' : '' }}>
                                            {{ $pharmacy->store_name }} ({{ $pharmacy->district ?? 'Nepal' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Medicine / Product --}}
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Medicine / Product</label>
                                <select name="product_id" class="form-select form-select-sm">
                                    <option value="">All Medicines</option>
                                    @foreach($reportData['products'] as $prod)
                                        <option value="{{ $prod->id }}" {{ (string) request('product_id') === (string) $prod->id ? 'selected' : '' }}>
                                            {{ $prod->brand_name }}
                                            @if($prod->illnessCategory) [{{ $prod->illnessCategory->name }}] @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Location Filter --}}
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Location / District</label>
                                <input type="text" name="location" class="form-control form-control-sm"
                                       placeholder="Kathmandu, Lalitpur, Pokhara..."
                                       value="{{ request('location') }}">
                            </div>

                            {{-- Patient Status --}}
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                        </div>

                        {{-- Quick Illness Category Tags / Badges --}}
                        <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center gap-1">
                            <span class="text-muted small fw-semibold me-2">Quick Illness Filter:</span>
                            <a href="{{ route('admin.reports.illness-categories', array_merge(request()->except(['illness_category_id', 'page']))) }}"
                               class="btn btn-xs {{ empty(request('illness_category_id')) ? 'btn-primary' : 'btn-outline-secondary' }}">
                                All ({{ $reportData['total_unique_patients'] }})
                            </a>
                            @foreach($reportData['categories']->take(8) as $cat)
                                @php
                                    $isSelected = (string) request('illness_category_id') === (string) $cat->id;
                                    $count = $reportData['category_counts'][$cat->name] ?? 0;
                                @endphp
                                <a href="{{ route('admin.reports.illness-categories', array_merge(request()->except('page'), ['illness_category_id' => $cat->id])) }}"
                                   class="btn btn-xs {{ $isSelected ? 'btn-primary filter-badge-active' : 'btn-outline-secondary' }}">
                                    {{ $cat->name }} <span class="badge bg-light text-dark ms-1">{{ $count }}</span>
                                </a>
                            @endforeach
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.reports.illness-categories') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="las la-times me-1"></i> Clear Filters
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="las la-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Summary KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Unique Patients --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-12">Total Unique Patients</span>
                            <h3 class="mb-0 fw-bold text-primary mt-1">{{ number_format($reportData['total_unique_patients']) }}</h3>
                            <small class="text-muted">Distinct individual patients</small>
                        </div>
                        <div class="kpi-icon bg-primary-subtle text-primary">
                            <i class="las la-user-injured"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Illness Records --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-12">Total Illness Records</span>
                            <h3 class="mb-0 fw-bold text-success mt-1">{{ number_format($reportData['total_illness_records']) }}</h3>
                            <small class="text-muted">Patient-illness mappings</small>
                        </div>
                        <div class="kpi-icon bg-success-subtle text-success">
                            <i class="las la-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Multi-Illness Patients --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-12">Multiple Illness Patients</span>
                            <h3 class="mb-0 fw-bold text-warning mt-1">{{ number_format($reportData['multiple_illness_patients_count']) }}</h3>
                            @php
                                $multiPct = $reportData['total_unique_patients'] > 0
                                    ? round(($reportData['multiple_illness_patients_count'] / $reportData['total_unique_patients']) * 100, 1)
                                    : 0;
                            @endphp
                            <small class="text-muted">{{ $multiPct }}% of total cohort</small>
                        </div>
                        <div class="kpi-icon bg-warning-subtle text-warning">
                            <i class="las la-layer-group"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Top / Selected Category --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            @php
                                $selectedCategoryName = !empty(request('illness_category_id'))
                                    ? (collect($reportData['categories'])->firstWhere('id', request('illness_category_id'))?->name ?? 'Selected Category')
                                    : 'Top Illness Category';
                                $topCatCount = !empty(request('illness_category_id'))
                                    ? ($reportData['category_counts'][$selectedCategoryName] ?? 0)
                                    : (!empty($reportData['category_summary']) ? $reportData['category_summary'][0]['unique_patients'] : 0);
                                $topCatName = !empty(request('illness_category_id'))
                                    ? $selectedCategoryName
                                    : (!empty($reportData['category_summary']) ? $reportData['category_summary'][0]['name'] : 'None');
                            @endphp
                            <span class="text-muted text-uppercase fw-semibold fs-12 text-truncate d-block" style="max-width: 170px;">
                                {{ $topCatName }}
                            </span>
                            <h3 class="mb-0 fw-bold text-info mt-1">{{ number_format($topCatCount) }}</h3>
                            <small class="text-muted">Unique patients diagnosed</small>
                        </div>
                        <div class="kpi-icon bg-info-subtle text-info">
                            <i class="las la-heartbeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Visual Analytics Section (Charts) --}}
    <div class="row g-3 mb-4">
        {{-- Chart 1: Illness Distribution --}}
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-chart-bar text-primary me-1"></i> Illness Category Distribution
                    </h6>
                    <span class="badge bg-light text-muted border">Unique Patients</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 260px; position: relative;">
                        <canvas id="illnessDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Monthly Diagnosis & Visit Trends --}}
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-chart-line text-success me-1"></i> Monthly Trend (Top Illnesses)
                    </h6>
                    <span class="badge bg-light text-muted border">6-Month Trend</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 260px; position: relative;">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 3: Gender Distribution --}}
        <div class="col-lg-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-venus-mars text-danger me-1"></i> Gender Demographics
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 200px; position: relative;">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-around text-center mt-2 small text-muted">
                        <div><strong class="text-primary">{{ $reportData['gender_distribution']['male'] }}</strong> Male</div>
                        <div><strong class="text-danger">{{ $reportData['gender_distribution']['female'] }}</strong> Female</div>
                        <div><strong class="text-secondary">{{ $reportData['gender_distribution']['other'] }}</strong> Other</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 4: Age Demographics --}}
        <div class="col-lg-8">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-birthday-cake text-warning me-1"></i> Patient Age Distribution Brackets
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 200px; position: relative;">
                        <canvas id="ageDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Illness Category Summary Table --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-table text-primary me-1"></i> Illness Category Summary Breakdown
                    </h6>
                    <span class="text-muted small">Click any category to filter patient records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Illness Category</th>
                                    <th>Status</th>
                                    <th class="text-end">Unique Patients</th>
                                    <th style="min-width: 150px;">% of Patients</th>
                                    <th class="text-end">Male</th>
                                    <th class="text-end">Female</th>
                                    <th class="text-end">Multiple Illnesses</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['category_summary'] as $cat)
                                    @php
                                        $isActive = (string) request('illness_category_id') === (string) $cat['id'];
                                    @endphp
                                    <tr class="{{ $isActive ? 'table-primary' : '' }}"
                                        onclick="window.location='{{ route('admin.reports.illness-categories', array_merge(request()->except('page'), ['illness_category_id' => $cat['id']])) }}'">
                                        <td>
                                            <strong class="text-dark">{{ $cat['name'] }}</strong>
                                            @if(!empty($cat['description']))
                                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $cat['description'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if(($cat['status'] ?? 'active') === 'active')
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($cat['unique_patients']) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $cat['percentage'] }}%;"></div>
                                                </div>
                                                <span class="small fw-semibold text-muted" style="min-width: 40px;">{{ $cat['percentage'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-end text-muted">{{ number_format($cat['male']) }}</td>
                                        <td class="text-end text-muted">{{ number_format($cat['female']) }}</td>
                                        <td class="text-end">
                                            @if($cat['multiple_illness'] > 0)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                    {{ number_format($cat['multiple_illness']) }}
                                                </span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <a href="{{ route('admin.reports.illness-categories', array_merge(request()->except('page'), ['illness_category_id' => $cat['id']])) }}"
                                               class="btn btn-xs btn-outline-primary">
                                                <i class="las la-filter"></i> View Patients
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No illness categories found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Multi-Illness Analysis Section --}}
    <div class="row g-3 mb-4">
        {{-- Distribution by Number of Illnesses --}}
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-cubes text-warning me-1"></i> Multi-Illness Distribution
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        @php
                            $totalPts = max(1, $reportData['total_unique_patients']);
                        @endphp
                        <div class="list-group-item px-0 py-2 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary me-2">1 Illness</span>
                                <span class="fw-semibold">Single Diagnosis Patients</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-15">{{ number_format($reportData['distribution']['1']) }}</span>
                                <span class="text-muted small ms-1">({{ round(($reportData['distribution']['1'] / $totalPts) * 100, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-info-subtle text-info me-2">2 Illnesses</span>
                                <span class="fw-semibold">Dual Diagnosis Patients</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-15 text-info">{{ number_format($reportData['distribution']['2']) }}</span>
                                <span class="text-muted small ms-1">({{ round(($reportData['distribution']['2'] / $totalPts) * 100, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-warning-subtle text-warning me-2">3 Illnesses</span>
                                <span class="fw-semibold">Triple Diagnosis Patients</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-15 text-warning">{{ number_format($reportData['distribution']['3']) }}</span>
                                <span class="text-muted small ms-1">({{ round(($reportData['distribution']['3'] / $totalPts) * 100, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-danger-subtle text-danger me-2">4+ Illnesses</span>
                                <span class="fw-semibold">High Complexity (4+ Illnesses)</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold fs-15 text-danger">{{ number_format($reportData['distribution']['4+']) }}</span>
                                <span class="text-muted small ms-1">({{ round(($reportData['distribution']['4+'] / $totalPts) * 100, 1) }}%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Illness Combinations --}}
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-project-diagram text-primary me-1"></i> Top Illness Combinations (Unique Patients)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Illness Combination</th>
                                    <th class="text-end">Unique Patients</th>
                                    <th class="text-end">% of Multi-Illness</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['top_combinations'] as $combo)
                                    <tr>
                                        <td><span class="badge bg-light text-dark border">{{ $combo['rank'] }}</span></td>
                                        <td>
                                            @php
                                                $parts = explode(' + ', $combo['combination']);
                                            @endphp
                                            @foreach($parts as $part)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">{{ $part }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($combo['patients_count']) }}</td>
                                        <td class="text-end text-muted">{{ $combo['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No multi-illness combinations detected in the current cohort.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Patient Report Table --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-light py-2 d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                            <i class="las la-users text-primary me-1"></i> Patient Records
                        </h6>
                        <small class="text-muted">
                            Showing {{ $reportData['patients']->firstItem() ?? 0 }}–{{ $reportData['patients']->lastItem() ?? 0 }} of {{ number_format($reportData['patients']->total()) }} unique patients
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                        {{-- Per Page Selector --}}
                        <form method="GET" action="{{ route('admin.reports.illness-categories') }}" class="d-flex align-items-center gap-1">
                            @foreach(request()->except(['per_page', 'page']) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <label class="small text-muted me-1 mb-0">Show:</label>
                            <select name="per_page" class="form-select form-select-xs" onchange="this.form.submit()" style="width: 70px;">
                                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 50) == '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient Name</th>
                                    <th>Age / Gender</th>
                                    <th>Phone / Location</th>
                                    <th>Illness Categories</th>
                                    @if(!empty(request('illness_category_id')))
                                        <th>Other Illnesses</th>
                                    @endif
                                    <th>Doctor / Branch</th>
                                    <th>Visits</th>
                                    <th>Medicines</th>
                                    <th>Total Amount</th>
                                    <th>Payment Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['patient_rows'] as $patient)
                                    <tr onclick="openPatientModal({{ $patient['id'] }})">
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                                {{ $patient['patient_id'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark">{{ $patient['name'] }}</strong>
                                        </td>
                                        <td>
                                            <span>{{ $patient['age'] }}</span> /
                                            <span class="text-muted">{{ $patient['gender'] }}</span>
                                        </td>
                                        <td>
                                            <div><i class="las la-phone text-muted me-1"></i>{{ $patient['phone'] }}</div>
                                            <small class="text-muted text-truncate d-block" style="max-width: 160px;">{{ $patient['location'] }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($patient['illness_categories'] as $cat)
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle badge-illness">
                                                        {{ $cat->name }}
                                                    </span>
                                                @endforeach
                                                @if($patient['illness_count'] > 1)
                                                    <span class="badge bg-warning text-dark badge-illness">{{ $patient['illness_count'] }} Illnesses</span>
                                                @endif
                                            </div>
                                        </td>
                                        @if(!empty(request('illness_category_id')))
                                            <td>
                                                <small class="text-muted">{{ $patient['other_illnesses'] }}</small>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="small fw-semibold text-dark">{{ $patient['doctor'] }}</div>
                                            <small class="text-muted">{{ $patient['pharmacy'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $patient['visits_count'] }} Visits</span>
                                            <small class="text-muted d-block">{{ $patient['last_visit_date'] }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted text-truncate d-block" style="max-width: 160px;">
                                                {{ $patient['medicines'] }}
                                            </small>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            Rs. {{ number_format($patient['total_amount'], 2) }}
                                        </td>
                                        <td>
                                            @if($patient['payment_status'] === 'Paid')
                                                <span class="badge bg-success-subtle text-success">Paid</span>
                                            @elseif(str_contains($patient['payment_status'], 'Pending'))
                                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Flagged</span>
                                            @endif
                                        </td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="openPatientModal({{ $patient['id'] }})">
                                                <i class="las la-eye me-1"></i> Profile
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4 text-muted">
                                            <i class="las la-search fs-24 d-block mb-1"></i>
                                            No patients found matching the selected filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Pagination Links --}}
                @if($reportData['patients']->hasPages())
                    <div class="card-footer bg-light py-2 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing {{ $reportData['patients']->firstItem() }} to {{ $reportData['patients']->lastItem() }} of {{ $reportData['patients']->total() }} patients
                        </div>
                        <div>
                            {{ $reportData['patients']->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Drill-Down Patient Profile Modal --}}
<div class="modal fade" id="patientDetailModal" tabindex="-1" aria-labelledby="patientDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fs-15 fw-bold" id="patientDetailModalLabel">
                    <i class="las la-user-circle text-primary me-1"></i> Patient Clinical & Purchase Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="patientModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                    Loading patient profile details...
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function toggleCustomDates(value) {
        const customInputs = document.querySelectorAll('.custom-date-inputs');
        customInputs.forEach(el => {
            el.style.display = (value === 'custom') ? '' : 'none';
        });
    }

    // Patient Profile Modal Loader
    function openPatientModal(userId) {
        const modal = new bootstrap.Modal(document.getElementById('patientDetailModal'));
        const modalBody = document.getElementById('patientModalBody');
        modalBody.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading profile...</div>';
        modal.show();

        fetch("{{ url('admin/reports/illness-categories/patient') }}/" + userId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(err => {
            modalBody.innerHTML = '<div class="alert alert-danger py-2 mb-0">Failed to load patient profile details.</div>';
        });
    }

    // Chart.js Visual Analytics Initialization
    document.addEventListener('DOMContentLoaded', function() {
        const categoryData = @json($reportData['illness_distribution']);
        const genderData = @json($reportData['gender_distribution']);
        const ageData = @json($reportData['age_distribution']);
        const trendData = @json($reportData['monthly_trends']);

        // 1. Illness Distribution Bar Chart
        const catLabels = Object.keys(categoryData);
        const catCounts = Object.values(categoryData);

        if (document.getElementById('illnessDistributionChart')) {
            new Chart(document.getElementById('illnessDistributionChart'), {
                type: 'bar',
                data: {
                    labels: catLabels.length > 0 ? catLabels : ['No Data'],
                    datasets: [{
                        label: 'Unique Patients',
                        data: catCounts.length > 0 ? catCounts : [0],
                        backgroundColor: '#CD0B6F',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 2. Gender Doughnut Chart
        if (document.getElementById('genderChart')) {
            new Chart(document.getElementById('genderChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female', 'Other'],
                    datasets: [{
                        data: [genderData.male || 0, genderData.female || 0, genderData.other || 0],
                        backgroundColor: ['#3b82f6', '#ec4899', '#94a3b8'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // 3. Age Distribution Bar Chart
        if (document.getElementById('ageDistributionChart')) {
            new Chart(document.getElementById('ageDistributionChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(ageData),
                    datasets: [{
                        label: 'Patients',
                        data: Object.values(ageData),
                        backgroundColor: '#0ea5e9',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // 4. Monthly Trend Line Chart
        if (document.getElementById('monthlyTrendChart')) {
            const colors = ['#CD0B6F', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'];
            const datasets = (trendData.series || []).map((s, idx) => ({
                label: s.name,
                data: s.data,
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length] + '20',
                fill: false,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 3,
            }));

            new Chart(document.getElementById('monthlyTrendChart'), {
                type: 'line',
                data: {
                    labels: trendData.labels || [],
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    });
</script>
@endpush
