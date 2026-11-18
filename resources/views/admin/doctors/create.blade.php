@extends('layouts.admin')

@section('title', 'Add New Doctor')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center mb-3">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-user-plus text-brand me-1"></i> Register New Doctor
                    </h4>
                    <p class="text-muted small mb-0">
                        User Management &gt; <a href="{{ route('admin.doctors.index') }}" class="text-brand text-decoration-underline">Doctors</a> &gt; Register Doctor
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Doctors Directory
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i> Please review and correct the errors below:
            <ul class="mb-0 mt-1 small ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-user-md text-brand me-1"></i> Doctor Account &amp; Practice Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.doctors.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Doctor Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Ramesh Karki" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NMC / Medical Council Reg. No <span class="text-danger">*</span></label>
                                <input type="text" name="registration_no" class="form-control" placeholder="e.g. 5410/NMC" value="{{ old('registration_no') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Medical Specialty / Department</label>
                                <input type="text" name="specialty" class="form-control" placeholder="e.g. Pediatrics, Cardiology, General Physician" value="{{ old('specialty') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hospital / Clinic Practice Location</label>
                                <input type="text" name="hospital_clinic" class="form-control" placeholder="e.g. Bir Hospital, Kathmandu Clinic" value="{{ old('hospital_clinic') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control" placeholder="e.g. 9841000000" value="{{ old('mobile') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address (Optional)</label>
                                <input type="email" name="email" class="form-control" placeholder="e.g. doctor@hospital.com" value="{{ old('email') }}">
                                <small class="text-muted">If omitted, an auto-generated portal email will be assigned.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="active" @selected(old('status', 'active') === 'active')>Active / Approved</option>
                                    <option value="pending" @selected(old('status') === 'pending')>Pending Verification</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.doctors.index') }}" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-brand px-4 py-2 fw-semibold">
                                    <i class="las la-save me-1"></i> Register Doctor
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
