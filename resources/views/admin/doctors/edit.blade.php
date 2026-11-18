@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Edit Doctor</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.doctors.index') }}">Doctors</a></li>
                            <li class="breadcrumb-item active">Edit Doctor</li>
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
                                <h4 class="card-title">Doctor Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <label for="registration_no" class="col-sm-3 col-form-label text-end">Registration No. <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('registration_no') is-invalid @enderror"
                                        id="registration_no" name="registration_no"
                                        value="{{ old('registration_no', $doctor->registration_no) }}"
                                        placeholder="Enter registration number" required>
                                    @error('registration_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="specialty" class="col-sm-3 col-form-label text-end">Specialty</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('specialty') is-invalid @enderror"
                                        id="specialty" name="specialty"
                                        value="{{ old('specialty', $doctor->specialty) }}"
                                        placeholder="e.g., Cardiologist, Dermatologist">
                                    @error('specialty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="hospital_clinic" class="col-sm-3 col-form-label text-end">Hospital / Clinic</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('hospital_clinic') is-invalid @enderror"
                                        id="hospital_clinic" name="hospital_clinic"
                                        value="{{ old('hospital_clinic', $doctor->hospital_clinic) }}"
                                        placeholder="Enter hospital or clinic name">
                                    @error('hospital_clinic')
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
                                        <option value="active" @selected(old('status', $doctor->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $doctor->status) === 'inactive')>Inactive</option>
                                        <option value="suspended" @selected(old('status', $doctor->status) === 'suspended')>Suspended</option>
                                        <option value="pending" @selected(old('status', $doctor->status) === 'pending')>Pending</option>
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
                                        <i class="las la-save me-1"></i> Update Doctor
                                    </button>
                                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary ms-2">
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
                                <h6 class="fw-semibold mb-1">Delete this doctor</h6>
                                <p class="text-muted mb-0 small">Once deleted, all doctor data will be permanently removed.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}"
                                onsubmit="return confirm('Are you sure you want to delete doctor #{{ $doctor->id }}? This action cannot be undone.')">
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
