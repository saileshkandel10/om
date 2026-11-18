@extends('layouts.admin')

@section('title', 'Other Products & Market Product Mapping')

@push('styles')
<style>
    .theme-primary-btn {
        background-color: #CD0B6F !important;
        border-color: #CD0B6F !important;
        color: #fff !important;
    }
    .theme-primary-btn:hover {
        background-color: #b0095f !important;
        border-color: #b0095f !important;
        color: #fff !important;
    }
    .theme-outline-btn {
        border-color: #CD0B6F !important;
        color: #CD0B6F !important;
    }
    .theme-outline-btn:hover {
        background-color: #CD0B6F !important;
        color: #fff !important;
    }
    .badge-soft-success {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .badge-soft-warning {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .badge-soft-info {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .badge-soft-pink {
        background-color: #fce7f3;
        color: #be185d;
        border: 1px solid #fbcfe8;
    }
    .badge-suggested {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 11.5px;
    }
    .badge-suggested:hover {
        background-color: #dbeafe;
        transform: translateY(-1px);
    }
    .bulk-toolbar {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 16px;
        display: none;
    }
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .drop-zone:hover {
        border-color: #CD0B6F;
        background: #fff5f9;
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
                        <i class="las la-pills text-primary me-1"></i> Other Products &amp; Market Illness Mapping
                    </h4>
                    <p class="text-muted small mb-0">
                        Directory of third-party market products &amp; generic medications mapped to Illness Categories for automated patient bill OCR diagnosis tracking.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    {{-- Auto Suggest All --}}
                    <form method="POST" action="{{ route('admin.other-products.auto-suggest') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Run fuzzy suggestion engine for all unmapped records">
                            <i class="las la-magic me-1"></i> Auto-Suggest Queue
                        </button>
                    </form>

                    {{-- Download Sample Template Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-download me-1"></i> Sample Template
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('admin.other-products.sample-template', ['format' => 'xlsx']) }}"><i class="las la-file-excel text-success me-2"></i> Excel (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.other-products.sample-template', ['format' => 'csv']) }}"><i class="las la-file-csv text-primary me-2"></i> CSV (.csv)</a></li>
                        </ul>
                    </div>

                    {{-- Export Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export Data
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('admin.other-products.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"><i class="las la-file-excel text-success me-2"></i> Export to Excel</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.other-products.export', array_merge(request()->all(), ['format' => 'csv'])) }}"><i class="las la-file-csv text-primary me-2"></i> Export to CSV</a></li>
                        </ul>
                    </div>

                    {{-- Bulk Import Button --}}
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="las la-file-upload me-1"></i> Bulk Import Excel
                    </button>

                    {{-- Add Other Product Button --}}
                    <button type="button" class="btn btn-sm theme-primary-btn" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="las la-plus-circle me-1"></i> Add Other Product
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="las la-check-circle me-1 fs-18 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="las la-exclamation-circle me-1 fs-18 align-middle"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-light text-primary me-3">
                        <i class="las la-boxes fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Total Market Products</span>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-light text-success me-3">
                        <i class="las la-check-double fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Mapped &amp; Active</span>
                        <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['active']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-light text-warning me-3">
                        <i class="las la-hourglass-half fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Unmapped OCR Queue</span>
                        <h4 class="mb-0 fw-bold {{ $stats['unmapped'] > 0 ? 'text-warning' : 'text-dark' }}">{{ number_format($stats['unmapped']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-light text-pink me-3">
                        <i class="las la-heartbeat fs-24" style="color: #CD0B6F;"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-12 fw-semibold">Illness Categories Covered</span>
                        <h4 class="mb-0 fw-bold" style="color: #CD0B6F;">{{ number_format($stats['illness_categories_count']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Tabs Navigation --}}
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-pills bg-white p-2 rounded-3 border shadow-sm" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'catalog' || (!request()->has('unmapped_page') && $tab !== 'unmapped') ? 'active' : '' }}"
                       id="catalog-tab" data-bs-toggle="pill" href="#catalogPane" role="tab">
                        <i class="las la-list me-1 fs-16"></i>
                        Other Products Directory ({{ number_format($stats['total']) }})
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link {{ $tab === 'unmapped' || request()->has('unmapped_page') ? 'active' : '' }}"
                       id="unmapped-tab" data-bs-toggle="pill" href="#unmappedPane" role="tab">
                        <i class="las la-exclamation-circle me-1 fs-16"></i>
                        Unmapped OCR &amp; Verification Queue
                        @if($stats['unmapped'] > 0)
                            <span class="badge bg-danger ms-1">{{ $stats['unmapped'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        {{-- ======================================================= --}}
        {{-- TAB 1: OTHER PRODUCTS MASTER DIRECTORY --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'catalog' || (!request()->has('unmapped_page') && $tab !== 'unmapped') ? 'show active' : '' }}" id="catalogPane" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    {{-- Filter & Search Form --}}
                    <form method="GET" action="{{ route('admin.other-products.index') }}" class="row g-2 mb-3 align-items-center">
                        <input type="hidden" name="tab" value="catalog">

                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search Brand, Generic, Description..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="illness_category_id" class="form-select form-select-sm">
                                <option value="">-- All Illness Categories --</option>
                                @foreach($illnessCategories as $ic)
                                    <option value="{{ $ic->id }}" {{ request('illness_category_id') == $ic->id ? 'selected' : '' }}>
                                        {{ $ic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">-- All Categories --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- All Statuses --</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="unmapped" {{ request('status') === 'unmapped' ? 'selected' : '' }}>Unmapped</option>
                                <option value="flagged" {{ request('status') === 'flagged' ? 'selected' : '' }}>Flagged</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary w-50">Filter</button>
                            <a href="{{ route('admin.other-products.index', ['tab' => 'catalog']) }}" class="btn btn-sm btn-outline-secondary w-50">Reset</a>
                        </div>
                    </form>

                    {{-- Products Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 22%;">Brand Name</th>
                                    <th style="width: 18%;">Generic Name</th>
                                    <th style="width: 15%;">Product Category</th>
                                    <th style="width: 18%;">Illness Category</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 15%; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $product->brand_name }}</div>
                                            @if($product->description)
                                                <small class="text-muted d-block text-truncate" style="max-width: 250px;" title="{{ $product->description }}">
                                                    {{ $product->description }}
                                                </small>
                                            @endif
                                            @if($product->source !== 'manual')
                                                <span class="badge badge-soft-info" style="font-size: 10px;">{{ strtoupper($product->source) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-secondary">{{ $product->generic_name ?: '-' }}</span>
                                        </td>
                                        <td>
                                            @if($product->category)
                                                <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->illnessCategory)
                                                <span class="badge badge-soft-pink px-2 py-1">
                                                    <i class="las la-heartbeat me-1"></i> {{ $product->illnessCategory->name }}
                                                </span>
                                            @else
                                                <span class="badge badge-soft-warning px-2 py-1">
                                                    <i class="las la-exclamation-triangle me-1"></i> Unmapped
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->status === 'active')
                                                <span class="badge badge-soft-success px-2 py-1"><i class="las la-check-circle me-1"></i> Active</span>
                                            @elseif($product->status === 'unmapped')
                                                <span class="badge badge-soft-warning px-2 py-1"><i class="las la-clock me-1"></i> Unmapped</span>
                                            @else
                                                <span class="badge badge-soft-danger px-2 py-1"><i class="las la-flag me-1"></i> Flagged</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                {{-- Remap Button --}}
                                                <button type="button" class="btn btn-outline-info" title="Remap Illness Category"
                                                        data-bs-toggle="modal" data-bs-target="#mapModal"
                                                        data-id="{{ $product->id }}"
                                                        data-brand="{{ $product->brand_name }}"
                                                        data-generic="{{ $product->generic_name }}"
                                                        data-category="{{ $product->category_id }}"
                                                        data-illness="{{ $product->illness_category_id }}">
                                                    <i class="las la-link"></i>
                                                </button>

                                                {{-- Edit Button --}}
                                                <button type="button" class="btn btn-outline-primary" title="Edit Product"
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        data-id="{{ $product->id }}"
                                                        data-brand="{{ $product->brand_name }}"
                                                        data-generic="{{ $product->generic_name }}"
                                                        data-category="{{ $product->category_id }}"
                                                        data-illness="{{ $product->illness_category_id }}"
                                                        data-description="{{ $product->description }}"
                                                        data-status="{{ $product->status }}">
                                                    <i class="las la-edit"></i>
                                                </button>

                                                {{-- Delete Button --}}
                                                <form method="POST" action="{{ route('admin.other-products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete {{ $product->brand_name }}?');">
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
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="las la-box-open fs-36 d-block mb-2 text-muted"></i>
                                            No other market products found. Use "+ Add Other Product" or "+ Bulk Import Excel" to populate the catalog.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} market products
                        </small>
                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- TAB 2: UNMAPPED OCR & VERIFICATION QUEUE --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'unmapped' || request()->has('unmapped_page') ? 'show active' : '' }}" id="unmappedPane" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    {{-- Queue Header Info --}}
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong class="text-dark"><i class="las la-robot text-primary me-1"></i> Auto-Matching &amp; Unmapped Verification Queue</strong>
                            <p class="text-muted small mb-0">Products detected from uploaded patient bills or imported without an illness category. Click a suggested badge or pick an Illness Category to map and activate.</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark px-2 py-1 fs-12">{{ number_format($stats['unmapped']) }} Pending Approval</span>
                        </div>
                    </div>

                    {{-- Batch Action Toolbar --}}
                    <form id="batchMapForm" method="POST" action="{{ route('admin.other-products.batch-map') }}" class="bulk-toolbar mb-3 align-items-center justify-content-between">
                        @csrf
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary fs-12" id="selectedCountBadge">0 selected</span>
                            <span class="text-muted small">Batch Map selected items to:</span>
                            <select name="illness_category_id" class="form-select form-select-sm" style="width: 250px;" required>
                                <option value="">-- Choose Illness Category --</option>
                                @foreach($illnessCategories as $ic)
                                    <option value="{{ $ic->id }}">{{ $ic->name }}</option>
                                @endforeach
                            </select>

                            <select name="category_id" class="form-select form-select-sm" style="width: 200px;">
                                <option value="">-- Product Category (Optional) --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="las la-check me-1"></i> Apply Batch Mapping
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-link text-muted" id="clearSelectionBtn">Clear Selection</button>
                        </div>
                    </form>

                    {{-- Unmapped Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 4%;">
                                        <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                    </th>
                                    <th style="width: 22%;">Product / OCR Text</th>
                                    <th style="width: 16%;">Generic Name</th>
                                    <th style="width: 10%;">Source</th>
                                    <th style="width: 26%;">Top Suggested Illness Categories</th>
                                    <th style="width: 22%; text-align: right;">Quick Approve &amp; Map</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unmappedRows as $row)
                                    @php($item = $row['model'])
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input item-checkbox" name="ids[]" form="batchMapForm" value="{{ $item->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->brand_name }}</div>
                                            @if($item->raw_ocr_text && $item->raw_ocr_text !== $item->brand_name)
                                                <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="{{ $item->raw_ocr_text }}">
                                                    Raw OCR: "{{ $item->raw_ocr_text }}"
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-secondary">{{ $item->generic_name ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->source === 'ocr' ? 'badge-soft-warning' : 'badge-soft-info' }} px-2 py-1">
                                                {{ strtoupper($item->source) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                                @forelse($row['suggestions'] as $sugg)
                                                    <button type="button"
                                                            class="btn badge badge-suggested py-1 px-2 border d-inline-flex align-items-center"
                                                            title="{{ $sugg['matched_by'] }}"
                                                            onclick="quickSelectSuggestion({{ $item->id }}, {{ $sugg['illness_category_id'] }})">
                                                        <i class="las la-sparkles me-1 text-primary"></i>
                                                        {{ $sugg['illness_category_name'] }}
                                                        <span class="badge bg-primary text-white ms-1" style="font-size: 9.5px;">{{ $sugg['confidence_score'] }}%</span>
                                                    </button>
                                                @empty
                                                    <span class="text-muted small fst-italic">No auto-suggestions calculated</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.other-products.map', $item) }}" class="d-inline-flex gap-1 align-items-center" id="mapForm_{{ $item->id }}">
                                                @csrf
                                                <select name="illness_category_id" id="illnessSelect_{{ $item->id }}" class="form-select form-select-sm" style="width: 170px;" required>
                                                    <option value="">-- Map Condition --</option>
                                                    @foreach($illnessCategories as $ic)
                                                        <option value="{{ $ic->id }}" {{ $item->suggested_illness_category_id == $ic->id ? 'selected' : '' }}>
                                                            {{ $ic->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve &amp; Map this product">
                                                    <i class="las la-check"></i> Map
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="las la-check-circle fs-36 d-block mb-2 text-success"></i>
                                            All market and OCR products are fully mapped and verified!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Unmapped Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Showing {{ $unmappedProducts->firstItem() ?? 0 }} to {{ $unmappedProducts->lastItem() ?? 0 }} of {{ $unmappedProducts->total() }} unmapped items
                        </small>
                        <div>
                            {{ $unmappedProducts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: ADD OTHER PRODUCT --}}
{{-- ======================================================= --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.other-products.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createModalLabel">
                        <i class="las la-plus-circle text-primary me-1"></i> Add Other Market Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="brand_name" id="addBrandName" class="form-control" placeholder="e.g. Pantocid 40, Glycomet 500" required>
                            <small class="text-muted">Commercial or market brand name from patient bills.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Generic Name</label>
                            <input type="text" name="generic_name" id="addGenericName" class="form-control" placeholder="e.g. Pantoprazole, Metformin Hydrochloride">
                        </div>

                        {{-- Real-time AI Suggestion Box in Add Modal --}}
                        <div class="col-12" id="addSuggestionBox" style="display: none;">
                            <div class="p-2 bg-light rounded border">
                                <small class="text-primary fw-bold"><i class="las la-magic me-1"></i> Auto-Suggested Conditions:</small>
                                <div id="addSuggestionBadges" class="d-flex flex-wrap gap-1 mt-1"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Illness Category (Target Condition)</label>
                            <select name="illness_category_id" id="addIllnessCategory" class="form-select">
                                <option value="">-- Select Linked Condition --</option>
                                @foreach($illnessCategories as $ic)
                                    <option value="{{ $ic->id }}">{{ $ic->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Directly maps this market drug to health reports.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Product Category (Optional) --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description / Clinical Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. Market third-party antacid for acidity and peptic ulcers"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active (Verified)</option>
                                <option value="unmapped">Unmapped (Pending Verification)</option>
                                <option value="flagged">Flagged</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn theme-primary-btn">Save Other Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: EDIT OTHER PRODUCT --}}
{{-- ======================================================= --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editModalLabel">
                        <i class="las la-edit text-primary me-1"></i> Edit Other Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="brand_name" id="editBrandName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Generic Name</label>
                            <input type="text" name="generic_name" id="editGenericName" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Illness Category (Condition)</label>
                            <select name="illness_category_id" id="editIllnessCategory" class="form-select">
                                <option value="">-- Unmapped / None --</option>
                                @foreach($illnessCategories as $ic)
                                    <option value="{{ $ic->id }}">{{ $ic->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Category</label>
                            <select name="category_id" id="editCategory" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="unmapped">Unmapped</option>
                                <option value="flagged">Flagged</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: REMAP / QUICK MAP MODAL --}}
{{-- ======================================================= --}}
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="mapModalForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="mapModalLabel">
                        <i class="las la-link text-info me-1"></i> Map Illness Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Brand Name</label>
                        <h5 class="fw-bold text-dark" id="mapModalBrandName"></h5>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Illness Category <span class="text-danger">*</span></label>
                        <select name="illness_category_id" id="mapModalIllnessCategory" class="form-select" required>
                            <option value="">-- Choose Illness Category --</option>
                            @foreach($illnessCategories as $ic)
                                <option value="{{ $ic->id }}">{{ $ic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Generic Name (Optional)</label>
                        <input type="text" name="generic_name" id="mapModalGenericName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product Category (Optional)</label>
                        <select name="category_id" id="mapModalCategory" class="form-select">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: BULK IMPORT EXCEL (HIGH-VOLUME ENGINE) --}}
{{-- ======================================================= --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="importForm" method="POST" action="{{ route('admin.other-products.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importModalLabel">
                        <i class="las la-file-upload text-success me-1"></i> High-Volume Bulk Import (.xlsx, .xls, .csv)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Upload Instructions --}}
                    <div class="alert alert-info border-0 mb-3 small">
                        <strong><i class="las la-info-circle me-1"></i> High Volume Capacity:</strong> Handles up to <strong>50,000 records</strong> asynchronously with chunking.
                        Columns expected: <code>Brand Name</code>, <code>Generic Name</code>, <code>Category</code>, <code>Illness Category</code>, <code>Description</code>.
                        <div class="mt-1">
                            <a href="{{ route('admin.other-products.sample-template', ['format' => 'xlsx']) }}" class="fw-bold text-decoration-underline text-primary">
                                <i class="las la-download"></i> Download Sample Excel Template
                            </a>
                        </div>
                    </div>

                    {{-- File Drop Zone --}}
                    <div class="drop-zone mb-3" onclick="document.getElementById('importFileInput').click()">
                        <i class="las la-cloud-upload-alt fs-48 text-muted mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">Click or Drag &amp; Drop Excel or CSV File Here</h6>
                        <span class="text-muted small" id="selectedFileName">Supports .xlsx, .xls, .csv (Max 50MB)</span>
                        <input type="file" name="file" id="importFileInput" class="d-none" accept=".xlsx,.xls,.csv,.txt" required onchange="handleFileSelected(this)">
                    </div>

                    {{-- Import Options --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">On Duplicate Brand Name</label>
                            <select name="on_duplicate" class="form-select form-select-sm">
                                <option value="skip">Skip duplicates (Keep existing)</option>
                                <option value="update">Update existing record with new data</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Status For Matched Items</label>
                            <select name="default_status" class="form-select form-select-sm">
                                <option value="active">Active (Instant Live Mapping)</option>
                                <option value="unmapped">Unmapped (Send to Verification Queue)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pre-Import Preview Section --}}
                    <div id="previewSection" style="display: none;">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="las la-table text-primary me-1"></i> Pre-Import Preview (First 10 Rows)</h6>
                            <div id="previewBadgeStats"></div>
                        </div>
                        <div class="table-responsive" style="max-height: 200px;">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 11.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Row</th>
                                        <th>Brand Name</th>
                                        <th>Generic Name</th>
                                        <th>Category</th>
                                        <th>Illness Category</th>
                                        <th>Validation</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-info" id="previewBtn" onclick="runPreImportPreview()" style="display: none;">
                            <i class="las la-eye me-1"></i> Preview First 10 Rows
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="startImportBtn">
                            <i class="las la-upload me-1"></i> Start Bulk Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Edit Modal Population
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const brand = btn.getAttribute('data-brand');
                const generic = btn.getAttribute('data-generic') || '';
                const cat = btn.getAttribute('data-category') || '';
                const illness = btn.getAttribute('data-illness') || '';
                const desc = btn.getAttribute('data-description') || '';
                const status = btn.getAttribute('data-status') || 'active';

                const form = document.getElementById('editForm');
                form.action = "{{ url('admin/other-products') }}/" + id;

                document.getElementById('editBrandName').value = brand;
                document.getElementById('editGenericName').value = generic;
                document.getElementById('editCategory').value = cat;
                document.getElementById('editIllnessCategory').value = illness;
                document.getElementById('editDescription').value = desc;
                document.getElementById('editStatus').value = status;
            });
        }

        // 2. Remap / Quick Map Modal Population
        const mapModal = document.getElementById('mapModal');
        if (mapModal) {
            mapModal.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const brand = btn.getAttribute('data-brand');
                const generic = btn.getAttribute('data-generic') || '';
                const cat = btn.getAttribute('data-category') || '';
                const illness = btn.getAttribute('data-illness') || '';

                const form = document.getElementById('mapModalForm');
                form.action = "{{ url('admin/other-products') }}/" + id + "/map";

                document.getElementById('mapModalBrandName').textContent = brand;
                document.getElementById('mapModalGenericName').value = generic;
                document.getElementById('mapModalCategory').value = cat;
                document.getElementById('mapModalIllnessCategory').value = illness;
            });
        }

        // 3. Select All & Bulk Toolbar
        const selectAll = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const bulkToolbar = document.querySelector('.bulk-toolbar');
        const countBadge = document.getElementById('selectedCountBadge');
        const clearBtn = document.getElementById('clearSelectionBtn');

        function updateBulkToolbar() {
            const selected = document.querySelectorAll('.item-checkbox:checked');
            if (selected.length > 0) {
                bulkToolbar.style.display = 'flex';
                countBadge.textContent = selected.length + ' selected';
            } else {
                bulkToolbar.style.display = 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkToolbar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkToolbar);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                checkboxes.forEach(cb => cb.checked = false);
                if (selectAll) selectAll.checked = false;
                updateBulkToolbar();
            });
        }

        // 4. Real-time Suggestion Debounce in Add Modal
        let typingTimer;
        const brandInput = document.getElementById('addBrandName');
        const genericInput = document.getElementById('addGenericName');

        function fetchSuggestions() {
            const brand = brandInput.value.trim();
            const generic = genericInput.value.trim();

            if (brand.length < 3) {
                document.getElementById('addSuggestionBox').style.display = 'none';
                return;
            }

            fetch(`{{ route('admin.other-products.suggestions') }}?brand_name=${encodeURIComponent(brand)}&generic_name=${encodeURIComponent(generic)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.suggestions && data.suggestions.length > 0) {
                        const box = document.getElementById('addSuggestionBox');
                        const container = document.getElementById('addSuggestionBadges');
                        container.innerHTML = '';

                        data.suggestions.forEach(sugg => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'btn badge badge-suggested py-1 px-2 border';
                            btn.innerHTML = `<i class="las la-sparkles me-1 text-primary"></i> ${sugg.illness_category_name} <span class="badge bg-primary text-white ms-1">${sugg.confidence_score}%</span>`;
                            btn.onclick = function() {
                                document.getElementById('addIllnessCategory').value = sugg.illness_category_id;
                            };
                            container.appendChild(btn);
                        });

                        box.style.display = 'block';
                    } else {
                        document.getElementById('addSuggestionBox').style.display = 'none';
                    }
                })
                .catch(() => {});
        }

        if (brandInput) {
            brandInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(fetchSuggestions, 400);
            });
        }
        if (genericInput) {
            genericInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(fetchSuggestions, 400);
            });
        }
    });

    // Quick select from suggested badge in Unmapped table
    function quickSelectSuggestion(productId, illnessCategoryId) {
        const select = document.getElementById('illnessSelect_' + productId);
        if (select) {
            select.value = illnessCategoryId;
            // Add flash animation
            select.classList.add('border-primary');
            setTimeout(() => select.classList.remove('border-primary'), 1000);
        }
    }

    // File selection UI update
    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('selectedFileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('previewBtn').style.display = 'inline-block';
        }
    }

    // AJAX pre-import preview
    function runPreImportPreview() {
        const fileInput = document.getElementById('importFileInput');
        if (!fileInput.files || !fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        const previewBtn = document.getElementById('previewBtn');
        previewBtn.disabled = true;
        previewBtn.innerHTML = '<i class="las la-spinner la-spin me-1"></i> Parsing file...';

        fetch('{{ route("admin.other-products.preview-import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="las la-eye me-1"></i> Refresh Preview';

            if (data.error) {
                alert(data.error);
                return;
            }

            const section = document.getElementById('previewSection');
            const tbody = document.getElementById('previewTableBody');
            const stats = document.getElementById('previewBadgeStats');

            tbody.innerHTML = '';
            stats.innerHTML = `<span class="badge bg-success me-1">${data.valid_count} Valid</span> <span class="badge bg-danger">${data.invalid_count} Invalid</span>`;

            data.rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.className = r.is_valid ? '' : 'table-danger';
                tr.innerHTML = `
                    <td>${r.row_number}</td>
                    <td class="fw-bold">${r.brand_name || '<em class="text-danger">Missing</em>'}</td>
                    <td>${r.generic_name || '-'}</td>
                    <td>${r.category_name || '-'}</td>
                    <td>${r.illness_category_name || '<em class="text-muted">Unmapped</em>'}</td>
                    <td><span class="badge ${r.is_valid ? 'bg-success' : 'bg-danger'}">${r.validation_notes}</span></td>
                `;
                tbody.appendChild(tr);
            });

            section.style.display = 'block';
        })
        .catch(err => {
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="las la-eye me-1"></i> Preview First 10 Rows';
            alert('Failed to preview file: ' + err.message);
        });
    }
</script>
@endpush
