@extends('layouts.admin')

@section('title', 'Product Demand & Purchasing Behaviour Report')

@push('styles')
<style>
    .kpi-box {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 10px;
    }
    .kpi-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .kpi-icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .table-hover tbody tr {
        cursor: pointer;
    }
    .nav-tabs .nav-link {
        color: #475569;
        font-weight: 500;
        padding: 8px 16px;
    }
    .nav-tabs .nav-link.active {
        color: #CD0B6F;
        border-bottom: 2px solid #CD0B6F;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-chart-pie text-primary me-1"></i> Product Demand &amp; Purchasing Behaviour
                    </h4>
                    <p class="text-muted small mb-0">Analyze product trends, customer purchasing patterns, and demand across all 77 districts of Nepal.</p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    {{-- Export Dropdown --}}
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export Report
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">Export Format</h6></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'product_demand'])) }}">
                                    <i class="las la-file-excel text-success me-2 fs-16"></i> Export Product Demand (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'district_demand'])) }}">
                                    <i class="las la-map-marked-alt text-primary me-2 fs-16"></i> Export District Demand (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'customer_behaviour'])) }}">
                                    <i class="las la-user-clock text-info me-2 fs-16"></i> Export Customer Behaviour (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'product_district'])) }}">
                                    <i class="las la-table text-warning me-2 fs-16"></i> Export Product × District (.xlsx)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.export', array_merge(request()->all(), ['format' => 'xlsx', 'export_type' => 'complete'])) }}">
                                    <i class="las la-layer-group text-danger me-2 fs-16"></i> Export Complete Report (.xlsx)
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.export', array_merge(request()->all(), ['format' => 'csv', 'export_type' => 'product_demand'])) }}">
                                    <i class="las la-file-csv text-secondary me-2 fs-16"></i> Export CSV (.csv)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.product-demand.print', request()->all()) }}" target="_blank">
                                    <i class="las la-print text-dark me-2 fs-16"></i> Print / PDF Report
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('admin.settings.locations.districts') }}" class="btn btn-sm btn-outline-primary">
                        <i class="las la-map-pin me-1"></i> Nepal 77 Districts
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Comprehensive Filter Section --}}
    <div class="row">
        <div class="col-12">
            <div class="card border mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-filter text-primary me-1"></i> Report Filters &amp; Parameters
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $activeCount = count(array_filter(request()->except(['page', '_token', 'per_page'])));
                        @endphp
                        @if($activeCount > 0)
                            <span class="badge bg-primary">{{ $activeCount }} Filter(s) Active</span>
                        @endif
                        <a href="{{ route('admin.reports.product-demand') }}" class="btn btn-xs btn-outline-danger">
                            <i class="las la-undo-alt me-1"></i> Reset
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.reports.product-demand') }}" id="demandFilterForm">
                        <div class="row g-2">
                            {{-- Date Preset --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date Period</label>
                                <select name="date_preset" class="form-select form-select-sm" id="demandDatePreset" onchange="toggleDemandCustomDates(this.value)">
                                    <option value="today" {{ request('date_preset') === 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="yesterday" {{ request('date_preset') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                    <option value="last_7_days" {{ request('date_preset') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="last_30_days" {{ request('date_preset', 'last_30_days') === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="this_month" {{ request('date_preset') === 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month" {{ request('date_preset') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="this_quarter" {{ request('date_preset') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                                    <option value="this_year" {{ request('date_preset') === 'this_year' ? 'selected' : '' }}>This Year</option>
                                    <option value="all_time" {{ request('date_preset') === 'all_time' ? 'selected' : '' }}>All Time</option>
                                    <option value="custom" {{ request('date_preset') === 'custom' || request('date_from') ? 'selected' : '' }}>Custom Range</option>
                                </select>
                            </div>

                            {{-- Custom Date Inputs --}}
                            <div class="col-md-2 col-sm-6 demand-custom-dates" style="{{ request('date_preset') === 'custom' || request('date_from') ? '' : 'display:none;' }}">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date From</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2 col-sm-6 demand-custom-dates" style="{{ request('date_preset') === 'custom' || request('date_from') ? '' : 'display:none;' }}">
                                <label class="form-label form-label-sm fw-semibold mb-1">Date To</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                            </div>

                            {{-- Province Filter --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Province</label>
                                <select name="province_id" class="form-select form-select-sm" id="provinceFilterSelect" onchange="filterDistrictsByProvince(this.value)">
                                    <option value="all">All 7 Provinces</option>
                                    @foreach($reportData['provinces'] as $prov)
                                        <option value="{{ $prov->id }}" {{ (string) request('province_id') === (string) $prov->id ? 'selected' : '' }}>
                                            {{ $prov->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- District Filter --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">District (77)</label>
                                <select name="district_id" class="form-select form-select-sm" id="districtFilterSelect">
                                    <option value="all">All 77 Districts</option>
                                    @foreach($reportData['districts'] as $dist)
                                        <option value="{{ $dist->id }}"
                                                data-province="{{ $dist->province_id }}"
                                                {{ (string) request('district_id') === (string) $dist->id ? 'selected' : '' }}>
                                            {{ $dist->name }} ({{ $dist->province?->name ?? 'Nepal' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product Category --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Product Category</label>
                                <select name="category_id" class="form-select form-select-sm">
                                    <option value="all">All Categories</option>
                                    @foreach($reportData['categories'] as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Specific Product --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Specific Product</label>
                                <select name="product_id" class="form-select form-select-sm">
                                    <option value="all">All Products</option>
                                    @foreach($reportData['products'] as $p)
                                        <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>
                                            {{ $p->brand_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Customer Type --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Customer Type</label>
                                <select name="customer_type" class="form-select form-select-sm">
                                    <option value="all">All Customers</option>
                                    <option value="new" {{ request('customer_type') === 'new' ? 'selected' : '' }}>New Customers</option>
                                    <option value="returning" {{ request('customer_type') === 'returning' ? 'selected' : '' }}>Returning Customers</option>
                                </select>
                            </div>

                            {{-- Order Status --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Order Status</label>
                                <select name="order_status" class="form-select form-select-sm">
                                    <option value="all">All Valid Orders</option>
                                    <option value="delivered" {{ request('order_status') === 'delivered' ? 'selected' : '' }}>Delivered / Approved</option>
                                    <option value="confirmed" {{ request('order_status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="processing" {{ request('order_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="dispatched" {{ request('order_status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="pending" {{ request('order_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="cancelled" {{ request('order_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            {{-- Payment Status --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Payment Status</label>
                                <select name="payment_status" class="form-select form-select-sm">
                                    <option value="all">All Payment Statuses</option>
                                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="cod" {{ request('payment_status') === 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>

                            {{-- Brand / Manufacturer --}}
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label form-label-sm fw-semibold mb-1">Brand / Search</label>
                                <input type="text" name="brand" class="form-control form-control-sm" placeholder="e.g. Om Mega, Corex..." value="{{ request('brand') }}">
                            </div>

                            {{-- Filter Buttons --}}
                            <div class="col-md-2 col-sm-6 d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                    <i class="las la-check me-1"></i> Apply
                                </button>
                                <a href="{{ route('admin.reports.product-demand') }}" class="btn btn-sm btn-outline-secondary">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 8 Dashboard KPI Summary Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Orders --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Total Orders / Bills</span>
                            <h3 class="mb-0 fw-bold text-primary mt-1">{{ number_format($reportData['summary']['total_orders']) }}</h3>
                            <small class="text-muted">In selected period</small>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="las la-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Products Sold --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Total Products Sold</span>
                            <h3 class="mb-0 fw-bold text-success mt-1">{{ number_format($reportData['summary']['total_units_sold']) }}</h3>
                            <small class="text-muted">Total units dispatched</small>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="las la-box-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Unique Products Purchased --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Unique Products</span>
                            <h3 class="mb-0 fw-bold text-info mt-1">{{ number_format($reportData['summary']['unique_products_purchased']) }}</h3>
                            <small class="text-muted">Distinct SKUs sold</small>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="las la-tags"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Revenue --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Total Sales Revenue</span>
                            <h3 class="mb-0 fw-bold text-dark mt-1">Rs. {{ number_format($reportData['summary']['total_revenue'], 2) }}</h3>
                            <small class="text-muted">AOV: Rs. {{ number_format($reportData['summary']['average_order_value'], 2) }}</small>
                        </div>
                        <div class="kpi-icon-circle bg-dark-subtle text-dark">
                            <i class="las la-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 5: Total Customers --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Unique Customers</span>
                            <h3 class="mb-0 fw-bold text-primary mt-1">{{ number_format($reportData['summary']['total_customers']) }}</h3>
                            <small class="text-muted">Active buying cohort</small>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="las la-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 6: Repeat Customers --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Repeat Customers</span>
                            <h3 class="mb-0 fw-bold text-warning mt-1">{{ number_format($reportData['summary']['repeat_customers']) }}</h3>
                            <small class="text-muted">{{ $reportData['summary']['repeat_customer_rate'] }}% repeat purchase rate</small>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="las la-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 7: Most Demanded Product --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Most Demanded Product</span>
                            <h5 class="mb-0 fw-bold text-danger mt-1 text-truncate" style="max-width: 170px;" title="{{ $reportData['summary']['most_demanded_product'] }}">
                                {{ $reportData['summary']['most_demanded_product'] }}
                            </h5>
                            <small class="text-muted">Top sales volume SKU</small>
                        </div>
                        <div class="kpi-icon-circle bg-danger-subtle text-danger">
                            <i class="las la-award"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 8: Top Purchasing District --}}
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-box border bg-white shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold fs-11">Top Purchasing District</span>
                            <h5 class="mb-0 fw-bold text-primary mt-1">{{ $reportData['summary']['top_purchasing_district'] }}</h5>
                            <small class="text-muted">{{ $reportData['district_coverage']['coverage_percentage'] }}% Nepal Coverage ({{ $reportData['district_coverage']['covered_districts_count'] }}/77)</small>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="las la-map-marker-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Visual Analytics (Charts) --}}
    <div class="row g-3 mb-4">
        {{-- Chart 1: Product Demand Over Time --}}
        <div class="col-lg-8">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-chart-line text-primary me-1"></i> Product Demand Trend ({{ ucfirst($reportData['time_series_demand']['mode']) }})
                    </h6>
                    <span class="badge bg-light text-dark border">Units &amp; Orders</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 280px; position: relative;">
                        <canvas id="demandTimeSeriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Customer Purchasing Behaviour (New vs Returning) --}}
        <div class="col-lg-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-users-cog text-warning me-1"></i> New vs Returning Customers
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 200px; position: relative;">
                        <canvas id="customerTypeDoughnutChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-around text-center mt-3 small text-muted">
                        <div><strong class="text-primary">{{ number_format($reportData['summary']['new_customers']) }}</strong> New</div>
                        <div><strong class="text-warning">{{ number_format($reportData['summary']['repeat_customers']) }}</strong> Returning ({{ $reportData['summary']['repeat_customer_rate'] }}%)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 3: Top 10 Purchasing Districts --}}
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-map-marked-alt text-success me-1"></i> Top 10 Purchasing Districts
                    </h6>
                    <span class="badge bg-light text-muted border">Total Orders</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="topDistrictsBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 4: Purchase Frequency Distribution --}}
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-history text-info me-1"></i> Customer Purchase Frequency
                    </h6>
                    <span class="badge bg-light text-muted border">Repeat Loyalty</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="purchaseFrequencyBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Tables & Deep Analytics Tabs --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header bg-light py-2">
                    <ul class="nav nav-tabs card-header-tabs" id="demandAnalyticsTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="trending-tab" data-bs-toggle="tab" data-bs-target="#trendingPane" type="button" role="tab">
                                <i class="las la-fire text-danger me-1"></i> Top Trending Products
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="districts-tab" data-bs-toggle="tab" data-bs-target="#districtsPane" type="button" role="tab">
                                <i class="las la-map-marked text-primary me-1"></i> District Demand (77 Districts)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="provinces-tab" data-bs-toggle="tab" data-bs-target="#provincesPane" type="button" role="tab">
                                <i class="las la-landmark text-success me-1"></i> Province Demand
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="matrix-tab" data-bs-toggle="tab" data-bs-target="#matrixPane" type="button" role="tab">
                                <i class="las la-th text-info me-1"></i> Product × District Matrix
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="coverage-tab" data-bs-toggle="tab" data-bs-target="#coveragePane" type="button" role="tab">
                                <i class="las la-globe-asia text-warning me-1"></i> Nepal Coverage ({{ $reportData['district_coverage']['coverage_percentage'] }}%)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="behaviour-tab" data-bs-toggle="tab" data-bs-target="#behaviourPane" type="button" role="tab">
                                <i class="las la-calendar-alt text-dark me-1"></i> Monthly Behaviour &amp; Peak Hours
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="demandAnalyticsContent">
                        {{-- Tab 1: Top Trending Products --}}
                        <div class="tab-pane fade show active p-3" id="trendingPane" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Showing {{ count($reportData['trending_products']) }} ranked products by demand score &amp; volume growth. Click any product to drill down.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th class="text-end">Units Sold</th>
                                            <th class="text-end">Previous Units</th>
                                            <th class="text-end">Orders</th>
                                            <th class="text-end">Customers</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Growth</th>
                                            <th class="text-end">Demand Score</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportData['trending_products'] as $prod)
                                            <tr onclick="openProductModal('{{ $prod['product_id'] ?: $prod['name'] }}')">
                                                <td><span class="badge bg-light text-dark border">{{ $prod['rank'] }}</span></td>
                                                <td>
                                                    <strong class="text-dark">{{ $prod['name'] }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $prod['category'] }}</span>
                                                </td>
                                                <td class="text-end fw-bold text-primary">{{ number_format($prod['units_sold']) }}</td>
                                                <td class="text-end text-muted">{{ number_format($prod['previous_units']) }}</td>
                                                <td class="text-end">{{ number_format($prod['orders_count']) }}</td>
                                                <td class="text-end">{{ number_format($prod['customers_count']) }}</td>
                                                <td class="text-end fw-semibold">Rs. {{ number_format($prod['revenue'], 2) }}</td>
                                                <td class="text-end">
                                                    @if($prod['growth_percent'] > 0)
                                                        <span class="badge bg-success-subtle text-success">+{{ $prod['growth_percent'] }}%</span>
                                                    @elseif($prod['growth_percent'] < 0)
                                                        <span class="badge bg-danger-subtle text-danger">{{ $prod['growth_percent'] }}%</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">0%</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-warning text-dark fw-bold">{{ $prod['demand_score'] }}</span>
                                                </td>
                                                <td class="text-end" onclick="event.stopPropagation();">
                                                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="openProductModal('{{ $prod['product_id'] ?: $prod['name'] }}')">
                                                        <i class="las la-search-location me-1"></i> Locations
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center py-4 text-muted">No trending product data found matching the selected filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 2: District Demand (77 Districts) --}}
                        <div class="tab-pane fade p-3" id="districtsPane" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Demand ranking across all 77 districts of Nepal. Click any district to see its top products and recent orders.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rank</th>
                                            <th>District Name</th>
                                            <th>Province</th>
                                            <th class="text-end">Orders</th>
                                            <th class="text-end">Customers</th>
                                            <th class="text-end">Products Sold</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">% of Total Orders</th>
                                            <th class="text-end">% of Total Sales</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportData['district_demand'] as $d)
                                            <tr onclick="openDistrictModal({{ $d['district_id'] }})">
                                                <td><span class="badge bg-light text-dark border">{{ $d['rank'] }}</span></td>
                                                <td><strong>{{ $d['name'] }}</strong></td>
                                                <td><span class="badge bg-light text-dark border">{{ $d['province_name'] }}</span></td>
                                                <td class="text-end fw-bold text-primary">{{ number_format($d['orders_count']) }}</td>
                                                <td class="text-end">{{ number_format($d['customers_count']) }}</td>
                                                <td class="text-end">{{ number_format($d['units_sold']) }}</td>
                                                <td class="text-end fw-semibold">Rs. {{ number_format($d['revenue'], 2) }}</td>
                                                <td class="text-end">{{ $d['order_percentage'] }}%</td>
                                                <td class="text-end">{{ $d['revenue_percentage'] }}%</td>
                                                <td class="text-end" onclick="event.stopPropagation();">
                                                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="openDistrictModal({{ $d['district_id'] }})">
                                                        <i class="las la-eye me-1"></i> Drill Down
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">No district records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 3: Province Demand --}}
                        <div class="tab-pane fade p-3" id="provincesPane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Province</th>
                                            <th>Code</th>
                                            <th>Total Districts</th>
                                            <th class="text-end">Orders</th>
                                            <th class="text-end">Customers</th>
                                            <th class="text-end">Units Sold</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">% of Total Sales</th>
                                            <th class="text-end">Filter</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['province_demand'] as $prov)
                                            <tr>
                                                <td><strong>{{ $prov['name'] }}</strong></td>
                                                <td><code>{{ $prov['code'] }}</code></td>
                                                <td>{{ $prov['districts_count'] }} districts</td>
                                                <td class="text-end fw-bold text-primary">{{ number_format($prov['orders_count']) }}</td>
                                                <td class="text-end">{{ number_format($prov['customers_count']) }}</td>
                                                <td class="text-end">{{ number_format($prov['units_sold']) }}</td>
                                                <td class="text-end fw-semibold">Rs. {{ number_format($prov['revenue'], 2) }}</td>
                                                <td class="text-end">{{ $prov['revenue_percentage'] }}%</td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.reports.product-demand', array_merge(request()->except('page'), ['province_id' => $prov['province_id']])) }}"
                                                       class="btn btn-xs btn-outline-primary">
                                                        <i class="las la-filter me-1"></i> Filter Province
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 4: Product × District Matrix --}}
                        <div class="tab-pane fade p-3" id="matrixPane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover align-middle mb-0 text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">District / Product</th>
                                            @foreach($reportData['product_district_matrix']['products'] as $pName)
                                                <th class="small">{{ $pName }}</th>
                                            @endforeach
                                            <th class="bg-primary text-white">Total Units</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportData['product_district_matrix']['matrix'] as $distName => $row)
                                            <tr>
                                                <td class="text-start fw-semibold">{{ $distName }}</td>
                                                @foreach($reportData['product_district_matrix']['products'] as $pName)
                                                    <td>
                                                        @if(($row[$pName] ?? 0) > 0)
                                                            <span class="badge bg-primary-subtle text-primary">{{ number_format($row[$pName]) }}</span>
                                                        @else
                                                            <span class="text-muted">0</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="fw-bold text-primary">{{ number_format($row['_total'] ?? 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">No cross-tabulation data available for current selection.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 5: Nepal 77 Districts Coverage --}}
                        <div class="tab-pane fade p-3" id="coveragePane" role="tabpanel">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center bg-light">
                                        <span class="text-muted small">Districts With Orders</span>
                                        <h3 class="mb-0 text-success fw-bold">{{ $reportData['district_coverage']['covered_districts_count'] }} / 77</h3>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $reportData['district_coverage']['coverage_percentage'] }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center bg-light">
                                        <span class="text-muted small">Districts With Zero Orders</span>
                                        <h3 class="mb-0 text-danger fw-bold">{{ $reportData['district_coverage']['zero_demand_districts_count'] }}</h3>
                                        <small class="text-muted">Geographic expansion opportunities</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center bg-light">
                                        <span class="text-muted small">National Coverage Rate</span>
                                        <h3 class="mb-0 text-primary fw-bold">{{ $reportData['district_coverage']['coverage_percentage'] }}%</h3>
                                        <small class="text-muted">Of all 77 districts of Nepal</small>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($reportData['district_coverage']['zero_demand_districts']))
                                <h6 class="fw-bold mb-2">Zero-Demand Districts (Expansion Opportunities)</h6>
                                <div class="row g-2">
                                    @foreach($reportData['district_coverage']['zero_demand_districts'] as $zd)
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="p-2 border rounded bg-white d-flex justify-content-between align-items-center">
                                                <span class="fw-medium text-dark">{{ $zd['name'] }}</span>
                                                <span class="badge bg-light text-muted border">{{ $zd['province_name'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Tab 6: Monthly Behaviour & Peak Hours --}}
                        <div class="tab-pane fade p-3" id="behaviourPane" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-2 fs-14">Orders by Day of Week</h6>
                                    <div style="height: 200px; position: relative;">
                                        <canvas id="dayOfWeekChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-2 fs-14">Orders by Hour of Day (Peak Times)</h6>
                                    <div style="height: 200px; position: relative;">
                                        <canvas id="hourlyDemandChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-2 fs-14">Monthly Customer Purchasing Metrics</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th class="text-end">New Customers</th>
                                            <th class="text-end">Returning Customers</th>
                                            <th class="text-end">Total Orders</th>
                                            <th class="text-end">Average Order Value</th>
                                            <th class="text-end">Repeat Rate</th>
                                            <th class="text-end">Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['customer_behaviour']['monthly_behaviour'] as $mb)
                                            <tr>
                                                <td><strong>{{ $mb['month'] }}</strong></td>
                                                <td class="text-end text-primary fw-semibold">{{ number_format($mb['new_customers']) }}</td>
                                                <td class="text-end text-warning fw-semibold">{{ number_format($mb['returning_customers']) }}</td>
                                                <td class="text-end fw-bold">{{ number_format($mb['orders_count']) }}</td>
                                                <td class="text-end">Rs. {{ number_format($mb['aov'], 2) }}</td>
                                                <td class="text-end"><span class="badge bg-light text-dark border">{{ $mb['repeat_rate'] }}%</span></td>
                                                <td class="text-end fw-bold">Rs. {{ number_format($mb['revenue'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Product Drill-Down Modal --}}
<div class="modal fade" id="productDrilldownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fs-15 fw-bold"><i class="las la-box text-primary me-1"></i> Product Geographic Demand Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="productModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading product demand breakdown...
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- District Drill-Down Modal --}}
<div class="modal fade" id="districtDrilldownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fs-15 fw-bold"><i class="las la-map-pin text-primary me-1"></i> District Purchasing Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="districtModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading district demand profile...
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function toggleDemandCustomDates(val) {
        const inputs = document.querySelectorAll('.demand-custom-dates');
        inputs.forEach(el => el.style.display = (val === 'custom') ? '' : 'none');
    }

    function filterDistrictsByProvince(provinceId) {
        const select = document.getElementById('districtFilterSelect');
        const options = select.querySelectorAll('option');
        options.forEach(opt => {
            if (opt.value === 'all') {
                opt.style.display = '';
                return;
            }
            const pId = opt.getAttribute('data-province');
            if (provinceId === 'all' || pId === provinceId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function openProductModal(identifier) {
        const modal = new bootstrap.Modal(document.getElementById('productDrilldownModal'));
        const body = document.getElementById('productModalBody');
        body.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...</div>';
        modal.show();

        fetch("{{ url('admin/reports/product-demand/product') }}/" + encodeURIComponent(identifier))
            .then(res => res.text())
            .then(html => body.innerHTML = html)
            .catch(() => body.innerHTML = '<div class="alert alert-danger py-2">Failed to load product details.</div>');
    }

    function openDistrictModal(districtId) {
        const modal = new bootstrap.Modal(document.getElementById('districtDrilldownModal'));
        const body = document.getElementById('districtModalBody');
        body.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading...</div>';
        modal.show();

        fetch("{{ url('admin/reports/product-demand/district') }}/" + districtId)
            .then(res => res.text())
            .then(html => body.innerHTML = html)
            .catch(() => body.innerHTML = '<div class="alert alert-danger py-2">Failed to load district details.</div>');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const timeSeries = @json($reportData['time_series_demand']);
        const summary = @json($reportData['summary']);
        const topDistricts = @json(array_slice($reportData['district_demand'], 0, 10));
        const freqBrackets = @json($reportData['customer_behaviour']['frequency_brackets']);
        const timeAnalysis = @json($reportData['time_analysis']);

        // 1. Time Series Line Chart
        if (document.getElementById('demandTimeSeriesChart')) {
            new Chart(document.getElementById('demandTimeSeriesChart'), {
                type: 'line',
                data: {
                    labels: timeSeries.labels || [],
                    datasets: [
                        {
                            label: 'Units Sold',
                            data: timeSeries.units || [],
                            borderColor: '#CD0B6F',
                            backgroundColor: '#CD0B6F20',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2,
                        },
                        {
                            label: 'Orders',
                            data: timeSeries.orders || [],
                            borderColor: '#3b82f6',
                            backgroundColor: '#3b82f610',
                            tension: 0.3,
                            fill: false,
                            borderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // 2. Customer Type Doughnut
        if (document.getElementById('customerTypeDoughnutChart')) {
            new Chart(document.getElementById('customerTypeDoughnutChart'), {
                type: 'doughnut',
                data: {
                    labels: ['New Customers', 'Returning Customers'],
                    datasets: [{
                        data: [summary.new_customers || 0, summary.repeat_customers || 0],
                        backgroundColor: ['#3b82f6', '#f59e0b'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // 3. Top Districts Bar Chart
        if (document.getElementById('topDistrictsBarChart')) {
            new Chart(document.getElementById('topDistrictsBarChart'), {
                type: 'bar',
                data: {
                    labels: topDistricts.map(d => d.name),
                    datasets: [{
                        label: 'Orders',
                        data: topDistricts.map(d => d.orders_count),
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // 4. Purchase Frequency Bar Chart
        if (document.getElementById('purchaseFrequencyBarChart')) {
            new Chart(document.getElementById('purchaseFrequencyBarChart'), {
                type: 'bar',
                data: {
                    labels: ['1 Order', '2 Orders', '3 Orders', '4 Orders', '5+ Orders'],
                    datasets: [{
                        label: 'Customers',
                        data: Object.values(freqBrackets || {}),
                        backgroundColor: '#0ea5e9',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // 5. Day of Week Chart
        if (document.getElementById('dayOfWeekChart')) {
            new Chart(document.getElementById('dayOfWeekChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(timeAnalysis.days || {}),
                    datasets: [{
                        label: 'Orders by Day',
                        data: Object.values(timeAnalysis.days || {}),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        // 6. Hourly Demand Chart
        if (document.getElementById('hourlyDemandChart')) {
            const hourLabels = Array.from({length: 24}, (_, i) => i + ':00');
            new Chart(document.getElementById('hourlyDemandChart'), {
                type: 'line',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'Hourly Orders',
                        data: timeAnalysis.hours || [],
                        borderColor: '#CD0B6F',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: '#CD0B6F15',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    });
</script>
@endpush
