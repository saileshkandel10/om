@extends('layouts.admin')

@section('title', 'Company Product Sales Analytics & Report')

@push('styles')
<style>
    .kpi-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #eef2f6;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    }
    .kpi-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .sales-tabs-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px 0 16px;
    }
    .sales-nav-tabs {
        border-bottom: none;
        gap: 6px;
        margin-bottom: 0;
    }
    .sales-nav-tabs .nav-link {
        font-size: 13.5px;
        font-weight: 500;
        color: #475569;
        padding: 10px 18px;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        background: transparent;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .sales-nav-tabs .nav-link:hover:not(.active) {
        color: #CD0B6F;
        background-color: #f1f5f9;
        border-color: #e2e8f0 #e2e8f0 transparent;
    }
    .sales-nav-tabs .nav-link.active {
        color: #CD0B6F !important;
        font-weight: 600;
        background-color: #ffffff !important;
        border-color: #cbd5e1 #cbd5e1 #ffffff !important;
        box-shadow: 0 -2px 6px rgba(0,0,0,0.03);
    }
    .table-sales th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        background-color: #f8fafc;
        font-weight: 600;
    }
    .badge-soft-success {
        background-color: #dcfce7;
        color: #15803d;
    }
    .badge-soft-warning {
        background-color: #fef9c3;
        color: #a16207;
    }
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    .badge-soft-info {
        background-color: #e0f2fe;
        color: #0369a1;
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
                        <i class="las la-chart-bar text-primary me-1"></i> Company Product Sales Report
                    </h4>
                    <p class="text-muted small mb-0">
                        Detailed revenue, volume, and customer purchasing analytics for <strong>our catalogue products</strong> only.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <a href="{{ route('admin.analytics.export', ['type' => 'sales']) }}" class="btn btn-sm btn-success">
                        <i class="las la-file-csv me-1 fs-15"></i> Export Sales CSV
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary d-none d-md-inline-block">
                        <i class="las la-print me-1 fs-15"></i> Print Report
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="las la-pills me-1 fs-15"></i> Product Catalogue ({{ $totalCatalogueCount }})
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Catch / Scoping Notice Banner --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-0 py-2 px-3" role="alert">
                <i class="las la-shield-alt fs-22 text-info me-2 flex-shrink-0"></i>
                <div class="small">
                    <strong>Catalogue-Scoped Revenue Reporting:</strong> When patients/pharmacies upload medical invoices containing mixed medicines from multiple brands,
                    <strong>only OM Connect catalogue medicines are counted in this sales report</strong>. Non-company competitor products and general medical fees are automatically excluded from revenue figures.
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar Card --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-0">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.analytics.sales') }}" id="salesFilterForm">
                        <div class="row g-2 align-items-end">
                            {{-- Period Preset --}}
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Time Period</label>
                                <select name="period" id="periodSelect" class="form-select form-select-sm" onchange="toggleCustomDates()">
                                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Time</option>
                                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                    <option value="last_7_days" {{ $period === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="last_30_days" {{ $period === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                                </select>
                            </div>

                            {{-- Custom Dates (Hidden unless custom selected) --}}
                            <div class="col-6 col-md-3 col-lg-2 custom-date-col" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                                <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDateInput }}">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2 custom-date-col" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                                <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDateInput }}">
                            </div>

                            {{-- Product Category --}}
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Product Category</label>
                                <select name="category_id" class="form-select form-select-sm">
                                    <option value="all">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Specific Product --}}
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Our Product</label>
                                <select name="product_id" class="form-select form-select-sm">
                                    <option value="all">All Products ({{ $totalCatalogueCount }})</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" {{ (string)$productId === (string)$prod->id ? 'selected' : '' }}>{{ $prod->brand_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pharmacy Filter --}}
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Pharmacy / Store</label>
                                <select name="pharmacy_id" class="form-select form-select-sm">
                                    <option value="all">All Pharmacies</option>
                                    @foreach($pharmacies as $pharm)
                                        <option value="{{ $pharm->id }}" {{ (string)$pharmacyId === (string)$pharm->id ? 'selected' : '' }}>{{ $pharm->store_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status Filter --}}
                            <div class="col-6 col-md-3 col-lg-1">
                                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>

                            {{-- Search Input --}}
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Keyword Search</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search product, invoice, customer..." value="{{ $search }}">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="las la-filter"></i> Apply
                                    </button>
                                    @if(request()->hasAny(['period', 'start_date', 'end_date', 'category_id', 'product_id', 'pharmacy_id', 'status', 'search']))
                                        <a href="{{ route('admin.analytics.sales') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                                            <i class="las la-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards Row (Strictly Company Products) --}}
    <div class="row g-3 mb-4">
        {{-- Total Company Revenue --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon-wrapper bg-primary-subtle text-primary me-3 flex-shrink-0">
                            <i class="las la-rupee-sign"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-muted text-truncate mb-1 small fw-semibold">Company Product Revenue</p>
                            <h4 class="mb-0 fw-bold text-dark">Rs {{ number_format($totalCompanyRevenue, 2) }}</h4>
                            <small class="text-muted" style="font-size: 11px;">
                                Out of Rs {{ number_format($grossBillsAmount, 2) }} gross ({{ number_format($companySharePercentage, 1) }}% share)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Company Units Sold --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon-wrapper bg-success-subtle text-success me-3 flex-shrink-0">
                            <i class="las la-cubes"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-muted text-truncate mb-1 small fw-semibold">Total Company Units Sold</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ number_format($totalCompanyUnits) }} <span class="fs-13 fw-normal text-muted">units</span></h4>
                            <small class="text-success" style="font-size: 11px;">
                                <i class="las la-check-circle"></i> Across verified catalogue items
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Invoices with Our Products --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon-wrapper bg-info-subtle text-info me-3 flex-shrink-0">
                            <i class="las la-file-invoice-dollar"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-muted text-truncate mb-1 small fw-semibold">Invoices with Company Items</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ number_format($totalCompanyInvoices) }} <span class="fs-13 fw-normal text-muted">bills</span></h4>
                            <small class="text-muted" style="font-size: 11px;">
                                Avg. Basket: Rs {{ number_format($averageCompanyOrderValue, 2) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Products in Sales --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon-wrapper bg-purple-subtle text-purple me-3 flex-shrink-0">
                            <i class="las la-pills"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-muted text-truncate mb-1 small fw-semibold">Active SKUs Sold</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $activeCompanyProductsSold }} <span class="fs-13 fw-normal text-muted">/ {{ $totalCatalogueCount }} catalogue SKUs</span></h4>
                            <small class="text-purple" style="font-size: 11px;">
                                {{ $totalCatalogueCount > 0 ? number_format(($activeCompanyProductsSold / $totalCatalogueCount) * 100, 1) : 0 }}% catalogue penetration
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Revenue & Units Trend Chart --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-chart-area text-primary me-1"></i> Company Product Sales Trend
                    </h5>
                    <span class="badge bg-primary-subtle text-primary">Monthly Revenue &amp; Volumes</span>
                </div>
                <div class="card-body">
                    @if($salesTrend->isNotEmpty())
                        <div id="salesTrendChart" style="min-height: 320px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="las la-chart-line fs-36 d-block mb-2"></i>
                            <p class="mb-0">No sales trend data found for the selected filter criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Category Breakdown Chart --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-chart-pie text-success me-1"></i> Sales by Category
                    </h5>
                    <span class="badge bg-success-subtle text-success">{{ $categorySales->count() }} Categories</span>
                </div>
                <div class="card-body">
                    @if($categorySales->isNotEmpty())
                        <div id="categoryDonutChart" style="min-height: 250px;"></div>
                        <div class="mt-3">
                            <ul class="list-group list-group-flush small">
                                @foreach($categorySales->take(4) as $cat)
                                    @php($catShare = $totalCompanyRevenue > 0 ? ($cat->revenue / $totalCompanyRevenue) * 100 : 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary-subtle text-dark me-2">{{ $loop->iteration }}</span>
                                            <span class="fw-semibold text-dark">{{ $cat->category_name }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold text-dark">Rs {{ number_format($cat->revenue, 2) }}</span>
                                            <small class="text-muted d-block" style="font-size: 11px;">{{ number_format($catShare, 1) }}% ({{ $cat->units_sold }} units)</small>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="las la-pie-chart fs-36 d-block mb-2"></i>
                            <p class="mb-0">No category sales recorded.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Tabs: Product Breakdown, Categories, and Itemized Sales Log --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border mb-4">
                <div class="card-header sales-tabs-header">
                    <ul class="nav nav-tabs sales-nav-tabs" id="salesAnalyticsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#tabProducts" type="button" role="tab" aria-controls="tabProducts" aria-selected="true">
                                <i class="las la-table text-primary me-1 fs-16"></i> Product-Wise Performance <span class="badge bg-primary-subtle text-primary ms-1">{{ $productSales->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#tabCategories" type="button" role="tab" aria-controls="tabCategories" aria-selected="false">
                                <i class="las la-layer-group text-success me-1 fs-16"></i> Category Breakdown <span class="badge bg-success-subtle text-success ms-1">{{ $categorySales->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#tabTransactions" type="button" role="tab" aria-controls="tabTransactions" aria-selected="false">
                                <i class="las la-receipt text-info me-1 fs-16"></i> Individual Sales Transactions <span class="badge bg-info-subtle text-info ms-1">{{ $companyTransactions->total() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="salesAnalyticsTabContent">
                        {{-- Tab 1: Product-Wise Sales Table --}}
                        <div class="tab-pane fade show active" id="tabProducts" role="tabpanel" aria-labelledby="products-tab">
                            <div class="table-responsive">
                                <table class="table table-hover table-sales align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>SKU</th>
                                            <th>Company Product</th>
                                            <th>Generic Name</th>
                                            <th>Category</th>
                                            <th class="text-end">Units Sold</th>
                                            <th class="text-end">Avg. Price (Rs)</th>
                                            <th class="text-end">Total Revenue (Rs)</th>
                                            <th class="text-center">Share %</th>
                                            <th class="text-center">Orders</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($productSales as $prod)
                                            @php($share = $totalCompanyRevenue > 0 ? ($prod->total_revenue / $totalCompanyRevenue) * 100 : 0)
                                            <tr>
                                                <td class="text-muted small">{{ $loop->iteration }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $prod->sku ?: 'SKU-'.$prod->product_id }}</span></td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $prod->brand_name }}</div>
                                                    @if($prod->strength || $prod->dosage_form)
                                                        <small class="text-muted">{{ $prod->strength }} {{ $prod->dosage_form }}</small>
                                                    @endif
                                                </td>
                                                <td class="small text-muted">{{ $prod->generic_name ?: '—' }}</td>
                                                <td><span class="badge bg-primary-subtle text-primary">{{ $prod->category_name }}</span></td>
                                                <td class="text-end fw-semibold text-dark">{{ number_format($prod->total_units_sold) }}</td>
                                                <td class="text-end text-muted small">Rs {{ number_format($prod->avg_unit_price, 2) }}</td>
                                                <td class="text-end fw-bold text-dark">Rs {{ number_format($prod->total_revenue, 2) }}</td>
                                                <td class="text-center" style="width: 140px;">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $share) }}%;"></div>
                                                        </div>
                                                        <small class="fw-semibold text-muted" style="min-width: 36px;">{{ number_format($share, 1) }}%</small>
                                                    </div>
                                                </td>
                                                <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $prod->total_orders_count }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5 text-muted">
                                                    <i class="las la-box-open fs-36 d-block mb-2"></i>
                                                    No company product sales found matching the current filters.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($productSales->isNotEmpty())
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="5" class="text-uppercase small">Total / Summary</td>
                                                <td class="text-end">{{ number_format($totalCompanyUnits) }}</td>
                                                <td class="text-end">—</td>
                                                <td class="text-end text-primary">Rs {{ number_format($totalCompanyRevenue, 2) }}</td>
                                                <td class="text-center">100%</td>
                                                <td class="text-center">{{ number_format($totalCompanyInvoices) }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        {{-- Tab 2: Category Breakdown Table --}}
                        <div class="tab-pane fade" id="tabCategories" role="tabpanel" aria-labelledby="categories-tab">
                            <div class="table-responsive">
                                <table class="table table-hover table-sales align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category Name</th>
                                            <th class="text-center">Active SKUs Sold</th>
                                            <th class="text-end">Units Sold</th>
                                            <th class="text-end">Total Revenue (Rs)</th>
                                            <th class="text-center">Revenue Share</th>
                                            <th class="text-center">Total Invoices</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categorySales as $cat)
                                            @php($catShare = $totalCompanyRevenue > 0 ? ($cat->revenue / $totalCompanyRevenue) * 100 : 0)
                                            <tr>
                                                <td class="text-muted small">{{ $loop->iteration }}</td>
                                                <td class="fw-bold text-dark">{{ $cat->category_name }}</td>
                                                <td class="text-center"><span class="badge bg-secondary-subtle text-dark">{{ $cat->products_count }} SKUs</span></td>
                                                <td class="text-end fw-semibold text-dark">{{ number_format($cat->units_sold) }}</td>
                                                <td class="text-end fw-bold text-dark">Rs {{ number_format($cat->revenue, 2) }}</td>
                                                <td class="text-center" style="width: 180px;">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $catShare) }}%;"></div>
                                                        </div>
                                                        <small class="fw-semibold text-muted" style="min-width: 40px;">{{ number_format($catShare, 1) }}%</small>
                                                    </div>
                                                </td>
                                                <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $cat->orders_count }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    No category data available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 3: Itemized Transactions Log --}}
                        <div class="tab-pane fade" id="tabTransactions" role="tabpanel" aria-labelledby="transactions-tab">
                            <div class="table-responsive">
                                <table class="table table-hover table-sales align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Customer / Patient</th>
                                            <th>Pharmacy / Store</th>
                                            <th>Our Product Sold</th>
                                            <th>Category</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Company Item Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($companyTransactions as $tx)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.purchase-bills.show', $tx->bill_id) }}" class="fw-bold text-primary text-decoration-none">
                                                        {{ $tx->invoice_number ?: 'BILL-'.$tx->bill_id }}
                                                    </a>
                                                </td>
                                                <td class="small text-muted">
                                                    {{ $tx->invoice_date ? \Carbon\Carbon::parse($tx->invoice_date)->format('M d, Y') : \Carbon\Carbon::parse($tx->bill_created_at)->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $tx->user_name ?: 'Anonymous / Direct' }}</div>
                                                </td>
                                                <td class="small">
                                                    {{ $tx->pharmacy_store_name ?: ($tx->bill_pharmacy_name ?: 'Direct Upload') }}
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $tx->brand_name }}</div>
                                                    <small class="text-muted">{{ $tx->sku }} {{ $tx->strength }}</small>
                                                </td>
                                                <td><span class="badge bg-primary-subtle text-primary small">{{ $tx->category_name ?: 'General' }}</span></td>
                                                <td class="text-end fw-semibold text-dark">{{ number_format($tx->quantity) }}</td>
                                                <td class="text-end text-muted small">Rs {{ number_format($tx->unit_price, 2) }}</td>
                                                <td class="text-end fw-bold text-dark">Rs {{ number_format($tx->item_amount, 2) }}</td>
                                                <td>
                                                    @if($tx->validation_status === 'approved')
                                                        <span class="badge badge-soft-success">Approved</span>
                                                    @elseif($tx->validation_status === 'pending')
                                                        <span class="badge badge-soft-warning">Pending</span>
                                                    @elseif($tx->validation_status === 'rejected')
                                                        <span class="badge badge-soft-danger">Rejected</span>
                                                    @else
                                                        <span class="badge badge-soft-info">{{ ucfirst($tx->validation_status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5 text-muted">
                                                    <i class="las la-receipt fs-36 d-block mb-2"></i>
                                                    No individual company product transactions match the selected filters.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($companyTransactions->hasPages())
                                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Showing {{ $companyTransactions->firstItem() }} to {{ $companyTransactions->lastItem() }} of {{ $companyTransactions->total() }} sales records
                                    </small>
                                    <div>
                                        {{ $companyTransactions->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCustomDates() {
        var select = document.getElementById('periodSelect');
        var customCols = document.querySelectorAll('.custom-date-col');
        if (select.value === 'custom') {
            customCols.forEach(function(el) { el.style.display = 'block'; });
        } else {
            customCols.forEach(function(el) { el.style.display = 'none'; });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if($salesTrend->isNotEmpty())
            // Monthly Trend Chart
            var trendLabels = {!! json_encode($salesTrend->pluck('period_label')) !!};
            var trendRevenue = {!! json_encode($salesTrend->pluck('revenue')) !!};
            var trendUnits = {!! json_encode($salesTrend->pluck('units_sold')) !!};

            var trendOptions = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                series: [
                    { name: 'Company Revenue (Rs)', data: trendRevenue },
                    { name: 'Units Sold', data: trendUnits }
                ],
                colors: ['#CD0B6F', '#0ea5e9'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: [3, 2] },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100]
                    }
                },
                xaxis: {
                    categories: trendLabels,
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: [
                    {
                        title: { text: 'Revenue (Rs)', style: { color: '#CD0B6F', fontWeight: 600 } },
                        labels: {
                            formatter: function(val) { return 'Rs ' + Number(val).toLocaleString(); },
                            style: { colors: '#64748b' }
                        }
                    },
                    {
                        opposite: true,
                        title: { text: 'Units Sold', style: { color: '#0ea5e9', fontWeight: 600 } },
                        labels: {
                            formatter: function(val) { return Number(val).toLocaleString(); },
                            style: { colors: '#64748b' }
                        }
                    }
                ],
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function(val, { seriesIndex }) {
                            if (seriesIndex === 0) return 'Rs ' + Number(val).toLocaleString();
                            return Number(val).toLocaleString() + ' units';
                        }
                    }
                },
                legend: { position: 'top', horizontalAlign: 'right' }
            };

            var trendChart = new ApexCharts(document.querySelector("#salesTrendChart"), trendOptions);
            trendChart.render();
        @endif

        @if($categorySales->isNotEmpty())
            // Category Donut Chart
            var catLabels = {!! json_encode($categorySales->pluck('category_name')) !!};
            var catRevenue = {!! json_encode($categorySales->pluck('revenue')) !!};

            var catOptions = {
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'inherit'
                },
                series: catRevenue,
                labels: catLabels,
                colors: ['#CD0B6F', '#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#64748b'],
                legend: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Sales',
                                    formatter: function (w) {
                                        return 'Rs ' + Number({{ $totalCompanyRevenue }}).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) { return 'Rs ' + Number(val).toLocaleString(); }
                    }
                }
            };

            var catChart = new ApexCharts(document.querySelector("#categoryDonutChart"), catOptions);
            catChart.render();
        @endif
    });
</script>
@endpush
