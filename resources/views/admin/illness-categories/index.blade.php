@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Illness Categories</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Product Management</a></li>
                            <li class="breadcrumb-item active">Illness Categories</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Illness Categories</h4>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <a href="{{ route('admin.reports.illness-categories') }}" class="btn btn-sm btn-outline-success">
                                    <i class="las la-chart-bar me-1"></i> Illness Report
                                </a>
                                <a href="{{ route('admin.illness-categories.create') }}" class="btn btn-sm btn-primary">
                                    <i class="las la-plus me-1"></i> New Category
                                </a>
                                <span class="text-muted small ms-1 align-self-center">{{ $categories->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.illness-categories.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search categories..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.illness-categories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Linked Products</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td><span class="fw-medium">{{ $category->name }}</span></td>
                                            <td class="text-truncate" style="max-width:250px">{{ $category->description ?? '—' }}</td>
                                            <td>
                                                @if (($category->status ?? 'active') === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php($totalProducts = $category->products_count + $category->other_products_count)
                                                @if ($totalProducts > 0)
                                                    <a href="#" class="badge bg-primary text-decoration-none"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#productsModal{{ $category->id }}"
                                                       style="cursor:pointer">
                                                        <i class="las la-pills me-1"></i>{{ $totalProducts }} Total Products
                                                    </a>
                                                    <div class="mt-1 small text-muted">
                                                        {{ $category->products_count }} Catalog | {{ $category->other_products_count }} Market
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-muted border">0 Products</span>
                                                @endif
                                                <div class="mt-1">
                                                    <a href="{{ route('admin.products.index', ['illness_category_id' => $category->id]) }}"
                                                       class="text-muted small" title="View in product catalog">
                                                        <i class="las la-external-link-alt"></i> Catalog
                                                    </a>
                                                    <span class="text-muted mx-1">|</span>
                                                    <a href="{{ route('admin.other-products.index', ['illness_category_id' => $category->id]) }}"
                                                       class="text-muted small" title="View in market products">
                                                        <i class="las la-external-link-alt"></i> Market
                                                    </a>
                                                </div>
                                            </td>
                                            <td data-sort="{{ $category->created_at->format('Y/m/d') }}">{{ $category->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#productsModal{{ $category->id }}">
                                                            <i class="las la-pills me-1"></i> View Products
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.reports.illness-categories', ['illness_category_id' => $category->id]) }}">
                                                            <i class="las la-chart-bar me-1"></i> View in Report
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="{{ route('admin.illness-categories.edit', $category) }}">
                                                            <i class="las la-edit me-1"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.illness-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category and all patient illness flags from it?')">
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
                                            <td colspan="6" class="text-center py-4 text-muted">No categories found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($unassignedProducts->count() > 0)
                            <div class="mt-3 p-3 border rounded bg-warning-subtle">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="las la-exclamation-triangle text-warning fs-20"></i>
                                    <strong>{{ $unassignedProducts->count() }} product(s) not linked to any illness category.</strong>
                                </div>
                                <p class="text-muted small mb-2">Products without an illness category cannot be used for patient flagging. Use the "Link Products" option in each illness category to assign them.</p>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($unassignedProducts->take(5) as $up)
                                        <a href="{{ route('admin.products.edit', $up) }}" class="badge bg-light text-dark border text-decoration-none">
                                            {{ $up->brand_name }}
                                        </a>
                                    @endforeach
                                    @if ($unassignedProducts->count() > 5)
                                        <a href="{{ route('admin.products.index', ['illness_category_id' => 'none']) }}" class="badge bg-secondary text-decoration-none">
                                            +{{ $unassignedProducts->count() - 5 }} more
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Linked Products Modals - one per category -->
    @foreach ($categories as $category)
        <div class="modal fade" id="productsModal{{ $category->id }}" tabindex="-1" aria-labelledby="productsModalLabel{{ $category->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productsModalLabel{{ $category->id }}">
                            <i class="las la-pills text-primary me-1"></i>
                            Products Linked to <strong>{{ $category->name }}</strong>
                            <span class="badge bg-primary ms-2">{{ $category->products_count }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                        <div class="modal-body p-0">
                            {{-- Tabs for Products --}}
                            <ul class="nav nav-tabs p-3 pb-0 border-bottom" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#catalogProductTab{{ $category->id }}" role="tab">
                                        <i class="las la-prescription-bottle-alt text-brand me-1"></i> Catalog Products <span class="badge bg-brand ms-1">{{ $category->products_count }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#marketProductTab{{ $category->id }}" role="tab">
                                        <i class="las la-store me-1 text-info"></i> Other Market Products <span class="badge bg-info ms-1">{{ $category->other_products_count }}</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content border-top bg-light-subtle">
                                <div class="tab-pane active" id="catalogProductTab{{ $category->id }}" role="tabpanel">
                                    {{-- Link New Products Form --}}
                                    @if ($unassignedProducts->count() > 0)
                                        <div class="p-3 border-bottom bg-white">
                                            <form method="POST" action="{{ route('admin.illness-categories.products.link', $category) }}" class="row g-2 align-items-end">
                                                @csrf
                                                <div class="col">
                                                    <label class="form-label form-label-sm fw-semibold">Link Unassigned Catalog Products:</label>
                                                    <select name="product_ids[]" class="form-select form-select-sm" multiple style="height: 60px;">
                                                        @foreach ($unassignedProducts as $up)
                                                            <option value="{{ $up->id }}">{{ $up->brand_name }} ({{ $up->sku ?? 'No SKU' }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="las la-link me-1"></i> Link Elements
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif

                                    <div class="table-responsive bg-white" style="max-height: 400px; overflow-y:auto">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Generic Name</th>
                                                    <th>Dosage / Form</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($category->products as $product)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="fw-medium">{{ $product->brand_name }}</span>
                                                                <code class="small d-block text-muted">{{ $product->sku ?? '—' }}</code>
                                                            </div>
                                                        </td>
                                                        <td class="text-muted small">{{ $product->generic_name ?? '—' }}</td>
                                                        <td class="small">{{ trim(($product->strength ?? '').' '.($product->dosage_form ?? '')) ?: '—' }}</td>
                                                        <td><span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                                                        <td class="text-end">
                                                            <div class="d-flex gap-1 justify-content-end">
                                                                <form method="POST" action="{{ route('admin.illness-categories.products.unlink', [$category, $product]) }}" onsubmit="return confirm('Exclude {{ $product->brand_name }} from {{ $category->name }}?')">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" title="Exclude from Category">
                                                                        <i class="las la-unlink"></i> Exclude
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-center py-4 text-muted"><i class="las la-inbox fs-36 d-block mb-1"></i>No products linked to this category yet.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="marketProductTab{{ $category->id }}" role="tabpanel">
                                    <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
                                        <small class="text-muted">These are external products detected via OCR and verified.</small>
                                        <a href="{{ route('admin.other-products.index') }}" class="btn btn-sm btn-info text-white"><i class="las la-plus me-1"></i> Add / Map Market Products</a>
                                    </div>
                                    <div class="table-responsive bg-white" style="max-height: 400px; overflow-y:auto">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Brand Name</th>
                                                    <th>Generic Name</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($category->otherProducts as $product)
                                                    <tr>
                                                        <td class="fw-bold">{{ $product->brand_name }}</td>
                                                        <td>{{ $product->generic_name ?? '—' }}</td>
                                                        <td><span class="badge {{ $product->is_verified ? 'bg-success' : 'bg-warning' }}">{{ ucfirst($product->status) }}</span></td>
                                                        <td class="text-end">
                                                            <a href="{{ route('admin.other-products.index', ['search' => $product->brand_name]) }}" class="btn btn-xs btn-outline-primary">Manage Item</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="las la-inbox fs-36 d-block mb-1"></i>No market products mapped to this illness category.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="modal-footer">
                        <a href="{{ route('admin.products.index', ['illness_category_id' => $category->id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="las la-external-link-alt me-1"></i> Open in Full Product Catalog
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
