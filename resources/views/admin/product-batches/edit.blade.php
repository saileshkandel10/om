@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Edit Product Batch</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.product-batches.index') }}">Product Batches</a></li>
                            <li class="breadcrumb-item active">Edit Batch</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Batch Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.product-batches.update', $productBatch) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <label for="product_id" class="col-sm-3 col-form-label text-end">Product <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('product_id') is-invalid @enderror"
                                        id="product_id" name="product_id" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $id => $name)
                                            <option value="{{ $id }}" @selected(old('product_id', $productBatch->product_id) == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="batch_no" class="col-sm-3 col-form-label text-end">Batch No. <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('batch_no') is-invalid @enderror"
                                        id="batch_no" name="batch_no"
                                        value="{{ old('batch_no', $productBatch->batch_no) }}"
                                        placeholder="Enter batch number" required>
                                    @error('batch_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="manufacture_date" class="col-sm-3 col-form-label text-end">Manufacture Date</label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('manufacture_date') is-invalid @enderror"
                                        id="manufacture_date" name="manufacture_date"
                                        value="{{ old('manufacture_date', $productBatch->manufacture_date?->format('Y-m-d')) }}">
                                    @error('manufacture_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="expiry_date" class="col-sm-3 col-form-label text-end">Expiry Date</label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('expiry_date') is-invalid @enderror"
                                        id="expiry_date" name="expiry_date"
                                        value="{{ old('expiry_date', $productBatch->expiry_date?->format('Y-m-d')) }}">
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                        <option value="active" @selected(old('status', $productBatch->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $productBatch->status) === 'inactive')>Inactive</option>
                                        <option value="expired" @selected(old('status', $productBatch->status) === 'expired')>Expired</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-sm-9 ms-auto">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="las la-save me-1"></i> Update Batch
                                    </button>
                                    <a href="{{ route('admin.product-batches.index') }}" class="btn btn-outline-secondary ms-2">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border border-danger">
                    <div class="card-header bg-danger-subtle">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title text-danger mb-0">Danger Zone</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-semibold mb-1">Delete this batch</h6>
                                <p class="text-muted mb-0 small">Once deleted, the batch record will be permanently removed.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.product-batches.destroy', $productBatch) }}"
                                onsubmit="return confirm('Are you sure you want to delete batch {{ $productBatch->batch_no }}? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="las la-trash-alt me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
