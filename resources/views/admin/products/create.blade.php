@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Create Product</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                            <li class="breadcrumb-item active">Create Product</li>
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
                                <h4 class="card-title">Product Information & Image</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="mt-3">
                            @csrf

                            <div class="mb-3 row">
                                <label for="sku" class="col-sm-3 col-form-label text-end">SKU <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('sku') is-invalid @enderror"
                                        id="sku" name="sku"
                                        value="{{ old('sku') }}"
                                        placeholder="e.g., MD-1001" required>
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="brand_name" class="col-sm-3 col-form-label text-end">Brand Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('brand_name') is-invalid @enderror"
                                        id="brand_name" name="brand_name"
                                        value="{{ old('brand_name') }}"
                                        placeholder="Enter brand name" required>
                                    @error('brand_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="generic_name" class="col-sm-3 col-form-label text-end">Generic Name</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('generic_name') is-invalid @enderror"
                                        id="generic_name" name="generic_name"
                                        value="{{ old('generic_name') }}"
                                        placeholder="Enter generic name">
                                    @error('generic_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="product_category_id" class="col-sm-3 col-form-label text-end">Category</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('product_category_id') is-invalid @enderror"
                                        id="product_category_id" name="product_category_id">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}" @selected(old('product_category_id') == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="illness_category_id" class="col-sm-3 col-form-label text-end">Illness Category</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('illness_category_id') is-invalid @enderror"
                                        id="illness_category_id" name="illness_category_id">
                                        <option value="">Select Illness Category</option>
                                        @foreach ($illnessCategories as $id => $name)
                                            <option value="{{ $id }}" @selected(old('illness_category_id') == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('illness_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="dosage_form" class="col-sm-3 col-form-label text-end">Dosage Form</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('dosage_form') is-invalid @enderror"
                                        id="dosage_form" name="dosage_form"
                                        value="{{ old('dosage_form') }}"
                                        placeholder="e.g., Tablet, Capsule, Syrup">
                                    @error('dosage_form')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="strength" class="col-sm-3 col-form-label text-end">Strength</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('strength') is-invalid @enderror"
                                        id="strength" name="strength"
                                        value="{{ old('strength') }}"
                                        placeholder="e.g., 500mg, 10ml">
                                    @error('strength')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="pack_size" class="col-sm-3 col-form-label text-end">Pack Size</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('pack_size') is-invalid @enderror"
                                        id="pack_size" name="pack_size"
                                        value="{{ old('pack_size') }}"
                                        placeholder="e.g., 10's, 30ml">
                                    @error('pack_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="mrp" class="col-sm-3 col-form-label text-end">MRP</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('mrp') is-invalid @enderror"
                                        id="mrp" name="mrp"
                                        value="{{ old('mrp') }}"
                                        placeholder="0.00">
                                    @error('mrp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="image" class="col-sm-3 col-form-label text-end">Product Image</label>
                                <div class="col-sm-9">
                                    <input type="file"
                                        class="form-control @error('image') is-invalid @enderror"
                                        id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Upload product packaging photo (JPEG, PNG, WebP, max 2MB).</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="description" class="col-sm-3 col-form-label text-end">Description</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3"
                                        placeholder="Optional product description">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-sm-9 ms-auto">
                                    <button type="submit" class="btn btn-primary px-4" style="background-color: #CD0B6F; border-color: #CD0B6F;">
                                        <i class="las la-save me-1"></i> Create Product
                                    </button>
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary ms-2">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
