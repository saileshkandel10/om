@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Reward Rules</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Rewards & Loyalty</a></li>
                            <li class="breadcrumb-item active">Reward Rules</li>
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
                                <h4 class="card-title">All Reward Rules</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.reward-rules.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Rule</a>
                                <span class="text-muted small ms-2">{{ $rules->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.reward-rules.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="user_type" class="form-select form-select-sm">
                                    <option value="">All User Types</option>
                                    <option value="shareholder" @selected(request('user_type') === 'shareholder')>Shareholder</option>
                                    <option value="pharmacy" @selected(request('user_type') === 'pharmacy')>Pharmacy</option>
                                    <option value="doctor" @selected(request('user_type') === 'doctor')>Doctor</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" @checked(request('is_active')) onchange="this.form.submit()">
                                    <label class="form-check-label" for="isActive">Active Only</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.reward-rules.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>User Type</th>
                                        <th>Basis</th>
                                        <th>Product</th>
                                        <th>Point Rate</th>
                                        <th>Bonus</th>
                                        <th>Active</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rules as $rule)
                                        <tr>
                                            <td><span class="fw-medium">{{ $rule->name }}</span></td>
                                            <td><span class="badge bg-info">{{ ucfirst($rule->user_type) }}</span></td>
                                            <td>{{ ucfirst($rule->basis_type) }}</td>
                                            <td>{{ $rule->product?->brand_name ?? 'All' }}</td>
                                            <td>{{ number_format($rule->point_rate, 2) }}</td>
                                            <td>{{ $rule->bonus_type ? ucfirst($rule->bonus_type) . ': ' . number_format($rule->bonus_value, 2) : '—' }}</td>
                                            <td>
                                                @if ($rule->is_active)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td data-sort="{{ $rule->created_at->format('Y/m/d') }}">{{ $rule->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.reward-rules.show', $rule) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('admin.reward-rules.edit', $rule) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.reward-rules.destroy', $rule) }}" onsubmit="return confirm('Delete this rule?')">
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
                                            <td colspan="9" class="text-center py-4 text-muted">No reward rules found.</td>
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

