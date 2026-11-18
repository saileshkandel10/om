@extends('layouts.admin')

@section('title', 'Product Management')

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
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s, background-color 0.2s;
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
                        <h4 class="page-title mb-1">Company Product Catalogue</h4>
                        <p class="text-muted small mb-0">Manage all pharmaceutical products, pricing, categories, and inventory specifications.</p>
                    </div>
                    <div>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Product Management</a></li>
                            <li class="breadcrumb-item active">Products</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title mb-0">All Catalogue Products</h4>
                                <small class="text-muted">Showing {{ $products->count() }} active and archived products</small>
                            </div>
                            <div class="col-auto d-flex flex-wrap gap-2 align-items-center">
                                {{-- Export Dropdown --}}
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="las la-file-export me-1 fs-15"></i> Export Products
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><h6 class="dropdown-header">Choose Format</h6></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.products.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}">
                                                <i class="las la-file-excel text-success me-2 fs-16"></i> Export Excel (.xlsx)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.products.export', array_merge(request()->all(), ['format' => 'csv'])) }}">
                                                <i class="las la-file-csv text-primary me-2 fs-16"></i> Export CSV (.csv)
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                {{-- Bulk Import Button --}}
                                <button type="button" class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#importProductModal">
                                    <i class="las la-file-upload me-1 fs-15"></i> Bulk Import
                                </button>

                                {{-- New Product Button --}}
                                <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-brand">
                                    <i class="las la-plus me-1 fs-15"></i> New Product
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        {{-- Filter Toolbar --}}
                        <form method="GET" action="{{ route('admin.products.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto flex-grow-1" style="max-width: 320px;">
                                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search brand name, generic, SKU..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label small fw-semibold text-muted mb-1">Category</label>
                                <select name="category_id" class="form-select form-select-sm">
                                    <option value="">All Categories</option>
                                    @php($catList = \App\Models\ProductCategory::pluck('name', 'id'))
                                    @foreach ($catList as $id => $name)
                                        <option value="{{ $id }}" @selected((int) request('category_id') === $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="active" @selected(request('status') === 'active')>Active</option>
                                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="las la-filter"></i> Filter
                                </button>
                                @if(request()->hasAny(['search', 'category_id', 'status']))
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                @endif
                            </div>
                        </form>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table datatable align-middle" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Generic Name</th>
                                        <th>Category</th>
                                        <th>Pack Size</th>
                                        <th>Strength / Dosage</th>
                                        <th>MRP (Rs)</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" alt="" class="rounded" width="36" height="36" style="object-fit:cover">
                                                    @else
                                                        <div class="rounded bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:15px"><i class="las la-pills"></i></div>
                                                    @endif
                                                    <div>
                                                        <span class="fw-bold text-dark">{{ $product->brand_name }}</span>
                                                        @if ($product->sku)
                                                            <br><small class="text-muted"><span class="badge bg-light text-dark border">{{ $product->sku }}</span></small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small">{{ $product->generic_name ?? '—' }}</td>
                                            <td><span class="badge bg-primary-subtle text-primary">{{ $product->category?->name ?? 'Uncategorized' }}</span></td>
                                            <td class="small">{{ $product->pack_size ?? '—' }}</td>
                                            <td class="small">{{ $product->strength ?? '' }} {{ $product->dosage_form ?? '' }}</td>
                                            <td class="fw-semibold text-dark">
                                                {{ $product->mrp !== null ? 'Rs ' . number_format($product->mrp, 2) : '—' }}
                                            </td>
                                            <td>
                                                @php($statusColor = $product->is_active ? 'success' : 'secondary')
                                                <span class="badge bg-{{ $statusColor }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td data-sort="{{ $product->created_at->format('Y/m/d') }}" class="small text-muted">{{ $product->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.products.show', $product) }}">
                                                            <i class="las la-eye me-1 text-primary"></i> View Details
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.products.edit', $product) }}">
                                                            <i class="las la-edit me-1 text-warning"></i> Edit Product
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="las la-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="las la-box-open fs-36 d-block mb-2"></i>
                                                No products found matching your search.
                                            </td>
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

    {{-- Bulk Import Modal --}}
    <div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="productImportForm" method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="importProductModalLabel">
                            <i class="las la-file-upload text-primary me-1"></i> Bulk Import Catalogue Products
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Template Download Notice --}}
                        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3 p-3 rounded">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark"><i class="las la-download text-primary me-1"></i> Need a format template?</h6>
                                <small class="text-muted">Download our pre-structured template with column headers and sample medicine records.</small>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('admin.products.sample-template', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success">
                                    <i class="las la-file-excel me-1"></i> Excel (.xlsx)
                                </a>
                                <a href="{{ route('admin.products.sample-template', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="las la-file-csv me-1"></i> CSV (.csv)
                                </a>
                            </div>
                        </div>

                        {{-- File Upload Drop Zone --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Excel / CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="productFileInput" class="form-control mb-2" accept=".xlsx,.xls,.csv,.txt" required onchange="handleProductFileSelect(this)">
                            <div class="drop-zone p-3" id="productDropZone" onclick="document.getElementById('productFileInput').click()">
                                <i class="las la-cloud-upload-alt fs-30 text-brand mb-1 d-block"></i>
                                <h6 class="mb-0 fs-13" id="productDropZoneText">Click here or drag &amp; drop Excel or CSV file</h6>
                                <small class="text-muted fs-11">Supported formats: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code> (Max 50MB)</small>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Duplicate Handling</label>
                                <select name="on_duplicate" class="form-select form-select-sm">
                                    <option value="update" selected>Update Existing (by SKU / Brand)</option>
                                    <option value="skip">Skip Duplicates</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Default Status</label>
                                <select name="default_status" class="form-select form-select-sm">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_create_categories" value="1" id="autoCreateCatSwitch" checked>
                                    <label class="form-check-label small fw-semibold" for="autoCreateCatSwitch">Auto-create new categories</label>
                                </div>
                            </div>
                        </div>

                        {{-- Instant Preview Container --}}
                        <div id="productPreviewContainer" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold"><i class="las la-eye text-primary me-1"></i> File Preview (First 10 Rows)</h6>
                                <span id="productPreviewBadge" class="badge bg-secondary-subtle text-dark"></span>
                            </div>
                            <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                <table class="table table-sm table-bordered align-middle small mb-0" id="productPreviewTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>SKU</th>
                                            <th>Brand Name</th>
                                            <th>Generic</th>
                                            <th>Category</th>
                                            <th>MRP (Rs)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productPreviewTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Loading Indicator --}}
                        <div id="productImportProgress" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status"></div>
                            <h6 class="mt-2 fw-semibold">Importing catalogue products...</h6>
                            <p class="text-muted small mb-0">Please wait while the products and categories are being processed.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-brand" id="productImportSubmitBtn">
                            <i class="las la-upload me-1"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function handleProductFileSelect(input) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];
        document.getElementById('productDropZoneText').innerHTML = '<strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(1) + ' KB)';
        document.getElementById('productImportSubmitBtn').disabled = false;

        // Fetch Live Preview via AJAX
        var formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        var previewContainer = document.getElementById('productPreviewContainer');
        var previewBody = document.getElementById('productPreviewTableBody');
        var previewBadge = document.getElementById('productPreviewBadge');

        previewBody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Reading preview...</td></tr>';
        previewContainer.style.display = 'block';

        fetch('{{ route("admin.products.preview-import") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                previewBody.innerHTML = '<tr><td colspan="7" class="text-danger text-center py-3"><i class="las la-exclamation-triangle"></i> ' + data.error + '</td></tr>';
                return;
            }

            previewBadge.textContent = 'Found ~' + (data.total_estimated || 0) + ' rows (' + data.valid_count + ' valid, ' + data.invalid_count + ' invalid)';
            previewBadge.className = data.invalid_count > 0 ? 'badge bg-warning-subtle text-warning' : 'badge bg-success-subtle text-success';

            if (!data.rows || data.rows.length === 0) {
                previewBody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">No readable rows found.</td></tr>';
                return;
            }

            var html = '';
            data.rows.forEach(function(row) {
                var rowClass = row.is_valid ? '' : 'table-danger';
                var statusBadge = row.is_valid
                    ? '<span class="badge bg-success-subtle text-success">Valid</span>'
                    : '<span class="badge bg-danger-subtle text-danger" title="' + (row.validation_notes || '') + '">Invalid</span>';

                html += '<tr class="' + rowClass + '">' +
                    '<td>' + row.row_number + '</td>' +
                    '<td>' + (row.sku ? '<span class="badge bg-light text-dark border">' + row.sku + '</span>' : '<span class="text-muted">Auto</span>') + '</td>' +
                    '<td><strong>' + (row.brand_name || '<em class="text-danger">Missing</em>') + '</strong></td>' +
                    '<td>' + (row.generic_name || '—') + '</td>' +
                    '<td>' + (row.category_raw || '<span class="text-muted">Uncategorized</span>') + '</td>' +
                    '<td>' + (row.mrp ? 'Rs ' + Number(row.mrp).toFixed(2) : '—') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                '</tr>';
            });

            previewBody.innerHTML = html;
        })
        .catch(function(err) {
            previewBody.innerHTML = '<tr><td colspan="7" class="text-danger text-center py-3">Failed to load preview. You can still proceed with import.</td></tr>';
        });
    }

    // Drag & Drop event handlers
    var dropZone = document.getElementById('productDropZone');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.style.borderColor = '#CD0B6F';
                dropZone.style.backgroundColor = '#fff5f9';
            }, false);
        });
        ['dragleave', 'drop'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.style.borderColor = '#cbd5e1';
                dropZone.style.backgroundColor = '#f8fafc';
            }, false);
        });
        dropZone.addEventListener('drop', function(e) {
            var dt = e.dataTransfer;
            var files = dt.files;
            if (files.length) {
                var fileInput = document.getElementById('productFileInput');
                fileInput.files = files;
                handleProductFileSelect(fileInput);
            }
        }, false);
    }
</script>
@endpush
