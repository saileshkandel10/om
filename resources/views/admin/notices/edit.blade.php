@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Edit Notice</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.notices.index') }}">Notices</a></li>
                            <li class="breadcrumb-item active">Edit Notice</li>
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
                                <h4 class="card-title">Notice Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.notices.update', $notice) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <label for="title" class="col-sm-3 col-form-label text-end">Title <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title"
                                        value="{{ old('title', $notice->title) }}"
                                        placeholder="Enter notice title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="notice_type" class="col-sm-3 col-form-label text-end">Notice Type <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('notice_type') is-invalid @enderror"
                                        id="notice_type" name="notice_type" required>
                                        <option value="">Select Type</option>
                                        <option value="agm" @selected(old('notice_type', $notice->notice_type) === 'agm')>AGM</option>
                                        <option value="financial" @selected(old('notice_type', $notice->notice_type) === 'financial')>Financial</option>
                                        <option value="announcement" @selected(old('notice_type', $notice->notice_type) === 'announcement')>Announcement</option>
                                        <option value="general" @selected(old('notice_type', $notice->notice_type) === 'general')>General</option>
                                    </select>
                                    @error('notice_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="audience_type" class="col-sm-3 col-form-label text-end">Audience <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('audience_type') is-invalid @enderror"
                                        id="audience_type" name="audience_type" required>
                                        <option value="">Select Audience</option>
                                         <option value="shareholder" @selected(old('audience_type', $notice->audience_type) === 'shareholder')>Shareholder</option>
                                         <option value="pharmacy" @selected(old('audience_type', $notice->audience_type) === 'pharmacy')>Pharmacy</option>
                                         <option value="doctor" @selected(old('audience_type', $notice->audience_type) === 'doctor')>Doctor</option>
                                         <option value="customer" @selected(old('audience_type', $notice->audience_type) === 'customer')>Patient</option>
                                         <option value="all" @selected(old('audience_type', $notice->audience_type) === 'all')>All</option>
                                    </select>
                                    @error('audience_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="body" class="col-sm-3 col-form-label text-end">Body <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('body') is-invalid @enderror"
                                        id="body" name="body"
                                        rows="8"
                                        placeholder="Enter notice content..." required>{{ old('body', $notice->body) }}</textarea>
                                    @error('body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="published_at" class="col-sm-3 col-form-label text-end">Publish Date</label>
                                <div class="col-sm-9">
                                    <input type="date"
                                        class="form-control @error('published_at') is-invalid @enderror"
                                        id="published_at" name="published_at"
                                        value="{{ old('published_at', $notice->published_at?->format('Y-m-d')) }}">
                                    @error('published_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                        <option value="draft" @selected(old('status', $notice->status) === 'draft')>Draft</option>
                                        <option value="published" @selected(old('status', $notice->status) === 'published')>Published</option>
                                        <option value="archived" @selected(old('status', $notice->status) === 'archived')>Archived</option>
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
                                        <i class="las la-save me-1"></i> Update Notice
                                    </button>
                                    <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary ms-2">
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
                                <h6 class="fw-semibold mb-1">Delete this notice</h6>
                                <p class="text-muted mb-0 small">Once deleted, the notice will be permanently removed.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}"
                                onsubmit="return confirm('Are you sure you want to delete {{ $notice->title }}? This action cannot be undone.')">
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
