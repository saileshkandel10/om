@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Audit Log Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">Audit Logs</a></li>
                        <li class="breadcrumb-item active">Log #{{ $auditLog->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-dark-subtle text-dark d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-history fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Audit Log #{{ $auditLog->id }}</h5>
                        <p class="text-muted mb-0">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Module</span>
                            <span class="fw-medium">{{ $auditLog->module_name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Action</span>
                            <span class="badge bg-primary-subtle text-primary">{{ $auditLog->action_name ?? '—' }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">User</span>
                            <span class="fw-medium">{{ $auditLog->user?->name ?? 'System' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Email</span>
                            <span>{{ $auditLog->user?->email ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">IP Address</span>
                            <span><code>{{ $auditLog->ip_address ?? '—' }}</code></span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-history fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Audit Log #{{ $auditLog->id }}</span>
                <span class="text-primary fw-normal">details</span>
            </div>
            @if ($auditLog->before_json || $auditLog->after_json)
                <div class="row g-3">
                    @if ($auditLog->before_json)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Before</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <pre class="bg-light p-3 rounded mb-0" style="max-height: 400px; overflow-y: auto; font-size: 12px;">{{ json_encode($auditLog->before_json, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($auditLog->after_json)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">After</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <pre class="bg-light p-3 rounded mb-0" style="max-height: 400px; overflow-y: auto; font-size: 12px;">{{ json_encode($auditLog->after_json, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-4">
                        <i class="las la-database fs-2 d-block mb-2"></i>
                        <span>No data changes recorded for this log entry.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

