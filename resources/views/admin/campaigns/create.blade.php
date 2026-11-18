@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Create Campaign</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.campaigns.index') }}">Campaigns</a></li>
                            <li class="breadcrumb-item active">Create Campaign</li>
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
                                <h4 class="card-title">Campaign Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.campaigns.store') }}" class="mt-3">
                            @csrf

                            <div class="mb-3 row">
                                <label for="name" class="col-sm-3 col-form-label text-end">Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Enter campaign name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="campaign_type" class="col-sm-3 col-form-label text-end">Campaign Type <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('campaign_type') is-invalid @enderror"
                                        id="campaign_type" name="campaign_type" required>
                                        <option value="">Select Type</option>
                                        <option value="promotion" @selected(old('campaign_type') === 'promotion')>Promotion</option>
                                        <option value="awareness" @selected(old('campaign_type') === 'awareness')>Awareness</option>
                                        <option value="product" @selected(old('campaign_type') === 'product')>Product</option>
                                    </select>
                                    @error('campaign_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="target_segment" class="col-sm-3 col-form-label text-end">Target Segment</label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('target_segment') is-invalid @enderror"
                                        id="target_segment" name="target_segment"
                                        value="{{ old('target_segment') }}"
                                        placeholder="e.g., Shareholders, Pharmacies, Doctors">
                                    @error('target_segment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="description" class="col-sm-3 col-form-label text-end">Description</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description"
                                        rows="4"
                                        placeholder="Enter campaign description">{{ old('description') }}</textarea>
                                    @error('description')
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
                                <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                        <option value="active" @selected(old('status') === 'active')>Active</option>
                                        <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                                        <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
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
                                        <i class="las la-save me-1"></i> Create Campaign
                                    </button>
                                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline-secondary ms-2">
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

