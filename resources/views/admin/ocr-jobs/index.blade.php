@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">OCR Jobs</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">OCR & Bills</a></li>
                            <li class="breadcrumb-item active">OCR Jobs</li>
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
                                <h4 class="card-title">All OCR Jobs</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $jobs->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.ocr-jobs.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="module" class="form-select form-select-sm">
                                    <option value="">All Modules</option>
                                    <option value="purchase_bill" @selected(request('module') === 'purchase_bill')>Purchase Bill</option>
                                    <option value="prescription" @selected(request('module') === 'prescription')>Prescription</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="processing" @selected(request('status') === 'processing')>Processing</option>
                                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.ocr-jobs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Module</th>
                                        <th>Source File</th>
                                        <th>Engine</th>
                                        <th>Confidence</th>
                                        <th>Status</th>
                                        <th>Started</th>
                                        <th>Completed</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($jobs as $job)
                                        <tr>
                                            <td><code>#{{ $job->id }}</code></td>
                                            <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $job->module_name ?? '—') }}</span></td>
                                            <td class="text-truncate" style="max-width:150px">{{ $job->sourceFile?->original_name ?? '—' }}</td>
                                            <td>{{ $job->engine_name ?? '—' }}</td>
                                            <td>{{ $job->confidence_score ? number_format($job->confidence_score, 2) . '%' : '—' }}</td>
                                            <td>
                                                @php($statusColor = match ($job->status) {
                                                    'completed' => 'success',
                                                    'processing' => 'info',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $job->status }}</span>
                                            </td>
                                            <td>{{ $job->started_at?->format('M d, H:i') ?? '—' }}</td>
                                            <td>{{ $job->completed_at?->format('M d, H:i') ?? '—' }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ocr-jobs.show', $job) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">No OCR jobs found.</td>
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

