@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Prescriptions</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Doctors & Prescriptions</a></li>
                            <li class="breadcrumb-item active">Prescriptions</li>
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
                                <h4 class="card-title">All Prescriptions</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $prescriptions->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.prescriptions.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search patient ref..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="parsed" @selected(request('status') === 'parsed')>Parsed</option>
                                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient Ref</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Confidence</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($prescriptions as $prescription)
                                        <tr>
                                            <td><code>{{ $prescription->patient_ref_code ?? '—' }}</code></td>
                                            <td>{{ $prescription->doctor?->user?->name ?? '—' }}</td>
                                            <td>{{ $prescription->prescription_date?->format('M d, Y') ?? '—' }}</td>
                                            <td><span class="badge bg-secondary">{{ $prescription->source_type ?? '—' }}</span></td>
                                            <td>
                                                @php($statusColor = match ($prescription->parsed_status) {
                                                    'parsed' => 'success',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $prescription->parsed_status ?? 'pending' }}</span>
                                            </td>
                                            <td>{{ $prescription->confidence_score ? number_format($prescription->confidence_score, 2) . '%' : '—' }}</td>
                                            <td data-sort="{{ $prescription->created_at->format('Y/m/d') }}">{{ $prescription->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                                                        <i class="las la-ellipsis-v fs-20 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('admin.prescriptions.show', $prescription) }}">View</a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.prescriptions.destroy', $prescription) }}" onsubmit="return confirm('Delete this prescription?')">
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
                                            <td colspan="8" class="text-center py-4 text-muted">No prescriptions found.</td>
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

