@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">OCR Job Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.ocr-jobs.index') }}">OCR Jobs</a></li>
                        <li class="breadcrumb-item active">Job #{{ $ocrJob->id }}</li>
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
                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-microchip fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">OCR Job #{{ $ocrJob->id }}</h5>
                        <p class="text-muted mb-0">{{ $ocrJob->module_name ?? '—' }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @php $sColors = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                            <span class="badge {{ $sColors[$ocrJob->status] ?? 'bg-warning-subtle text-warning' }}">{{ $ocrJob->status ?? 'pending' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Module</span>
                            <span class="fw-medium">{{ $ocrJob->module_name ?? '—' }}</span>
                        </div>
                        @if ($ocrJob->confidence_score)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Confidence</span>
                                <span>{{ number_format($ocrJob->confidence_score, 1) }}%</span>
                            </div>
                        @endif
                        @if ($ocrJob->started_at && $ocrJob->completed_at)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Processing Time</span>
                                <span>{{ $ocrJob->started_at->diffInSeconds($ocrJob->completed_at) }}s</span>
                            </div>
                        @endif
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Source File</span>
                            <span>{{ $ocrJob->sourceFile?->original_name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span>{{ $ocrJob->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-microchip fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">OCR Job #{{ $ocrJob->id }}</span>
                <span class="text-primary fw-normal">details & review</span>
            </div>
            @if ($ocrJob->raw_text)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Raw Text</h4>
                    </div>
                    <div class="card-body pt-0">
                        <pre class="bg-light p-3 rounded mb-0" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; font-size: 13px;">{{ $ocrJob->raw_text }}</pre>
                    </div>
                </div>
            @endif
            @if ($ocrJob->parsed_json)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Parsed Data</h4>
                    </div>
                    <div class="card-body pt-0">
                        <pre class="bg-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto; font-size: 13px;">{{ json_encode($ocrJob->parsed_json, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
            @if ($ocrJob->reviewQueue->count() > 0)
                @php $review = $ocrJob->reviewQueue->first(); @endphp
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Review Queue</h4>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Review Status</span>
                            @php $rClasses = ['completed' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger']; @endphp
                            <span class="badge {{ $rClasses[$review->review_status] ?? 'bg-warning-subtle text-warning' }}">{{ $review->review_status ?? 'pending' }}</span>
                        </div>
                        @if ($review->assignedTo)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Assigned To</span>
                                <span>{{ $review->assignedTo->name }}</span>
                            </div>
                        @endif
                        @if ($review->review_note)
                            <div class="mb-2">
                                <span class="text-muted d-block mb-1">Review Note</span>
                                <p class="bg-light p-2 rounded mb-0">{{ $review->review_note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

