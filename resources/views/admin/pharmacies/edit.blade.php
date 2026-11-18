@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Edit Pharmacy</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pharmacies.index') }}">Pharmacies</a></li>
                            <li class="breadcrumb-item active">Edit Pharmacy</li>
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
                                <h4 class="card-title">Pharmacy Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.pharmacies.update', $pharmacy) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <label for="store_name" class="col-sm-3 col-form-label text-end">Store Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('store_name') is-invalid @enderror"
                                        id="store_name" name="store_name"
                                        value="{{ old('store_name', $pharmacy->store_name) }}"
                                        placeholder="Enter store name" required>
                                    @error('store_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="license_no" class="col-sm-3 col-form-label text-end">License No. <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('license_no') is-invalid @enderror"
                                        id="license_no" name="license_no"
                                        value="{{ old('license_no', $pharmacy->license_no) }}"
                                        placeholder="Enter license number" required>
                                    @error('license_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="owner_name" class="col-sm-3 col-form-label text-end">Owner Name</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('owner_name') is-invalid @enderror"
                                        id="owner_name" name="owner_name"
                                        value="{{ old('owner_name', $pharmacy->owner_name) }}"
                                        placeholder="Enter owner name">
                                    @error('owner_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="pan_vat_no" class="col-sm-3 col-form-label text-end">PAN/VAT No.</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('pan_vat_no') is-invalid @enderror"
                                        id="pan_vat_no" name="pan_vat_no"
                                        value="{{ old('pan_vat_no', $pharmacy->pan_vat_no) }}"
                                        placeholder="Enter PAN or VAT number">
                                    @error('pan_vat_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="district" class="col-sm-3 col-form-label text-end">District</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('district') is-invalid @enderror"
                                        id="district" name="district"
                                        value="{{ old('district', $pharmacy->district) }}"
                                        placeholder="Enter district">
                                    @error('district')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="address" class="col-sm-3 col-form-label text-end">Address</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                        id="address" name="address"
                                        rows="3"
                                        placeholder="Enter pharmacy address">{{ old('address', $pharmacy->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="status" class="col-sm-3 col-form-label text-end">Status <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" @selected(old('status', $pharmacy->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $pharmacy->status) === 'inactive')>Inactive</option>
                                        <option value="suspended" @selected(old('status', $pharmacy->status) === 'suspended')>Suspended</option>
                                        <option value="pending" @selected(old('status', $pharmacy->status) === 'pending')>Pending</option>
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
                                        <i class="las la-save me-1"></i> Update Pharmacy
                                    </button>
                                    <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-outline-secondary ms-2">
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
                                <h6 class="fw-semibold mb-1">Delete this pharmacy</h6>
                                <p class="text-muted mb-0 small">Once deleted, all pharmacy data will be permanently removed.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.pharmacies.destroy', $pharmacy) }}"
                                onsubmit="return confirm('Are you sure you want to delete {{ $pharmacy->store_name }}? This action cannot be undone.')">
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
