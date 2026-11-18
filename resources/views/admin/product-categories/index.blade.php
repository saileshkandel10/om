@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Product Categories</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Product Management</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Product Categories</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.product-categories.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Category</a>
                                <span class="text-muted small ms-2">{{ $categories->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.product-categories.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search categories..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.product-categories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Linked Products</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td><span class="fw-semibold text-dark">{{ $category->name }}</span></td>
                                            <td class="text-truncate text-muted" style="max-width:300px">{{ $category->description ?? '—' }}</td>
                                            <td>
                                                @php($total = $category->products_count + $category->other_products_count)
                                                @if($total > 0)
                                                    <span class="badge bg-primary fs-12">{{ $total }} Total</span>
                                                    <div class="small text-muted mt-1">
                                                        {{ $category->products_count }} Catalog | {{ $category->other_products_count }} Market
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-muted border">0 Products</span>
                                                @endif
                                            </td>
                                            <td data-sort="{{ $category->created_at->format('Y/m/d') }}">{{ $category->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.product-categories.edit', $category) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.product-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
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
@endsection

