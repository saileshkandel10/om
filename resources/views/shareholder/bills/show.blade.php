@extends('layouts.shareholder')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Bill Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('shareholder.dashboard') }}">{{ config('system.name') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shareholder.bills.index') }}">Purchase Bills</a></li>
                        <li class="breadcrumb-item active">{{ $purchaseBill->invoice_number ?? '#' . $purchaseBill->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-receipt fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">{{ $purchaseBill->invoice_number ?? 'Bill #' . $purchaseBill->id }}</h5>
                        <p class="text-muted mb-0">Purchase Bill</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">OCR Status</span>
                            @php
                                $ocrColors = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning'];
                            @endphp
                            <span class="badge {{ $ocrColors[$purchaseBill->ocr_status] ?? 'bg-warning-subtle text-warning' }}">
                                {{ ucfirst($purchaseBill->ocr_status ?? 'pending') }}
                            </span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Validation</span>
                            @php
                                $valColors = ['verified' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning'];
                            @endphp
                            <span class="badge {{ $valColors[$purchaseBill->validation_status] ?? 'bg-warning-subtle text-warning' }}">
                                {{ ucfirst($purchaseBill->validation_status ?? 'pending') }}
                            </span>
                        </div>
                        @if ($purchaseBill->duplicate_flag)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Duplicate</span>
                                <span class="badge bg-danger-subtle text-danger">Flagged</span>
                            </div>
                        @endif
                        @if ($purchaseBill->fraud_flag)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Fraud Check</span>
                                <span class="badge bg-danger-subtle text-danger">Flagged</span>
                            </div>
                        @endif
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Submitted</span>
                            <span>{{ $purchaseBill->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Last Updated</span>
                            <span>{{ $purchaseBill->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <a href="{{ route('shareholder.bills.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="las la-arrow-left me-1"></i> Back to Bills
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Rewards Earned</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @forelse ($purchaseBill->rewardTransactions as $txn)
                        <div class="d-flex align-items-center py-2 border-bottom border-dashed">
                            <div class="flex-shrink-0 thumb-md rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center">
                                <i class="las la-coins fs-5"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-semibold fs-13">{{ $txn->type ?? 'Reward' }}</h6>
                                <small class="text-muted">{{ $txn->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success">+{{ number_format($txn->points_earned ?? 0, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="las la-coins fs-2 mb-2 d-block"></i>
                            <p class="mb-0 small">No rewards calculated yet.</p>
                            <small>Rewards are calculated after OCR processing.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-file-invoice fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Bill #{{ $purchaseBill->invoice_number ?? $purchaseBill->id }}</span>
                <span class="text-primary fw-normal">details</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-money-bill fs-24 align-self-center text-info me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Total Amount</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">Rs {{ number_format($purchaseBill->total_amount ?? 0, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-store fs-24 align-self-center text-success me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Pharmacy</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold fs-14">{{ $purchaseBill->pharmacy?->store_name ?? '—' }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-calendar fs-24 align-self-center text-primary me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Invoice Date</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold fs-14">{{ $purchaseBill->invoice_date?->format('M d, Y') ?? '—' }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-check-circle fs-24 align-self-center text-warning me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Confidence</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">
                                        {{ $purchaseBill->confidence_score ? number_format($purchaseBill->confidence_score * 100, 0) . '%' : '—' }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($purchaseBill->items->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Bill Items</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseBill->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->item_name ?? $item->product?->generic_name ?? 'Item #' . $item->id }}</td>
                                            <td>{{ $item->quantity ?? '—' }}</td>
                                            <td>{{ $item->rate ? 'Rs ' . number_format($item->rate, 2) : '—' }}</td>
                                            <td class="fw-semibold">{{ $item->total ? 'Rs ' . number_format($item->total, 2) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if ($purchaseBill->uploadedFile)
                @php
                    $fileUrl = \Illuminate\Support\Facades\Storage::url($purchaseBill->uploadedFile->file_path);
                    $isImage = $purchaseBill->uploadedFile->mime_type && str_starts_with($purchaseBill->uploadedFile->mime_type, 'image/');
                @endphp
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Uploaded Receipt</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="las la-external-link-alt me-1"></i> Open Full Size
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="las la-file-image fs-2 text-info"></i>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ basename($purchaseBill->uploadedFile->file_path) }}</h6>
                                <small class="text-muted">
                                    {{ $purchaseBill->uploadedFile->file_size ? number_format($purchaseBill->uploadedFile->file_size / 1024, 1) . ' KB' : '' }}
                                    &middot; Uploaded {{ $purchaseBill->uploadedFile->created_at?->format('M d, Y \\a\\t h:i A') }}
                                </small>
                            </div>
                        </div>

                        @if ($isImage)
                            <a href="#" data-bs-toggle="modal" data-bs-target="#receiptModal">
                                <img src="{{ $fileUrl }}" alt="Receipt"
                                    class="img-fluid rounded border w-100"
                                    style="max-height: 400px; object-fit: contain; background: #f8f9fa; cursor: pointer;">
                            </a>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    <i class="las la-search-plus me-1"></i> Click image to preview full size
                                </small>
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-center p-5 border rounded bg-light flex-column">
                                <i class="las la-file-pdf fs-1 text-danger mb-2"></i>
                                <p class="mb-1 fw-semibold">PDF Document</p>
                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-danger mt-2">
                                    <i class="las la-download me-1"></i> View PDF
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($isImage)
                    {{-- Receipt Full-Size Modal --}}
                    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title" id="receiptModalLabel">
                                        <i class="las la-receipt me-1"></i> Receipt Preview
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center p-0">
                                    <img src="{{ $fileUrl }}" alt="Receipt Full Size" class="img-fluid w-100" style="background: #f8f9fa;">
                                </div>
                                <div class="modal-footer border-0 justify-content-center">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-primary">
                                        <i class="las la-external-link-alt me-1"></i> Open in New Tab
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
