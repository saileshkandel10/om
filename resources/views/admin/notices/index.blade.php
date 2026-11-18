@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Notices</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Campaigns</a></li>
                            <li class="breadcrumb-item active">Notices</li>
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
                                <h4 class="card-title">All Notices</h4>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="las la-file-export me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="{{ route('admin.notices.export', ['format' => 'xlsx']) }}"><i class="las la-file-excel text-success me-2"></i> Excel (.xlsx)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.notices.export', ['format' => 'csv']) }}"><i class="las la-file-csv text-primary me-2"></i> CSV (.csv)</a></li>
                                    </ul>
                                </div>
                                <a href="{{ route('admin.notices.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Notice</a>
                                <span class="text-muted small ms-1 align-self-center">{{ $notices->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.notices.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="agm" @selected(request('type') === 'agm')>AGM</option>
                                    <option value="financial" @selected(request('type') === 'financial')>Financial</option>
                                    <option value="announcement" @selected(request('type') === 'announcement')>Announcement</option>
                                    <option value="general" @selected(request('type') === 'general')>General</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="audience" class="form-select form-select-sm">
                                    <option value="">All Audiences</option>
                                    <option value="shareholder" @selected(request('audience') === 'shareholder')>Shareholder</option>
                                    <option value="pharmacy" @selected(request('audience') === 'pharmacy')>Pharmacy</option>
                                    <option value="doctor" @selected(request('audience') === 'doctor')>Doctor</option>
                                    <option value="all" @selected(request('audience') === 'all')>All</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                    <option value="published" @selected(request('status') === 'published')>Published</option>
                                    <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.notices.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Audience</th>
                                        <th>Status</th>
                                        <th>Published</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($notices as $notice)
                                        <tr>
                                            <td class="text-truncate" style="max-width:250px"><span class="fw-medium">{{ $notice->title }}</span></td>
                                            <td><span class="badge bg-info">{{ ucfirst($notice->notice_type) }}</span></td>
                                            <td><span class="badge bg-secondary">{{ ucfirst($notice->audience_type) }}</span></td>
                                            <td>
                                                @php($statusColor = match ($notice->status) {
                                                    'published' => 'success',
                                                    'draft' => 'warning',
                                                    'archived' => 'secondary',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $notice->status }}</span>
                                            </td>
                                            <td>{{ $notice->published_at?->format('M d, Y') ?? '—' }}</td>
                                            <td data-sort="{{ $notice->created_at->format('Y/m/d') }}">{{ $notice->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.notices.show', $notice) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('admin.notices.edit', $notice) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm('Delete this notice?')">
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
                                            <td colspan="7" class="text-center py-4 text-muted">No notices found.</td>
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

