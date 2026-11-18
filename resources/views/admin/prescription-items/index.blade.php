@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Prescription Items</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Doctors & Prescriptions</a></li>
                            <li class="breadcrumb-item active">Prescription Items</li>
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
                                <h4 class="card-title">All Prescription Items</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $items->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.prescription-items.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search medicine or generic name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.prescription-items.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Generic Name</th>
                                        <th>Prescription</th>
                                        <th>Doctor</th>
                                        <th>Quantity</th>
                                        <th>Illness Category</th>
                                        <th>Confidence</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td><span class="fw-medium">{{ $item->medicine_name_raw }}</span></td>
                                            <td>{{ $item->generic_name ?? '—' }}</td>
                                            <td><code>#{{ $item->prescription_id }}</code></td>
                                            <td>{{ $item->prescription?->doctor?->user?->name ?? '—' }}</td>
                                            <td>{{ $item->quantity ?? '—' }}</td>
                                            <td>{{ $item->illnessCategory?->name ?? '—' }}</td>
                                            <td>{{ $item->confidence_score ? number_format($item->confidence_score, 2) . '%' : '—' }}</td>
                                            <td data-sort="{{ $item->created_at->format('Y/m/d') }}">{{ $item->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.prescription-items.show', $item) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">No prescription items found.</td>
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

