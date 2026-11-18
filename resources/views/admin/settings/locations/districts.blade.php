@extends('layouts.admin')

@section('title', 'Nepal Districts & Location Normalization')

@push('styles')
<style>
    .bulk-toolbar {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        display: none;
    }
    .unmapped-row:hover {
        background-color: #f8fafc;
    }
    .badge-suggested {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
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
                        <i class="las la-map-marked-alt text-primary me-1"></i> Nepal 77 Districts &amp; Location Management
                    </h4>
                    <p class="text-muted small mb-0">Official provincial and district administration, granular unmapped bill review, popup mapping &amp; bulk actions.</p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    {{-- Universal Location Export Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-file-export me-1"></i> Export Data
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="dropdown-header text-uppercase fs-11 fw-bold text-muted">Districts Master</li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.locations.districts.export', ['type' => 'districts', 'format' => 'xlsx']) }}"><i class="las la-file-excel text-success me-2"></i> 77 Districts (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.locations.districts.export', ['type' => 'districts', 'format' => 'csv']) }}"><i class="las la-file-csv text-primary me-2"></i> 77 Districts (.csv)</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-header text-uppercase fs-11 fw-bold text-muted">Unmapped Queue</li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.locations.districts.export', ['type' => 'unmapped_bills', 'format' => 'xlsx']) }}"><i class="las la-file-excel text-warning me-2"></i> Unmapped Bills (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.locations.districts.export', ['type' => 'unmapped_bills', 'format' => 'csv']) }}"><i class="las la-file-csv text-warning me-2"></i> Unmapped Bills (.csv)</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-header text-uppercase fs-11 fw-bold text-muted">Coverage Summary</li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.locations.districts.export', ['type' => 'summary', 'format' => 'xlsx']) }}"><i class="las la-chart-bar text-info me-2"></i> Province Breakdown (.xlsx)</a></li>
                        </ul>
                    </div>

                    {{-- Auto Normalize All Button --}}
                    <form method="POST" action="{{ route('admin.settings.locations.districts.normalize') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Auto-scan all unmapped records against 77 official districts">
                            <i class="las la-magic me-1"></i> Auto-Match All Locations
                        </button>
                    </form>

                    <a href="{{ route('admin.reports.product-demand') }}" class="btn btn-sm btn-primary">
                        <i class="las la-chart-pie me-1"></i> View Demand Report
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Tabs Navigation --}}
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-pills bg-white p-2 rounded-3 border shadow-sm" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'unmapped' || request()->has('unmapped_page') || (!request()->has('districts_page') && $unmappedStats['total_pending'] > 0) ? 'active' : '' }}"
                       id="unmapped-tab" data-bs-toggle="pill" href="#unmappedPane" role="tab">
                        <i class="las la-exclamation-circle me-1 fs-16"></i>
                        Unmapped Bills &amp; Orders Queue
                        @if($unmappedStats['total_pending'] > 0)
                            <span class="badge bg-danger ms-1">{{ $unmappedStats['total_pending'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link {{ $tab === 'districts' || request()->has('districts_page') || ($unmappedStats['total_pending'] === 0 && !request()->has('unmapped_page')) ? 'active' : '' }}"
                       id="districts-tab" data-bs-toggle="pill" href="#districtsPane" role="tab">
                        <i class="las la-list me-1 fs-16"></i>
                        Nepal 77 Districts Master Registry
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        {{-- ======================================================= --}}
        {{-- TAB 1: UNMAPPED BILLS & ORDERS REVIEW QUEUE --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'unmapped' || request()->has('unmapped_page') || (!request()->has('districts_page') && $unmappedStats['total_pending'] > 0) ? 'show active' : '' }}" id="unmappedPane" role="tabpanel">
            {{-- Summary Metric Cards for Unmapped Queue --}}
            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-sm-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Pending Unmapped Bills</span>
                                <h3 class="mb-0 fw-bold text-danger mt-1">{{ number_format($unmappedStats['total_pending']) }}</h3>
                                <small class="text-muted">Awaiting district mapping</small>
                            </div>
                            <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 20px;">
                                <i class="las la-map-marker-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Suggested Matches Found</span>
                                <h3 class="mb-0 fw-bold text-success mt-1">{{ number_format($unmappedStats['with_suggestions']) }}</h3>
                                <small class="text-muted">Ready for 1-click auto map</small>
                            </div>
                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 20px;">
                                <i class="las la-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Need Manual Review</span>
                                <h3 class="mb-0 fw-bold text-warning mt-1">{{ number_format($unmappedStats['need_manual']) }}</h3>
                                <small class="text-muted">Requires admin district selection</small>
                            </div>
                            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 20px;">
                                <i class="las la-user-edit"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Unmapped Revenue</span>
                                <h3 class="mb-0 fw-bold text-dark mt-1">Rs. {{ number_format($unmappedStats['total_revenue'], 2) }}</h3>
                                <small class="text-muted">{{ number_format($unmappedStats['total_skipped']) }} bills skipped</small>
                            </div>
                            <div class="rounded-circle bg-dark-subtle text-dark d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 20px;">
                                <i class="las la-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Unmapped Queue Filter Bar --}}
            <div class="card border mb-3">
                <div class="card-body p-2">
                    <form method="GET" action="{{ route('admin.settings.locations.districts') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="unmapped">
                        <div class="col-md-4 col-sm-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="las la-search"></i></span>
                                <input type="search" name="search" class="form-control form-control-sm"
                                       placeholder="Search invoice #, customer name, pharmacy..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending Review Only ({{ $unmappedStats['total_pending'] }})</option>
                                <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Skipped / Ignored ({{ $unmappedStats['total_skipped'] }})</option>
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Unmapped Records</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                <i class="las la-filter me-1"></i> Filter Queue
                            </button>
                            <a href="{{ route('admin.settings.locations.districts', ['tab' => 'unmapped']) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                        <div class="col-md-2 col-sm-6 text-end">
                            <form method="POST" action="{{ route('admin.settings.locations.bills.bulk') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="bulk_action" value="auto_match_all">
                                <button type="submit" class="btn btn-sm btn-success w-100" title="Auto-assign all bills where high-confidence match is available">
                                    <i class="las la-check-double me-1"></i> Auto-Map Suggestions
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bulk Actions Toolbar (Appears when rows are checked) --}}
            <form id="bulkActionForm" method="POST" action="{{ route('admin.settings.locations.bills.bulk') }}">
                @csrf
                <div class="bulk-toolbar mb-3 align-items-center justify-content-between" id="bulkToolbar">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary fs-13"><span id="selectedCount">0</span> Bills Selected</span>
                        <span class="text-muted small">Choose an action to apply to selected bills:</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
                        {{-- Bulk District Dropdown --}}
                        <select name="bulk_district_id" class="form-select form-select-sm" style="width: 220px;" id="bulkDistrictSelect">
                            <option value="">Choose District for Selected...</option>
                            @foreach($allDistrictsList as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->province?->name ?? 'Nepal' }})</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary" onclick="submitBulkAction('assign_district')">
                            <i class="las la-check me-1"></i> Assign District
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="submitBulkAction('auto_match_selected')">
                            <i class="las la-magic me-1"></i> Auto-Match Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitBulkAction('skip_selected')">
                            <i class="las la-forward me-1"></i> Skip Selected
                        </button>
                    </div>
                </div>
                <input type="hidden" name="bulk_action" id="bulkActionInput" value="">

                {{-- Unmapped Bills Table --}}
                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                            <i class="las la-clipboard-list text-primary me-1"></i> Unmapped Purchase Bills &amp; Orders
                        </h6>
                        <small class="text-muted">Showing {{ count($unmappedRows) }} records</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 38px;">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                                        </th>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Customer &amp; Phone</th>
                                        <th>Pharmacy / Store</th>
                                        <th style="min-width: 200px;">Extracted Raw Address</th>
                                        <th>Suggested Match</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end" style="min-width: 240px;">Action / Map District</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unmappedRows as $row)
                                        <tr class="unmapped-row">
                                            <td>
                                                <input type="checkbox" name="bill_ids[]" value="{{ $row['id'] }}" class="form-check-input bill-checkbox" onchange="updateSelectedCount()">
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">#{{ $row['invoice_number'] }}</span>
                                                <small class="text-muted d-block">ID: {{ $row['id'] }}</small>
                                            </td>
                                            <td><span class="small text-muted">{{ $row['invoice_date'] }}</span></td>
                                            <td>
                                                <strong class="text-dark d-block">{{ $row['customer_name'] }}</strong>
                                                <small class="text-muted"><i class="las la-phone me-1"></i>{{ $row['customer_phone'] }}</small>
                                            </td>
                                            <td>
                                                <span class="small fw-medium text-truncate d-block" style="max-width: 160px;" title="{{ $row['pharmacy_name'] }}">
                                                    {{ $row['pharmacy_name'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-break text-muted d-block" style="max-width: 260px;" title="{{ $row['raw_address'] }}">
                                                    <i class="las la-map-marker-alt text-danger me-1"></i>{{ $row['raw_address'] }}
                                                </small>
                                            </td>
                                            <td>
                                                @if(!empty($row['suggested_district_name']))
                                                    <span class="badge badge-suggested">
                                                        <i class="las la-star me-1 text-warning"></i>{{ $row['suggested_district_name'] }}
                                                    </span>
                                                    <small class="text-muted d-block">{{ $row['suggested_province_name'] }}</small>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">No match</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold text-dark">
                                                Rs. {{ number_format($row['total_amount'], 2) }}
                                            </td>
                                            <td class="text-end" onclick="event.stopPropagation();">
                                                <div class="d-flex justify-content-end align-items-center gap-1">
                                                    {{-- Popup Mapping Modal Button --}}
                                                    <button type="button" class="btn btn-xs btn-primary" onclick="openBillMapModal({{ $row['id'] }})" title="Open popup review & mapping">
                                                        <i class="las la-map-pin me-1"></i> Map District
                                                    </button>

                                                    {{-- Quick Single Skip Button --}}
                                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="skipSingleBillAction({{ $row['id'] }})" title="Skip/Ignore this bill">
                                                        <i class="las la-forward"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="las la-check-circle text-success fs-36 d-block mb-2"></i>
                                                <h6 class="fw-bold mb-0">All bills and orders are successfully mapped!</h6>
                                                <small>No unmapped location records found in the queue.</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($unmappedBillsPaginator->hasPages())
                            <div class="card-footer bg-light py-2 d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    Showing {{ $unmappedBillsPaginator->firstItem() }} to {{ $unmappedBillsPaginator->lastItem() }} of {{ $unmappedBillsPaginator->total() }} unmapped bills
                                </div>
                                <div>
                                    {{ $unmappedBillsPaginator->appends(['tab' => 'unmapped'])->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ======================================================= --}}
        {{-- TAB 2: NEPAL 77 DISTRICTS MASTER REGISTRY --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'districts' || request()->has('districts_page') || ($unmappedStats['total_pending'] === 0 && !request()->has('unmapped_page')) ? 'show active' : '' }}" id="districtsPane" role="tabpanel">
            {{-- Filter & Search --}}
            <div class="card border mb-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.settings.locations.districts') }}" class="row g-2 align-items-end">
                        <input type="hidden" name="tab" value="districts">
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label small text-muted mb-1">Search District / Code</label>
                            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search Kathmandu, KTM, Bagmati..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small text-muted mb-1">Province</label>
                            <select name="province_id" class="form-select form-select-sm">
                                <option value="">All Provinces</option>
                                @foreach($provinces as $p)
                                    <option value="{{ $p->id }}" {{ (string) request('province_id') === (string) $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small text-muted mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                            <a href="{{ route('admin.settings.locations.districts', ['tab' => 'districts']) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Districts Table --}}
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fs-14 fw-semibold text-dark">
                        <i class="las la-list text-primary me-1"></i> Official Nepal 77 Districts Master Registry
                    </h6>
                    <span class="text-muted small">77 Districts across 7 Provinces</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>District Name</th>
                                    <th>Province</th>
                                    <th>Code</th>
                                    <th>Status</th>
                                    <th class="text-end">Orders / Bills</th>
                                    <th class="text-end">Registered Users</th>
                                    <th class="text-end">Update Settings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($districts as $index => $district)
                                    <tr>
                                        <td class="text-muted small">{{ $index + 1 }}</td>
                                        <td>
                                            <strong class="text-dark">{{ $district->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                {{ $district->province?->name ?? 'Nepal' }}
                                            </span>
                                        </td>
                                        <td><code>{{ $district->code ?? '—' }}</code></td>
                                        <td>
                                            @if($district->status === 'active')
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($district->purchase_bills_count) }}</td>
                                        <td class="text-end">{{ number_format($district->user_profiles_count) }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.settings.locations.districts.update', $district) }}" class="d-inline-flex gap-1">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="code" value="{{ $district->code }}" class="form-control form-control-xs" style="width: 70px;" placeholder="Code">
                                                <select name="status" class="form-select form-select-xs" style="width: 90px;">
                                                    <option value="active" @selected($district->status === 'active')>Active</option>
                                                    <option value="inactive" @selected($district->status === 'inactive')>Inactive</option>
                                                </select>
                                                <button type="submit" class="btn btn-xs btn-outline-primary">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No districts found matching filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Individual Bill Popup Mapping Modal --}}
<div class="modal fade" id="mapBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fs-15 fw-bold text-dark">
                    <i class="las la-map-pin text-primary me-1"></i> Individual Bill Location Mapping &amp; Verification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="mapBillModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading bill location details...
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Single Skip Form Holder --}}
<form id="singleSkipForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="reason" value="Manually skipped by admin">
</form>
@endsection

@push('scripts')
<script>
    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.bill-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.bill-checkbox:checked');
        const count = checked.length;
        document.getElementById('selectedCount').innerText = count;

        const toolbar = document.getElementById('bulkToolbar');
        if (count > 0) {
            toolbar.style.display = 'flex';
        } else {
            toolbar.style.display = 'none';
        }
    }

    function submitBulkAction(action) {
        const checked = document.querySelectorAll('.bill-checkbox:checked');
        if (checked.length === 0 && action !== 'auto_match_all') {
            alert('Please select at least one bill using checkboxes.');
            return;
        }

        if (action === 'assign_district') {
            const districtSelect = document.getElementById('bulkDistrictSelect');
            if (!districtSelect.value) {
                alert('Please select a district from the dropdown.');
                return;
            }
        }

        if (action === 'skip_selected') {
            if (!confirm('Are you sure you want to skip and ignore ' + checked.length + ' selected bills from unmapped review?')) {
                return;
            }
        }

        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkActionForm').submit();
    }

    function openBillMapModal(billId) {
        const modal = new bootstrap.Modal(document.getElementById('mapBillModal'));
        const body = document.getElementById('mapBillModalBody');
        body.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading bill context...</div>';
        modal.show();

        fetch("{{ url('admin/settings/locations/bills') }}/" + billId + "/modal")
            .then(res => res.text())
            .then(html => body.innerHTML = html)
            .catch(() => body.innerHTML = '<div class="alert alert-danger py-2">Failed to load bill mapping modal.</div>');
    }

    function skipSingleBillAction(billId) {
        if (confirm('Skip and ignore bill #' + billId + ' from unmapped review?')) {
            const form = document.getElementById('singleSkipForm');
            form.action = "{{ url('admin/settings/locations/bills') }}/" + billId + "/skip";
            form.submit();
        }
    }

    function skipCurrentBill(billId) {
        const form = document.getElementById('skipBillForm_' + billId);
        if (form && confirm('Skip bill #' + billId + '?')) {
            form.submit();
        }
    }
</script>
@endpush
