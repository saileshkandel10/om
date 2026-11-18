@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Pharmacies</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">User Management</a></li>
                            <li class="breadcrumb-item active">Pharmacies</li>
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
                                <h4 class="card-title">All Pharmacies</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $pharmacies->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.pharmacies.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search name, license or address..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="active" @selected(request('status') === 'active')>Active</option>
                                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pharmacy</th>
                                        <th>Owner</th>
                                        <th>License No.</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pharmacies as $pharmacy)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px">{{ substr($pharmacy->store_name, 0, 2) }}</div>
                                                    <div>
                                                        <span class="fw-medium">{{ $pharmacy->store_name }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $pharmacy->user?->name ?? '—' }}</td>
                                            <td><code>{{ $pharmacy->license_no }}</code></td>
                                            <td class="text-truncate" style="max-width:200px">{{ $pharmacy->address ?? '—' }}</td>
                                            <td>
                                                @php($statusColor = match ($pharmacy->status) {
                                                    'active' => 'success',
                                                    'inactive' => 'warning',
                                                    'suspended' => 'danger',
                                                    'pending' => 'secondary',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $pharmacy->status }}</span>
                                            </td>
                                            <td data-sort="{{ $pharmacy->created_at->format('Y/m/d') }}">{{ $pharmacy->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.pharmacies.show', $pharmacy) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('admin.pharmacies.edit', $pharmacy) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" onsubmit="return confirm('Delete this pharmacy?')">
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
                                            <td colspan="7" class="text-center py-4 text-muted">No pharmacies found.</td>
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

