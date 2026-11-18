@extends('layouts.shareholder')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Upload Purchase Bill</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('shareholder.dashboard') }}">{{ config('system.name') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shareholder.bills.index') }}">Purchase Bills</a></li>
                        <li class="breadcrumb-item active">Upload Bill</li>
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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Bill Information</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form method="POST" action="{{ route('shareholder.bills.store') }}" class="mt-3" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3 row">
                            <label for="pharmacy_id" class="col-sm-3 col-form-label text-end">Pharmacy</label>
                            <div class="col-sm-9">
                                <select class="form-select @error('pharmacy_id') is-invalid @enderror"
                                    id="pharmacy_id" name="pharmacy_id">
                                    <option value="">Select Pharmacy (optional)</option>
                                    @foreach ($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" @selected(old('pharmacy_id') == $pharmacy->id)>
                                            {{ $pharmacy->store_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pharmacy_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="invoice_number" class="col-sm-3 col-form-label text-end">Invoice Number</label>
                            <div class="col-sm-9">
                                <input type="text"
                                    class="form-control @error('invoice_number') is-invalid @enderror"
                                    id="invoice_number" name="invoice_number"
                                    value="{{ old('invoice_number') }}"
                                    placeholder="Enter invoice or receipt number">
                                @error('invoice_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="invoice_date" class="col-sm-3 col-form-label text-end">Invoice Date</label>
                            <div class="col-sm-9">
                                <input type="date"
                                    class="form-control @error('invoice_date') is-invalid @enderror"
                                    id="invoice_date" name="invoice_date"
                                    value="{{ old('invoice_date', date('Y-m-d')) }}">
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="total_amount" class="col-sm-3 col-form-label text-end">Total Amount (Rs)</label>
                            <div class="col-sm-9">
                                <input type="number" step="0.01" min="0"
                                    class="form-control @error('total_amount') is-invalid @enderror"
                                    id="total_amount" name="total_amount"
                                    value="{{ old('total_amount') }}"
                                    placeholder="0.00">
                                @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="receipt" class="col-sm-3 col-form-label text-end">Receipt Image</label>
                            <div class="col-sm-9">
                                <input type="file"
                                    class="form-control @error('receipt') is-invalid @enderror"
                                    id="receipt" name="receipt"
                                    accept="image/jpeg,image/png,application/pdf">
                                @error('receipt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Upload a photo or scan of your bill (JPG, PNG, PDF. Max 5MB).</small>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm-9 ms-auto">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="las la-upload me-1"></i> Submit Bill
                                </button>
                                <a href="{{ route('shareholder.bills.index') }}" class="btn btn-outline-secondary ms-2">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Instructions</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start py-2">
                            <i class="las la-check-circle text-success fs-5 me-2 mt-1"></i>
                            <span>Enter the invoice/receipt number exactly as shown on your bill.</span>
                        </li>
                        <li class="d-flex align-items-start py-2">
                            <i class="las la-check-circle text-success fs-5 me-2 mt-1"></i>
                            <span>Select the pharmacy where the purchase was made.</span>
                        </li>
                        <li class="d-flex align-items-start py-2">
                            <i class="las la-check-circle text-success fs-5 me-2 mt-1"></i>
                            <span>Enter the total amount from your bill (in NPR).</span>
                        </li>
                        <li class="d-flex align-items-start py-2">
                            <i class="las la-check-circle text-success fs-5 me-2 mt-1"></i>
                            <span>Your bill will be processed automatically to calculate rewards.</span>
                        </li>
                        <li class="d-flex align-items-start py-2">
                            <i class="las la-info-circle text-info fs-5 me-2 mt-1"></i>
                            <span>Processing typically takes a few minutes. You'll be notified when complete.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
