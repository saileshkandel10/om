@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Uploaded Files</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active">Uploaded Files</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Uploaded Files</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $files->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.uploaded-files.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search file name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="module" class="form-select form-select-sm">
                                    <option value="">All Modules</option>
                                    <option value="purchase_bill" @selected(request('module') === 'purchase_bill')>Purchase Bill</option>
                                    <option value="prescription" @selected(request('module') === 'prescription')>Prescription</option>
                                    <option value="profile" @selected(request('module') === 'profile')>Profile</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="image" @selected(request('type') === 'image')>Image</option>
                                    <option value="document" @selected(request('type') === 'document')>Document</option>
                                    <option value="pdf" @selected(request('type') === 'pdf')>PDF</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.uploaded-files.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>File Name</th>
                                        <th>Uploaded By</th>
                                        <th>Module</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>MIME</th>
                                        <th>Uploaded</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($files as $file)
                                        <tr>
                                            <td class="text-truncate" style="max-width:200px">
                                                <span class="fw-medium">{{ $file->original_name }}</span>
                                            </td>
                                            <td>{{ $file->user?->name ?? '—' }}</td>
                                            <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $file->module_name ?? '—') }}</span></td>
                                            <td><span class="badge bg-info">{{ $file->file_type ?? '—' }}</span></td>
                                            <td>{{ $file->file_size ? number_format($file->file_size / 1024, 1) . ' KB' : '—' }}</td>
                                            <td><code>{{ $file->mime_type ?? '—' }}</code></td>
                                            <td data-sort="{{ $file->uploaded_at?->format('Y/m/d') ?? $file->created_at->format('Y/m/d') }}">{{ $file->uploaded_at?->format('M d, Y') ?? $file->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.uploaded-files.show', $file) }}">View</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.uploaded-files.destroy', $file) }}" onsubmit="return confirm('Delete this file permanently?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No files found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

