@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Edit Sourcing Record</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.sourcing-records.index') }}">Sourcing Records</a></li>
                            <li class="breadcrumb-item active">Edit Record</li>
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
                                <h4 class="card-title">Record Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.sourcing-records.update', $sourcingRecord) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <label for="pharmacy_id" class="col-sm-3 col-form-label text-end">Pharmacy <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('pharmacy_id') is-invalid @enderror"
                                        id="pharmacy_id" name="pharmacy_id" required>
                                        <option value="">Select Pharmacy</option>
                                        @foreach ($pharmacies as $id => $name)
                                            <option value="{{ $id }}" @selected(old('pharmacy_id', $sourcingRecord->pharmacy_id) == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('pharmacy_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="supplier_name" class="col-sm-3 col-form-label text-end">Supplier Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('supplier_name') is-invalid @enderror"
                                        id="supplier_name" name="supplier_name"
                                        value="{{ old('supplier_name', $sourcingRecord->supplier_name) }}"
                                        placeholder="Enter supplier name" required>
                                    @error('supplier_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="source_invoice_no" class="col-sm-3 col-form-label text-end">Source Invoice No.</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('source_invoice_no') is-invalid @enderror"
                                        id="source_invoice_no" name="source_invoice_no"
                                        value="{{ old('source_invoice_no', $sourcingRecord->source_invoice_no) }}"
                                        placeholder="Enter source invoice number">
                                    @error('source_invoice_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="source_date" class="col-sm-3 col-form-label text-end">Source Date</label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('source_date') is-invalid @enderror"
                                        id="source_date" name="source_date"
                                        value="{{ old('source_date', $sourcingRecord->source_date?->format('Y-m-d')) }}">
                                    @error('source_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="notes" class="col-sm-3 col-form-label text-end">Notes</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                        id="notes" name="notes"
                                        rows="3"
                                        placeholder="Enter any notes (optional)">{{ old('notes', $sourcingRecord->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-sm-9 ms-auto">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="las la-save me-1"></i> Update Record
                                    </button>
                                    <a href="{{ route('admin.sourcing-records.index') }}" class="btn btn-outline-secondary ms-2">
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
                                <h6 class="fw-semibold mb-1">Delete this record</h6>
                                <p class="text-muted mb-0 small">Once deleted, the sourcing record will be permanently removed.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.sourcing-records.destroy', $sourcingRecord) }}"
                                onsubmit="return confirm('Are you sure you want to delete this sourcing record? This action cannot be undone.')">
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
