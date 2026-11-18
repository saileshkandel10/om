@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Audit Logs</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active">Audit Logs</li>
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
                                <h4 class="card-title">All Audit Logs</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $logs->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="module" class="form-select form-select-sm">
                                    <option value="">All Modules</option>
                                    <option value="users" @selected(request('module') === 'users')>Users</option>
                                    <option value="products" @selected(request('module') === 'products')>Products</option>
                                    <option value="purchase_bills" @selected(request('module') === 'purchase_bills')>Purchase Bills</option>
                                    <option value="prescriptions" @selected(request('module') === 'prescriptions')>Prescriptions</option>
                                    <option value="rewards" @selected(request('module') === 'rewards')>Rewards</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="action" class="form-select form-select-sm">
                                    <option value="">All Actions</option>
                                    <option value="created" @selected(request('action') === 'created')>Created</option>
                                    <option value="updated" @selected(request('action') === 'updated')>Updated</option>
                                    <option value="deleted" @selected(request('action') === 'deleted')>Deleted</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From date">
                            </div>
                            <div class="col-auto">
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To date">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Entity</th>
                                        <th>Entity ID</th>
                                        <th>IP Address</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td><code>#{{ $log->id }}</code></td>
                                            <td>{{ $log->user?->name ?? 'System' }}</td>
                                            <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $log->module_name ?? '—') }}</span></td>
                                            <td>
                                                @php($actionColor = match ($log->action_name) {
                                                    'created' => 'success',
                                                    'updated' => 'info',
                                                    'deleted' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $actionColor }}">{{ $log->action_name }}</span>
                                            </td>
                                            <td class="text-truncate" style="max-width:150px">{{ $log->entity_name ?? '—' }}</td>
                                            <td><code>{{ $log->entity_id ?? '—' }}</code></td>
                                            <td><code>{{ $log->ip_address ?? '—' }}</code></td>
                                            <td data-sort="{{ $log->created_at->format('Y/m/d H:i') }}">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.audit-logs.show', $log) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">No audit logs found.</td>
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

