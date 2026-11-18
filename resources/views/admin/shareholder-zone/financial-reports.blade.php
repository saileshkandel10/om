@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Financial Reports</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Shareholder Zone</a></li>
                            <li class="breadcrumb-item active">Financial Reports</li>
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
                                <h4 class="card-title">Financial Reports</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.notices.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Report</a>
                                <span class="text-muted small ms-2">{{ $notices->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
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
                                            <td class="text-truncate" style="max-width:300px"><span class="fw-medium">{{ $notice->title }}</span></td>
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
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No financial reports found.</td>
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

