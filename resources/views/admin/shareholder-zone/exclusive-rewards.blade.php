@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Exclusive Rewards</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Shareholder Zone</a></li>
                            <li class="breadcrumb-item active">Exclusive Rewards</li>
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
                                <h4 class="card-title">Active Shareholders</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $shareholders->count() }} eligible</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.exclusive-rewards') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search name or BOID..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.exclusive-rewards') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Shareholder</th>
                                        <th>BOID</th>
                                        <th>Mobile</th>
                                        <th>Accounts</th>
                                        <th>Status</th>
                                        <th>Added</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($shareholders as $shareholder)
                                        <tr>
                                            <td><span class="fw-medium">{{ $shareholder->shareholder_name }}</span></td>
                                            <td><code>{{ $shareholder->boid }}</code></td>
                                            <td>{{ $shareholder->mobile ?? '—' }}</td>
                                            <td><span class="badge bg-info">{{ $shareholder->accounts_count }}</span></td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td data-sort="{{ $shareholder->created_at->format('Y/m/d') }}">{{ $shareholder->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.shareholders.show', $shareholder) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No active shareholders found.</td>
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

