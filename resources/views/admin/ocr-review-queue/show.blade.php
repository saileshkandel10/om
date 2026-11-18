@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">OCR Review Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.ocr-review-queue.index') }}">OCR Review Queue</a></li>
                        <li class="breadcrumb-item active">Review #{{ $ocrReviewQueue->id }}</li>
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
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-eye fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Review #{{ $ocrReviewQueue->id }}</h5>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Review Status</span>
                            @php $rClasses = ['completed' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger', 'approved' => 'bg-success-subtle text-success']; @endphp
                            <span class="badge {{ $rClasses[$ocrReviewQueue->review_status] ?? 'bg-warning-subtle text-warning' }}">{{ $ocrReviewQueue->review_status ?? 'pending' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Assigned To</span>
                            <span class="fw-medium">{{ $ocrReviewQueue->assignedTo?->name ?? 'Unassigned' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">OCR Job</span>
                            <span><a href="{{ route('admin.ocr-jobs.show', $ocrReviewQueue->ocrJob) }}" class="text-primary">Job #{{ $ocrReviewQueue->ocrJob->id }}</a></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Module</span>
                            <span>{{ $ocrReviewQueue->ocrJob?->module_name ?? '—' }}</span>
                        </div>
                        @if ($ocrReviewQueue->reviewed_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Reviewed At</span>
                                <span>{{ $ocrReviewQueue->reviewed_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span>{{ $ocrReviewQueue->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-eye fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Review #{{ $ocrReviewQueue->id }}</span>
                <span class="text-primary fw-normal">notes & actions</span>
            </div>
            @if ($ocrReviewQueue->review_note)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Review Note</h4>
                    </div>
                    <div class="card-body pt-0">
                        <p class="bg-light p-3 rounded mb-0">{{ $ocrReviewQueue->review_note }}</p>
                    </div>
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Update Review</h4>
                </div>
                <div class="card-body pt-0">
                    <form method="POST" action="{{ route('admin.ocr-review-queue.update', $ocrReviewQueue) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Review Status</label>
                            <select name="review_status" class="form-select @error('review_status') is-invalid @enderror">
                                <option value="pending" @selected($ocrReviewQueue->review_status === 'pending')>Pending</option>
                                <option value="approved" @selected($ocrReviewQueue->review_status === 'approved')>Approved</option>
                                <option value="rejected" @selected($ocrReviewQueue->review_status === 'rejected')>Rejected</option>
                            </select>
                            @error('review_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Review Note</label>
                            <textarea name="review_note" class="form-control @error('review_note') is-invalid @enderror" rows="4" placeholder="Add your review comments...">{{ old('review_note', $ocrReviewQueue->review_note) }}</textarea>
                            @error('review_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="las la-save me-1"></i> Update Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

