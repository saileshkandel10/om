@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">OCR Review Queue</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">OCR & Bills</a></li>
                            <li class="breadcrumb-item active">Review Queue</li>
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
                                <h4 class="card-title">OCR Review Queue</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $reviews->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.ocr-review-queue.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.ocr-review-queue.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>OCR Job</th>
                                        <th>Assigned To</th>
                                        <th>Review Status</th>
                                        <th>Review Note</th>
                                        <th>Reviewed At</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reviews as $review)
                                        <tr>
                                            <td><code>#{{ $review->id }}</code></td>
                                            <td><a href="{{ route('admin.ocr-jobs.show', $review->ocrJob) }}">Job #{{ $review->ocr_job_id }}</a></td>
                                            <td>{{ $review->assignedTo?->name ?? '—' }}</td>
                                            <td>
                                                @php($statusColor = match ($review->review_status) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'pending' => 'warning',
                                                    default => 'secondary',
                                                })
                                                <span class="badge bg-{{ $statusColor }}">{{ $review->review_status ?? 'pending' }}</span>
                                            </td>
                                            <td class="text-truncate" style="max-width:200px">{{ $review->review_note ?? '—' }}</td>
                                            <td>{{ $review->reviewed_at?->format('M d, H:i') ?? '—' }}</td>
                                            <td data-sort="{{ $review->created_at->format('Y/m/d') }}">{{ $review->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ocr-review-queue.show', $review) }}">Review</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No reviews found.</td>
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

