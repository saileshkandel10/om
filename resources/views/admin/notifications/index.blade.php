@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Notifications</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Campaigns</a></li>
                            <li class="breadcrumb-item active">Notifications</li>
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
                                <h4 class="card-title">All Notifications</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $notifications->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.notifications.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="channel" class="form-select form-select-sm">
                                    <option value="">All Channels</option>
                                    <option value="email" @selected(request('channel') === 'email')>Email</option>
                                    <option value="sms" @selected(request('channel') === 'sms')>SMS</option>
                                    <option value="push" @selected(request('channel') === 'push')>Push</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>User</th>
                                        <th>Channel</th>
                                        <th>Status</th>
                                        <th>Read At</th>
                                        <th>Sent</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($notifications as $notification)
                                        <tr>
                                            <td class="text-truncate" style="max-width:250px"><span class="fw-medium">{{ $notification->title }}</span></td>
                                            <td>{{ $notification->user?->name ?? '—' }}</td>
                                            <td><span class="badge bg-secondary">{{ strtoupper($notification->channel) }}</span></td>
                                            <td>
                                                @php($statusColor = match ($notification->status) {
                                                    'sent' => 'success',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $notification->status }}</span>
                                            </td>
                                            <td>{{ $notification->read_at?->format('M d, Y H:i') ?? '—' }}</td>
                                            <td data-sort="{{ $notification->created_at->format('Y/m/d') }}">{{ $notification->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.notifications.show', $notification) }}">View</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Delete this notification?')">
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
                                            <td colspan="7" class="text-center py-4 text-muted">No notifications found.</td>
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

