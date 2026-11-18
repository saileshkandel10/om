@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Users</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">OmConnect</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">Admin</a>
                            </li>
                            <li class="breadcrumb-item active">Users</li>
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
                                <h4 class="card-title">All Users</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary" style="background-color: #CD0B6F; border-color: #CD0B6F;">
                                    <i class="iconoir-plus me-1"></i> Add New User
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search name or email..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="role" class="form-select form-select-sm">
                                    <option value="">All Roles</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Roles</th>
                                        <th>Bills Uploaded</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($user->avatar)
                                                        <img src="{{ $user->avatar }}" alt="" class="rounded-circle" width="32" height="32">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px">{{ $user->initials() }}</div>
                                                    @endif
                                                    <div>
                                                        <span class="fw-medium">{{ $user->name }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->mobile ?? '—' }}</td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span class="badge bg-{{ match($role->name) { 'Admin' => 'danger', 'Staff' => 'primary', 'Shareholder' => 'secondary', 'Pharmacy' => 'success', 'Doctor' => 'info', 'Customer' => 'warning', default => 'secondary' } }}">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @php
                                                    $billCount = $user->purchase_bills_count ?? $user->purchaseBills()->count();
                                                @endphp
                                                <a href="{{ route('admin.purchase-bills.index', ['search' => $user->name]) }}" class="badge {{ $billCount > 0 ? 'bg-primary-subtle text-primary' : 'bg-light text-muted' }} fs-12 px-2 py-1">
                                                    <i class="las la-receipt me-1"></i>{{ $billCount }} {{ Str::plural('Bill', $billCount) }}
                                                </a>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColor = match ($user->status) {
                                                        'active' => 'success',
                                                        'inactive' => 'warning',
                                                        'suspended' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">{{ $user->status ?? 'pending' }}</span>
                                            </td>
                                            <td data-sort="{{ $user->created_at->format('Y/m/d') }}">{{ $user->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                @if ($user->id !== auth()->id())
                                                    <div class="dropdown d-inline-block">
                                                        <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                                            <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="{{ route('admin.users.show', $user) }}">View</a>
                                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                                            <div class="dropdown-divider"></div>
                                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

