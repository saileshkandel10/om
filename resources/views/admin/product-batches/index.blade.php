@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Product Batches</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Product Management</a></li>
                            <li class="breadcrumb-item active">Batches</li>
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
                                <h4 class="card-title">All Product Batches</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.product-batches.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Batch</a>
                                <span class="text-muted small ms-2">{{ $batches->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.product-batches.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search batch no..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="active" @selected(request('status') === 'active')>Active</option>
                                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.product-batches.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Batch No.</th>
                                        <th>Product</th>
                                        <th>Manufacture Date</th>
                                        <th>Expiry Date</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($batches as $batch)
                                        <tr>
                                            <td><code>{{ $batch->batch_no }}</code></td>
                                            <td>
                                                <span class="fw-medium">{{ $batch->product?->brand_name ?? '—' }}</span>
                                            </td>
                                            <td>{{ $batch->manufacture_date?->format('M d, Y') ?? '—' }}</td>
                                            <td>{{ $batch->expiry_date?->format('M d, Y') ?? '—' }}</td>
                                            <td>
                                                @php($statusColor = match ($batch->status) {
                                                    'active' => 'success',
                                                    'inactive' => 'warning',
                                                    'expired' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $batch->status ?? 'active' }}</span>
                                            </td>
                                            <td data-sort="{{ $batch->created_at->format('Y/m/d') }}">{{ $batch->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.product-batches.show', $batch) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('admin.product-batches.edit', $batch) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.product-batches.destroy', $batch) }}" onsubmit="return confirm('Delete this batch?')">
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
                                            <td colspan="7" class="text-center py-4 text-muted">No batches found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

