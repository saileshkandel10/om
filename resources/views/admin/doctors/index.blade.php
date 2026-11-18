@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Doctors</h4>
                    <div>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">User Management</a></li>
                            <li class="breadcrumb-item active">Doctors</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="las la-check-circle me-1 fs-18 align-middle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('import_stats'))
            @php($stats = session('import_stats'))
            <div class="card bg-light-subtle border mb-3">
                <div class="card-body py-3">
                    <h6 class="mb-2 text-primary"><i class="las la-info-circle me-1"></i> Latest Import Breakdown:</h6>
                    <div class="row g-2 text-center">
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <div class="text-muted small">Total Rows</div>
                                <div class="fs-18 fw-bold">{{ number_format($stats['total_rows']) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <div class="text-success small">Imported (New)</div>
                                <div class="fs-18 fw-bold text-success">{{ number_format($stats['imported']) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <div class="text-warning small">Duplicates Filtered</div>
                                <div class="fs-18 fw-bold text-warning">{{ number_format($stats['duplicates_skipped']) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <div class="text-info small">Updated</div>
                                <div class="fs-18 fw-bold text-info">{{ number_format($stats['updated']) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <div class="text-danger small">Invalid Rows</div>
                                <div class="fs-18 fw-bold text-danger">{{ number_format($stats['invalid_skipped']) }}</div>
                            </div>
                        </div>
                    </div>
                    @if (!empty($stats['errors']))
                        <div class="mt-2 text-danger small">
                            <strong>Notes / Warnings:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach (array_slice($stats['errors'], 0, 5) as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Doctors</h4>
                                <p class="text-muted mb-0 small">{{ number_format($doctors->total()) }} total doctors in system</p>
                            </div>
                            <div class="col-auto d-flex flex-wrap gap-2">
                                <!-- Export Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="las la-file-export me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li class="dropdown-header text-uppercase small fw-bold text-muted">This Page ({{ number_format($doctors->count()) }} rows)</li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.doctors.export', array_merge(request()->query(), ['scope' => 'current', 'format' => 'xlsx', 'page' => $doctors->currentPage(), 'per_page' => $doctors->perPage()])) }}">
                                                <i class="las la-file-excel me-1 text-success"></i> Export This Page (.xlsx)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.doctors.export', array_merge(request()->query(), ['scope' => 'current', 'format' => 'csv', 'page' => $doctors->currentPage(), 'per_page' => $doctors->perPage()])) }}">
                                                <i class="las la-file-csv me-1 text-info"></i> Export This Page (.csv)
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li class="dropdown-header text-uppercase small fw-bold text-muted">All Matching ({{ number_format($doctors->total()) }} rows)</li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.doctors.export', array_merge(request()->query(), ['scope' => 'all', 'format' => 'xlsx'])) }}">
                                                <i class="las la-file-excel me-1 text-success"></i> Export All Data (.xlsx)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.doctors.export', array_merge(request()->query(), ['scope' => 'all', 'format' => 'csv'])) }}">
                                                <i class="las la-file-csv me-1 text-info"></i> Export All Data (.csv)
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exportModal">
                                                <i class="las la-sliders-h me-1 text-secondary"></i> Custom Export Options...
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Sample Template Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="las la-download me-1"></i> Sample Template
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="{{ route('admin.doctors.sample', ['format' => 'xlsx']) }}"><i class="las la-file-excel me-1 text-success"></i> Excel Template (.xlsx)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.doctors.sample', ['format' => 'csv']) }}"><i class="las la-file-csv me-1 text-info"></i> CSV Template (.csv)</a></li>
                                    </ul>
                                </div>

                                <!-- Import Button -->
                                <button type="button" class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="las la-file-excel me-1"></i> Import Doctors
                                </button>

                                <!-- Create Doctor Button -->
                                <a href="{{ route('admin.doctors.create') }}" class="btn btn-sm btn-brand">
                                    <i class="las la-plus me-1"></i> Add Doctor
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <form method="GET" action="{{ route('admin.doctors.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small text-muted mb-1">Search</label>
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search name, reg no, hospital, phone..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small text-muted mb-1">Specialty</label>
                                <select name="specialty" class="form-select form-select-sm">
                                    <option value="">All Specialties</option>
                                    @foreach($specialties as $spec)
                                        <option value="{{ $spec }}" {{ request('specialty') === $spec ? 'selected' : '' }}>{{ $spec }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label class="form-label small text-muted mb-1">Per Page</label>
                                <select name="per_page" class="form-select form-select-sm">
                                    <option value="25" {{ (request('per_page') == 25) ? 'selected' : '' }}>25 per page</option>
                                    <option value="50" {{ (request('per_page', 50) == 50) ? 'selected' : '' }}>50 per page</option>
                                    <option value="100" {{ (request('per_page') == 100) ? 'selected' : '' }}>100 per page</option>
                                    <option value="250" {{ (request('per_page') == 250) ? 'selected' : '' }}>250 per page</option>
                                    <option value="500" {{ (request('per_page') == 500) ? 'selected' : '' }}>500 per page</option>
                                    <option value="1000" {{ (request('per_page') == 1000) ? 'selected' : '' }}>1000 per page</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="las la-filter me-1"></i> Filter</button>
                                <a href="{{ route('admin.doctors.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Doctor Name</th>
                                        <th>Registration No.</th>
                                        <th>Specialty</th>
                                        <th>Hospital / Clinic</th>
                                        <th>Phone / Email</th>
                                        <th>Prescriptions</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($doctors as $index => $doctor)
                                        <tr>
                                            <td class="text-muted small">{{ ($doctors->currentPage() - 1) * $doctors->perPage() + $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold" style="width:34px;height:34px;font-size:13px">
                                                        {{ $doctor->user?->name ? strtoupper(substr($doctor->user->name, 0, 2)) : 'DR' }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold text-dark d-block">{{ $doctor->user?->name ?? 'Unknown Doctor' }}</span>
                                                        <small class="text-muted">DOC-{{ 1000 + $doctor->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code class="fw-bold fs-13">{{ $doctor->registration_no }}</code></td>
                                            <td>
                                                @if(!empty($doctor->specialty))
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $doctor->specialty }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $doctor->hospital_clinic }}">
                                                    {{ $doctor->hospital_clinic ?? '—' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div><i class="las la-phone text-muted me-1"></i>{{ $doctor->user?->mobile ?? '—' }}</div>
                                                <small class="text-muted"><i class="las la-envelope text-muted me-1"></i>{{ $doctor->user?->email ?? '—' }}</small>
                                            </td>
                                            <td>
                                                @if ($doctor->prescriptions_count > 0)
                                                    <span class="badge bg-info">{{ $doctor->prescriptions_count }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted border">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php($statusColor = match ($doctor->status) {
                                                    'active' => 'success',
                                                    'approved' => 'success',
                                                    'inactive' => 'warning',
                                                    'suspended' => 'danger',
                                                    'pending' => 'secondary',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }} text-capitalize">{{ $doctor->status ?? 'pending' }}</span>
                                            </td>
                                            <td>
                                                <span class="small text-muted">{{ $doctor->created_at?->format('M d, Y') ?? '—' }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-18 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.doctors.show', $doctor) }}"><i class="las la-eye me-1"></i> View Details</a>
                                                        <a class="dropdown-item" href="{{ route('admin.doctors.edit', $doctor) }}"><i class="las la-edit me-1"></i> Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" onsubmit="return confirm('Delete doctor #{{ $doctor->registration_no }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"><i class="las la-trash me-1"></i> Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <i class="las la-user-md fs-36 text-secondary d-block mb-2"></i>
                                                <span class="fw-medium">No doctors found matching the criteria.</span>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                                                        <i class="las la-file-excel me-1"></i> Import Doctors
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row align-items-center mt-3 pt-2 border-top">
                            <div class="col-sm-6 text-muted small">
                                Showing <span class="fw-semibold">{{ number_format($doctors->firstItem() ?? 0) }}</span> to
                                <span class="fw-semibold">{{ number_format($doctors->lastItem() ?? 0) }}</span> of
                                <span class="fw-semibold">{{ number_format($doctors->total()) }}</span> doctors
                            </div>
                            <div class="col-sm-6 d-flex justify-content-sm-end mt-2 mt-sm-0">
                                {{ $doctors->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.doctors.export') }}" method="GET">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if (request('specialty'))
                        <input type="hidden" name="specialty" value="{{ request('specialty') }}">
                    @endif
                    <input type="hidden" name="page" value="{{ $doctors->currentPage() }}">
                    <input type="hidden" name="per_page" value="{{ $doctors->perPage() }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">
                            <i class="las la-file-export text-success me-1 fs-20 align-middle"></i>
                            Export Doctors Data
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Export Scope <span class="text-danger">*</span></label>
                            <div class="border rounded p-3 bg-light-subtle">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="scope" id="scopeCurrent" value="current" checked>
                                    <label class="form-check-label" for="scopeCurrent">
                                        <strong>This page only</strong>
                                        <span class="d-block text-muted small">Exports current page {{ $doctors->currentPage() }} ({{ number_format($doctors->count()) }} records).</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="scope" id="scopeAll" value="all">
                                    <label class="form-check-label" for="scopeAll">
                                        <strong>All data</strong>
                                        <span class="d-block text-muted small">Exports all {{ number_format($doctors->total()) }} matching records in the system.</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Format <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check border rounded p-2 ps-4">
                                        <input class="form-check-input" type="radio" name="format" id="formatXlsx" value="xlsx" checked>
                                        <label class="form-check-label" for="formatXlsx">
                                            <i class="las la-file-excel text-success fs-18 align-middle me-1"></i> Excel (.xlsx)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check border rounded p-2 ps-4">
                                        <input class="form-check-input" type="radio" name="format" id="formatCsv" value="csv">
                                        <label class="form-check-label" for="formatCsv">
                                            <i class="las la-file-csv text-info fs-18 align-middle me-1"></i> CSV (.csv)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (request('search') || request('status') || request('specialty'))
                            <div class="alert alert-warning py-2 px-3 small mb-0">
                                <i class="las la-filter me-1"></i> <strong>Active filters will be applied:</strong>
                                @if (request('search')) <span class="d-block">Search: "{{ request('search') }}"</span> @endif
                                @if (request('status')) <span class="d-block">Status: {{ ucfirst(request('status')) }}</span> @endif
                                @if (request('specialty')) <span class="d-block">Specialty: {{ request('specialty') }}</span> @endif
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="las la-download me-1"></i> Download Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.doctors.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">
                            <i class="las la-file-excel text-success me-1 fs-20 align-middle"></i>
                            Import Doctors from Excel / CSV
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="las la-info-circle me-1"></i>
                            <strong>High-Capacity Import:</strong> Supports thousands of doctor records with automatic user account setup, medical council ID validation, and duplicate handling.
                        </div>

                        <div class="mb-3">
                            <label for="excelFile" class="form-label fw-semibold">Select Excel / CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="excelFile" class="form-control" accept=".xlsx,.xls,.csv,.txt" required>
                            <div class="form-text small">
                                Supported formats: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code> (Max file size: 50MB).
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Duplicate Registration No. Handling <span class="text-danger">*</span></label>
                            <div class="border rounded p-2 bg-light-subtle">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="on_duplicate" id="onDupSkip" value="skip" checked>
                                    <label class="form-check-label" for="onDupSkip">
                                        <strong>Filter &amp; Skip duplicates (Recommended)</strong>
                                        <span class="d-block text-muted small">If a doctor with this Registration No already exists, it will be skipped safely.</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="on_duplicate" id="onDupUpdate" value="update">
                                    <label class="form-check-label" for="onDupUpdate">
                                        <strong>Update existing records</strong>
                                        <span class="d-block text-muted small">Update doctor name, specialty, hospital, phone, and email for existing registration numbers.</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="defaultStatus" class="form-label fw-semibold">Default Status for Imported Doctors</label>
                            <select name="default_status" id="defaultStatus" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="border rounded p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold small text-uppercase text-muted">Expected File Structure</span>
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-outline-primary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown">
                                        <i class="las la-download me-1"></i> Download Template
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="{{ route('admin.doctors.sample', ['format' => 'xlsx']) }}"><i class="las la-file-excel me-1 text-success"></i> Excel (.xlsx)</a></li>
                                        <li><a class="dropdown-item small" href="{{ route('admin.doctors.sample', ['format' => 'csv']) }}"><i class="las la-file-csv me-1 text-info"></i> CSV (.csv)</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 small text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Registration No <span class="text-danger">*</span></th>
                                            <th>Doctor Name <span class="text-danger">*</span></th>
                                            <th>Specialty</th>
                                            <th>Hospital / Clinic</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>NMC-10842</code></td>
                                            <td>Dr. Anil Sharma</td>
                                            <td>Cardiology</td>
                                            <td>Om Hospital, Kathmandu</td>
                                            <td>9841234560</td>
                                            <td>dr.anil@example.com</td>
                                            <td>active</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-file-upload me-1"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
