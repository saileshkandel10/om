@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">File Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.uploaded-files.index') }}">Uploaded Files</a></li>
                        <li class="breadcrumb-item active">{{ $uploadedFile->original_name ?? 'File #'.$uploadedFile->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-file fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold text-truncate">{{ $uploadedFile->original_name ?? 'N/A' }}</h5>
                        @if ($uploadedFile->file_type)
                            <span class="badge bg-info-subtle text-info">{{ strtoupper($uploadedFile->file_type) }}</span>
                        @endif
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Module</span>
                            <span class="fw-medium">{{ $uploadedFile->module_name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">File Type</span>
                            <span>{{ $uploadedFile->file_type ?? '—' }}</span>
                        </div>
                        @if ($uploadedFile->file_size)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">File Size</span>
                                <span>{{ number_format($uploadedFile->file_size / 1024, 1) }} KB</span>
                            </div>
                        @endif
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Uploaded By</span>
                            <span class="fw-medium">{{ $uploadedFile->user?->name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span>{{ $uploadedFile->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <form method="POST" action="{{ route('admin.uploaded-files.destroy', $uploadedFile) }}" class="d-inline" onsubmit="return confirm('Delete this file permanently?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100"><i class="las la-trash-alt me-1"></i> Delete File</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-file fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $uploadedFile->original_name ?? 'File' }}</span>
                <span class="text-primary fw-normal">metadata</span>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">File Information</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted" style="width: 150px;">Original Name</th>
                                    <td>{{ $uploadedFile->original_name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Storage Path</th>
                                    <td><code>{{ $uploadedFile->file_path ?? '—' }}</code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Module</th>
                                    <td>{{ $uploadedFile->module_name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">MIME Type</th>
                                    <td>{{ $uploadedFile->mime_type ?? $uploadedFile->file_type ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">File Size</th>
                                    <td>{{ $uploadedFile->file_size ? number_format($uploadedFile->file_size / 1024, 2) . ' KB' : '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Uploaded By</th>
                                    <td>{{ $uploadedFile->user?->name ?? '—' }} ({{ $uploadedFile->user?->email ?? '—' }})</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Created At</th>
                                    <td>{{ $uploadedFile->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Last Updated</th>
                                    <td>{{ $uploadedFile->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

