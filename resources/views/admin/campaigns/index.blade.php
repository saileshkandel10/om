@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Campaigns</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Campaigns</a></li>
                            <li class="breadcrumb-item active">Campaigns</li>
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
                                <h4 class="card-title">All Campaigns</h4>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="las la-file-export me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="{{ route('admin.campaigns.export', ['format' => 'xlsx']) }}"><i class="las la-file-excel text-success me-2"></i> Excel (.xlsx)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.campaigns.export', ['format' => 'csv']) }}"><i class="las la-file-csv text-primary me-2"></i> CSV (.csv)</a></li>
                                    </ul>
                                </div>
                                <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Campaign</a>
                                <span class="text-muted small ms-1 align-self-center">{{ $campaigns->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.campaigns.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="promotion" @selected(request('type') === 'promotion')>Promotion</option>
                                    <option value="awareness" @selected(request('type') === 'awareness')>Awareness</option>
                                    <option value="product" @selected(request('type') === 'product')>Product</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                    <option value="active" @selected(request('status') === 'active')>Active</option>
                                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.campaigns.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Target Segment</th>
                                        <th>Recipients</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($campaigns as $campaign)
                                        <tr>
                                            <td><span class="fw-medium">{{ $campaign->name }}</span></td>
                                            <td><span class="badge bg-info">{{ ucfirst($campaign->campaign_type) }}</span></td>
                                            <td>{{ $campaign->target_segment ?? '—' }}</td>
                                            <td><span class="badge bg-primary">{{ $campaign->recipients_count }}</span></td>
                                            <td>{{ $campaign->start_date?->format('M d, Y') ?? '—' }}</td>
                                            <td>{{ $campaign->end_date?->format('M d, Y') ?? '—' }}</td>
                                            <td>
                                                @php($statusColor = match ($campaign->status) {
                                                    'active' => 'success',
                                                    'draft' => 'secondary',
                                                    'completed' => 'info',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $campaign->status }}</span>
                                            </td>
                                            <td data-sort="{{ $campaign->created_at->format('Y/m/d') }}">{{ $campaign->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.campaigns.show', $campaign) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('admin.campaigns.edit', $campaign) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this campaign?')">
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
                                            <td colspan="9" class="text-center py-4 text-muted">No campaigns found.</td>
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

