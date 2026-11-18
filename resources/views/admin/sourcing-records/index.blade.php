@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Sourcing Records</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Pharmacy Operations</a></li>
                            <li class="breadcrumb-item active">Sourcing Records</li>
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
                                <h4 class="card-title">All Sourcing Records</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.sourcing-records.create') }}" class="btn btn-sm btn-primary"><i class="las la-plus me-1"></i> New Record</a>
                                <span class="text-muted small ms-2">{{ $records->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.sourcing-records.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search supplier or invoice..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.sourcing-records.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Pharmacy</th>
                                        <th>Invoice No.</th>
                                        <th>Source Date</th>
                                        <th>Notes</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($records as $record)
                                        <tr>
                                            <td><span class="fw-medium">{{ $record->supplier_name }}</span></td>
                                            <td>{{ $record->pharmacy?->store_name ?? '—' }}</td>
                                            <td><code>{{ $record->source_invoice_no ?? '—' }}</code></td>
                                            <td>{{ $record->source_date?->format('M d, Y') ?? '—' }}</td>
                                            <td class="text-truncate" style="max-width:200px">{{ $record->notes ?? '—' }}</td>
                                            <td data-sort="{{ $record->created_at->format('Y/m/d') }}">{{ $record->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.sourcing-records.show', $record) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('admin.sourcing-records.edit', $record) }}">Edit</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.sourcing-records.destroy', $record) }}" onsubmit="return confirm('Delete this record?')">
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
                                            <td colspan="7" class="text-center py-4 text-muted">No sourcing records found.</td>
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

