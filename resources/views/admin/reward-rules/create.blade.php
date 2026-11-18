@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Create Reward Rule</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reward-rules.index') }}">Reward Rules</a></li>
                            <li class="breadcrumb-item active">Create Rule</li>
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
                                <h4 class="card-title">Rule Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.reward-rules.store') }}" class="mt-3">
                            @csrf

                            <div class="mb-3 row">
                                <label for="name" class="col-sm-3 col-form-label text-end">Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Enter rule name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="user_type" class="col-sm-3 col-form-label text-end">User Type <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('user_type') is-invalid @enderror"
                                        id="user_type" name="user_type" required>
                                        <option value="">Select Type</option>
                                        <option value="shareholder" @selected(old('user_type') === 'shareholder')>Shareholder</option>
                                        <option value="pharmacy" @selected(old('user_type') === 'pharmacy')>Pharmacy</option>
                                        <option value="doctor" @selected(old('user_type') === 'doctor')>Doctor</option>
                                    </select>
                                    @error('user_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="basis_type" class="col-sm-3 col-form-label text-end">Basis Type <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('basis_type') is-invalid @enderror"
                                        id="basis_type" name="basis_type" required>
                                        <option value="">Select Basis</option>
                                        <option value="qty" @selected(old('basis_type') === 'qty')>Quantity</option>
                                        <option value="amount" @selected(old('basis_type') === 'amount')>Amount</option>
                                        <option value="hybrid" @selected(old('basis_type') === 'hybrid')>Hybrid</option>
                                    </select>
                                    @error('basis_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="product_id" class="col-sm-3 col-form-label text-end">Product</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('product_id') is-invalid @enderror"
                                        id="product_id" name="product_id">
                                        <option value="">All Products</option>
                                        @foreach ($products as $id => $name)
                                            <option value="{{ $id }}" @selected(old('product_id') == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="illness_category_id" class="col-sm-3 col-form-label text-end">Illness Category</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('illness_category_id') is-invalid @enderror"
                                        id="illness_category_id" name="illness_category_id">
                                        <option value="">All</option>
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
                                <label for="min_qty" class="col-sm-3 col-form-label text-end">Min Quantity</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('min_qty') is-invalid @enderror"
                                        id="min_qty" name="min_qty"
                                        value="{{ old('min_qty') }}"
                                        placeholder="0">
                                    @error('min_qty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="min_amount" class="col-sm-3 col-form-label text-end">Min Amount</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('min_amount') is-invalid @enderror"
                                        id="min_amount" name="min_amount"
                                        value="{{ old('min_amount') }}"
                                        placeholder="0.00">
                                    @error('min_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="point_rate" class="col-sm-3 col-form-label text-end">Point Rate <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('point_rate') is-invalid @enderror"
                                        id="point_rate" name="point_rate"
                                        value="{{ old('point_rate') }}"
                                        placeholder="e.g., 1.00" required>
                                    @error('point_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="bonus_type" class="col-sm-3 col-form-label text-end">Bonus Type</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('bonus_type') is-invalid @enderror"
                                        id="bonus_type" name="bonus_type">
                                        <option value="">No Bonus</option>
                                        <option value="percentage" @selected(old('bonus_type') === 'percentage')>Percentage</option>
                                        <option value="fixed" @selected(old('bonus_type') === 'fixed')>Fixed</option>
                                    </select>
                                    @error('bonus_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="bonus_value" class="col-sm-3 col-form-label text-end">Bonus Value</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('bonus_value') is-invalid @enderror"
                                        id="bonus_value" name="bonus_value"
                                        value="{{ old('bonus_value') }}"
                                        placeholder="e.g., 10">
                                    @error('bonus_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="start_date" class="col-sm-3 col-form-label text-end">Start Date</label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('start_date') is-invalid @enderror"
                                        id="start_date" name="start_date"
                                        value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="end_date" class="col-sm-3 col-form-label text-end">End Date</label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('end_date') is-invalid @enderror"
                                        id="end_date" name="end_date"
                                        value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="is_active" class="col-sm-3 col-form-label text-end">Active</label>
                                <div class="col-sm-9">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox"
                                            id="is_active" name="is_active" value="1"
                                            @checked(old('is_active', true))>
                                        <label class="form-check-label" for="is_active">Enable this rule</label>
                                    </div>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-sm-9 ms-auto">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="las la-save me-1"></i> Create Rule
                                    </button>
                                    <a href="{{ route('admin.reward-rules.index') }}" class="btn btn-outline-secondary ms-2">
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
