@extends('layouts.admin')

@section('title', 'Pharmacist Quiz Categories')

@push('styles')
<style>
    .cat-color-badge {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
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
                        <i class="las la-folder-open text-brand me-1"></i> Pharmacist Quiz Categories
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.index') }}" class="text-brand text-decoration-underline">Pharmacist Quiz</a> &gt; Categories Management
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm btn-brand shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                        <i class="las la-plus-circle me-1"></i> New Category
                    </button>
                    <a href="{{ route('admin.pharmacist-quiz.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-graduation-cap me-1"></i> View Quizzes
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-check-circle me-1 fs-18 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row mb-3 g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-brand-soft text-brand me-3">
                        <i class="las la-folder fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Total Categories</span>
                        <h4 class="mb-0 fw-bold text-dark mt-1">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #10B981 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-success-subtle text-success me-3">
                        <i class="las la-check-circle fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Active Categories</span>
                        <h4 class="mb-0 fw-bold text-success mt-1">{{ number_format($stats['active']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #F59E0B !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning me-3">
                        <i class="las la-pause-circle fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Inactive Categories</span>
                        <h4 class="mb-0 fw-bold text-warning mt-1">{{ number_format($stats['inactive']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #1E3A5F !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary me-3">
                        <i class="las la-graduation-cap fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Linked Quizzes</span>
                        <h4 class="mb-0 fw-bold text-primary mt-1">{{ number_format($stats['total_quizzes']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-brand-soft py-3 border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-list text-brand me-1"></i> Pharmaceutical Quiz Categories Directory
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-white text-brand border border-brand-subtle px-3 py-2 fw-semibold">
                        {{ $categories->total() }} Categories
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter & Search Form --}}
            <form method="GET" action="{{ route('admin.pharmacist-quiz.categories.index') }}" class="row g-2 mb-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search category name, description, slug..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active Only</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive Only</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-brand">Filter</button>
                    <a href="{{ route('admin.pharmacist-quiz.categories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%;">Category Name &amp; Visual</th>
                            <th style="width: 20%;">Slug Identifier</th>
                            <th style="width: 25%;">Description</th>
                            <th style="width: 10%;">Quizzes</th>
                            <th style="width: 8%;">Order</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 12%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($cat->image_url)
                                            <img src="{{ $cat->image_url }}" alt="" class="rounded-circle me-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle p-2 bg-brand-soft text-brand me-2 fs-18 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                <i class="{{ $cat->icon ?: 'las la-tag' }}"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="cat-color-badge" style="background-color: {{ $cat->color ?: '#CD0B6F' }};"></span>
                                            <strong class="text-dark">{{ $cat->name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $cat->slug }}</code></td>
                                <td>
                                    <p class="mb-0 text-muted small text-truncate" style="max-width: 260px;">
                                        {{ $cat->description ?: '—' }}
                                    </p>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pharmacist-quiz.index', ['category_id' => $cat->id]) }}" class="badge bg-brand-soft text-brand border border-brand-subtle text-decoration-none px-2 py-1 fs-12 fw-bold">
                                        {{ $cat->quizzes_count }} Quizzes
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $cat->display_order }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.pharmacist-quiz.categories.toggle-status', $cat) }}">
                                        @csrf
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" @checked($cat->is_active)>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-brand" title="Edit Category"
                                                onclick="openEditCategoryModal({{ json_encode($cat) }})">
                                            <i class="las la-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pharmacist-quiz.categories.destroy', $cat) }}" class="d-inline"
                                              onsubmit="return confirm('Delete category {{ $cat->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Category" {{ $cat->quizzes_count > 0 ? 'disabled' : '' }}>
                                                <i class="las la-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="las la-folder-open fs-48 text-muted d-block mb-2"></i>
                                    No quiz categories found.
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                            Create First Category
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: CREATE CATEGORY --}}
{{-- ======================================================= --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pharmacist-quiz.categories.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-brand-soft border-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="las la-plus-circle text-brand me-1"></i> Add Quiz Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Medicine Knowledge, Drug Interaction" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief summary of topics covered..."></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Icon Class (LineAwesome)</label>
                            <input type="text" name="icon" class="form-control" placeholder="las la-pills" value="las la-prescription-bottle-alt">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Brand Accent Color</label>
                            <div class="d-flex gap-2">
                                <input type="color" id="createColorPicker" class="form-control form-control-color" value="#CD0B6F" onchange="document.getElementById('createColorInput').value = this.value">
                                <input type="text" name="color" id="createColorInput" class="form-control" value="#CD0B6F">
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Optional Image / Icon File</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="form-check form-switch p-2 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" name="is_active" id="createActiveSwitch" value="1" checked>
                        <label class="form-check-label fw-semibold text-dark" for="createActiveSwitch">
                            Activate Category Immediately
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand px-4">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- MODAL: EDIT CATEGORY --}}
{{-- ======================================================= --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="editCategoryForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-brand-soft border-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="las la-edit text-brand me-1"></i> Edit Quiz Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Icon Class</label>
                            <input type="text" name="icon" id="editIcon" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Accent Color</label>
                            <div class="d-flex gap-2">
                                <input type="color" id="editColorPicker" class="form-control form-control-color" value="#CD0B6F" onchange="document.getElementById('editColorInput').value = this.value">
                                <input type="text" name="color" id="editColorInput" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="display_order" id="editDisplayOrder" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">New Image (Optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="form-check form-switch p-2 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" name="is_active" id="editActiveSwitch" value="1">
                        <label class="form-check-label fw-semibold text-dark" for="editActiveSwitch">
                            Category Active &amp; Visible to Pharmacists
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditCategoryModal(category) {
        document.getElementById('editCategoryForm').action = `/admin/pharmacist-quiz/categories/${category.id}`;
        document.getElementById('editName').value = category.name || '';
        document.getElementById('editDescription').value = category.description || '';
        document.getElementById('editIcon').value = category.icon || 'las la-prescription-bottle-alt';
        document.getElementById('editColorInput').value = category.color || '#CD0B6F';
        document.getElementById('editColorPicker').value = (category.color && category.color.startsWith('#')) ? category.color : '#CD0B6F';
        document.getElementById('editDisplayOrder').value = category.display_order || 0;
        document.getElementById('editActiveSwitch').checked = Boolean(category.is_active);

        const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        modal.show();
    }
</script>
@endpush
