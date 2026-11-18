@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Purchase Bill Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.purchase-bills.index') }}">Purchase Bills</a></li>
                        <li class="breadcrumb-item active">#{{ $purchaseBill->invoice_number ?? $purchaseBill->id }}</li>
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
                        <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-receipt fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Invoice #{{ $purchaseBill->invoice_number ?? $purchaseBill->id }}</h5>
                        <p class="text-muted mb-0">{{ $purchaseBill->invoice_date?->format('M d, Y') ?? '—' }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Total Amount</span>
                            <span class="fw-bold fs-5">Rs {{ number_format($purchaseBill->total_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Subtotal</span>
                            <span>Rs {{ number_format($purchaseBill->subtotal_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Tax</span>
                            <span>Rs {{ number_format($purchaseBill->tax_amount ?? 0, 2) }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">OCR Status</span>
                            @php $oClasses = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                            <span class="badge {{ $oClasses[$purchaseBill->ocr_status] ?? 'bg-secondary-subtle text-secondary' }}">{{ $purchaseBill->ocr_status ?? 'pending' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Validation</span>
                            @php $vClasses = ['verified' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger']; @endphp
                            <span class="badge {{ $vClasses[$purchaseBill->validation_status] ?? 'bg-secondary-subtle text-secondary' }}">{{ $purchaseBill->validation_status ?? 'pending' }}</span>
                        </div>
                        @if ($purchaseBill->confidence_score)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Confidence</span>
                                <span>{{ number_format($purchaseBill->confidence_score, 1) }}%</span>
                            </div>
                        @endif
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Fraud Flag</span>
                            @if ($purchaseBill->fraud_flag)
                                <span class="badge bg-danger-subtle text-danger"><i class="las la-exclamation-triangle me-1"></i>Flagged</span>
                            @else
                                <span class="badge bg-success-subtle text-success">Clean</span>
                            @endif
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Duplicate</span>
                            @if ($purchaseBill->duplicate_flag)
                                <span class="badge bg-warning-subtle text-warning">Possible Duplicate</span>
                            @else
                                <span class="badge bg-success-subtle text-success">No</span>
                            @endif
                        </div>
                        @if ($purchaseBill->validation_status !== 'approved')
                            <form method="POST" action="{{ route('admin.purchase-bills.verify', $purchaseBill) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-semibold">
                                    <i class="las la-check-circle me-1"></i> Manually Verify & Approve
                                </button>
                            </form>
                        @endif
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Pharmacy</span>
                            <span class="fw-medium">{{ $purchaseBill->pharmacy_name ?? $purchaseBill->pharmacy?->store_name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Uploaded By</span>
                            <span class="fw-medium">{{ $purchaseBill->user?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span>{{ $purchaseBill->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        @if ($purchaseBill->uploaded_file_id && $purchaseBill->ocr_status !== 'completed')
                            <form method="POST" action="{{ route('admin.purchase-bills.rescan-ocr', $purchaseBill) }}" class="d-inline mb-2" onsubmit="return confirm('Rescan this bill with OCR?');">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm w-100 mb-2"><i class="las la-sync me-1"></i> Rescan OCR</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.purchase-bills.destroy', $purchaseBill) }}" class="d-inline" onsubmit="return confirm('Delete this bill?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100"><i class="las la-trash-alt me-1"></i> Delete Bill</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-receipt fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Invoice #{{ $purchaseBill->invoice_number ?? $purchaseBill->id }}</span>
                <span class="text-primary fw-normal">bill items & OCR report</span>
            </div>

            @php
                $extracted = $purchaseBill->ocrJob?->parsed_json['_extracted'] ?? [];
            @endphp
            <div class="card mb-3">
                <div class="card-header" style="background-color: #fce8f3;">
                    <h4 class="card-title text-dark">
                        <i class="las la-file-invoice-dollar me-1" style="color: #CD0B6F;"></i> Extracted Bill Summary & Details
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 28%;">Extracted Field</th>
                                    <th>Details / Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted">Pharmacy Name</td>
                                    <td><strong class="text-dark fs-6">{{ $purchaseBill->pharmacy_name ?? $extracted['pharmacy_name'] ?? $purchaseBill->pharmacy?->store_name ?? 'Not Extracted' }}</strong></td>
                                </tr>
                                @if (!empty($extracted['pan_number']) || !empty($purchaseBill->pharmacy?->pan_number))
                                    <tr>
                                        <td class="fw-semibold text-muted">PAN / VAT Number</td>
                                        <td><code>{{ $extracted['pan_number'] ?? $purchaseBill->pharmacy?->pan_number }}</code></td>
                                    </tr>
                                @endif
                                @if (!empty($extracted['dda_reg']))
                                    <tr>
                                        <td class="fw-semibold text-muted">DDA Reg. No</td>
                                        <td><code>{{ $extracted['dda_reg'] }}</code></td>
                                    </tr>
                                @endif
                                @if (!empty($extracted['pharmacy_phone']) || !empty($purchaseBill->pharmacy?->phone))
                                    <tr>
                                        <td class="fw-semibold text-muted">Pharmacy Phone</td>
                                        <td>{{ $extracted['pharmacy_phone'] ?? $purchaseBill->pharmacy?->phone }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fw-semibold text-muted">Invoice / Bill Number</td>
                                    <td><span class="badge bg-primary-subtle text-primary fs-12 px-2 py-1">{{ $purchaseBill->invoice_number ?? $extracted['invoice_number'] ?? 'Not Extracted' }}</span></td>
                                </tr>
                                @if (!empty($extracted['ref_number']))
                                    <tr>
                                        <td class="fw-semibold text-muted">Reference No (Ref #)</td>
                                        <td><code>{{ $extracted['ref_number'] }}</code></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fw-semibold text-muted">Invoice Date & Time</td>
                                    <td>
                                        <i class="las la-calendar me-1 text-muted"></i>
                                        {{ $purchaseBill->invoice_date?->format('M d, Y') ?? $extracted['invoice_date'] ?? 'Not Extracted' }}
                                        @if (!empty($extracted['invoice_time']))
                                            <span class="text-muted ms-2"><i class="las la-clock me-1"></i>{{ $extracted['invoice_time'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @if (!empty($extracted['customer_name']) || !empty($purchaseBill->user?->name))
                                    <tr>
                                        <td class="fw-semibold text-muted">Customer / Patient Name</td>
                                        <td><i class="las la-user me-1 text-muted"></i>{{ $extracted['customer_name'] ?? $purchaseBill->user?->name }}</td>
                                    </tr>
                                @endif
                                @if (!empty($extracted['customer_phone']))
                                    <tr>
                                        <td class="fw-semibold text-muted">Customer Phone</td>
                                        <td><i class="las la-phone me-1 text-muted"></i>{{ $extracted['customer_phone'] }}</td>
                                    </tr>
                                @endif
                                @if (!empty($extracted['customer_address']))
                                    <tr>
                                        <td class="fw-semibold text-muted">Customer Address</td>
                                        <td><i class="las la-map-marker me-1 text-muted"></i>{{ $extracted['customer_address'] }}</td>
                                    </tr>
                                @endif
                                @if ($purchaseBill->doctor_name || !empty($extracted['doctor_name']))
                                    <tr>
                                        <td class="fw-semibold text-muted">Doctor / Dispensed By</td>
                                        <td><i class="las la-user-md me-1 text-muted"></i>{{ $purchaseBill->doctor_name ?? $extracted['doctor_name'] }}</td>
                                    </tr>
                                @endif
                                @if ($purchaseBill->doctor_nmc || !empty($extracted['doctor_nmc']))
                                    <tr>
                                        <td class="fw-semibold text-muted">Doctor NMC / Reg. #</td>
                                        <td><code>{{ $purchaseBill->doctor_nmc ?? $extracted['doctor_nmc'] }}</code></td>
                                    </tr>
                                @endif
                                @if (!empty($extracted['payment_mode']))
                                    <tr>
                                        <td class="fw-semibold text-muted">Payment Mode</td>
                                        <td><span class="badge bg-success-subtle text-success">{{ $extracted['payment_mode'] }}</span></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fw-semibold text-muted">Medicine Name (Extracted Items)</td>
                                    <td>
                                        @php
                                            $displayItems = $purchaseBill->items->count() > 0 ? $purchaseBill->items : ($extracted['items'] ?? []);
                                        @endphp
                                        @if (count($displayItems) > 0)
                                            <div class="table-responsive rounded border">
                                                <table class="table table-sm table-striped mb-0 small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Medicine Name</th>
                                                            <th>Batch</th>
                                                            <th>Qty</th>
                                                            <th>Unit Price</th>
                                                            <th class="text-end">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($displayItems as $idx => $item)
                                                            @php
                                                                $isModel = is_object($item);
                                                                $name = $isModel ? ($item->product_name_raw ?? $item->product?->brand_name) : ($item['name'] ?? '—');
                                                                $batch = $isModel ? ($item->batch_no ?? null) : ($item['batch_no'] ?? null);
                                                                $qty = $isModel ? ($item->quantity ?? 1) : ($item['quantity'] ?? 1);
                                                                $unitPrice = $isModel ? ($item->unit_price ?? null) : ($item['unit_price'] ?? null);
                                                                $amount = $isModel ? ($item->amount ?? null) : ($item['amount'] ?? null);
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $idx + 1 }}</td>
                                                                <td class="fw-semibold text-dark">{{ $name }}</td>
                                                                <td><code>{{ $batch ?? '—' }}</code></td>
                                                                <td>{{ $qty }}</td>
                                                                <td>{{ $unitPrice ? 'Rs ' . number_format($unitPrice, 2) : '—' }}</td>
                                                                <td class="text-end fw-bold text-success">{{ $amount ? 'Rs ' . number_format($amount, 2) : '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">Not Extracted</span>
                                        @endif
                                    </td>
                                </tr>
                                @if (!empty($extracted['subtotal_amount']) || $purchaseBill->subtotal_amount > 0)
                                    <tr>
                                        <td class="fw-semibold text-muted">Gross / Subtotal Amount</td>
                                        <td>Rs {{ number_format($purchaseBill->subtotal_amount ?? $extracted['subtotal_amount'] ?? 0, 2) }}</td>
                                    </tr>
                                @endif
                                @if (!empty($extracted['discount_amount']) && $extracted['discount_amount'] > 0)
                                    <tr>
                                        <td class="fw-semibold text-muted">Discount</td>
                                        <td class="text-danger">- Rs {{ number_format($extracted['discount_amount'], 2) }}</td>
                                    </tr>
                                @endif
                                @if ($purchaseBill->tax_amount > 0 || !empty($extracted['tax_amount']))
                                    <tr>
                                        <td class="fw-semibold text-muted">VAT / Tax</td>
                                        <td>Rs {{ number_format($purchaseBill->tax_amount ?? $extracted['tax_amount'] ?? 0, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fw-semibold text-muted">Grand Total Amount</td>
                                    <td><span class="fw-bold fs-5 text-success">Rs {{ number_format($purchaseBill->total_amount ?? $extracted['total_amount'] ?? 0, 2) }}</span></td>
                                </tr>
                                @if (!empty($extracted['amount_in_words']))
                                    <tr>
                                        <td class="fw-semibold text-muted">In Words</td>
                                        <td class="fst-italic text-muted">{{ $extracted['amount_in_words'] }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fw-semibold text-muted">City / Location</td>
                                    <td>{{ $purchaseBill->ocrJob?->parsed_json['_extracted']['city'] ?? $purchaseBill->pharmacy_location ?? 'Kathmandu' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">OCR Processing Status</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($purchaseBill->ocr_status ?? 'pending') }}</span>
                                        @if (!empty($extracted['verification']['status']))
                                            <span class="badge {{ $extracted['verification']['status'] === 'verified' ? 'bg-success' : ($extracted['verification']['status'] === 'conflict' ? 'bg-danger' : 'bg-warning') }} ms-1">
                                                {{ ucfirst($extracted['verification']['status']) }}
                                            </span>
                                        @endif
                                        @if (!empty($extracted['verification']['calculation_check']) && $extracted['verification']['calculation_check'] === 'passed')
                                            <span class="badge bg-success-subtle text-success ms-1"><i class="las la-check-circle me-1"></i>Math Verified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Points Awarded</td>
                                    <td><span class="badge bg-success">{{ number_format($purchaseBill->points_awarded ?? 0, 2) }} pts</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($purchaseBill->ocrJob)
                @php $ocrJob = $purchaseBill->ocrJob; @endphp
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">
                                    <i class="las la-microchip me-1"></i> OCR Data
                                    <span class="badge {{ $ocrJob->status === 'completed' ? 'bg-success' : 'bg-danger' }} ms-2">{{ $ocrJob->status }}</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block">Engine</small>
                                    <span class="fw-semibold">{{ $ocrJob->engine_name ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block">Started</small>
                                    <span class="fw-semibold">{{ $ocrJob->started_at?->format('d/m/Y H:i:s') ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block">Completed</small>
                                    <span class="fw-semibold">{{ $ocrJob->completed_at?->format('d/m/Y H:i:s') ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        @php $extracted = $ocrJob->parsed_json['_extracted'] ?? null; @endphp

                        @if ($extracted)
                            <div class="mb-3">
                                <h6 class="fw-semibold mb-2">Extracted Data</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Field</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($extracted as $key => $value)
                                                <tr>
                                                    <td class="text-capitalize">{{ str_replace('_', ' ', $key) }}</td>
                                                    <td>
                                                        @if ($value === null)
                                                            <span class="text-muted fst-italic">Not detected</span>
                                                        @elseif (is_array($value))
                                                            <pre class="mb-0 small" style="white-space: pre-wrap;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                        @elseif (is_numeric($value))
                                                            <span class="fw-semibold">Rs {{ number_format($value, 2) }}</span>
                                                        @else
                                                            <span class="fw-semibold">{{ $value }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if ($ocrJob->raw_text)
                            <div class="mb-3">
                                <h6 class="fw-semibold mb-2">
                                    Raw OCR Text
                                    <a href="#rawTextCollapse" data-bs-toggle="collapse" class="small text-muted ms-2">(toggle)</a>
                                </h6>
                                <div class="collapse" id="rawTextCollapse">
                                    <pre class="p-3 border rounded bg-dark text-light" style="max-height: 300px; overflow-y: auto; font-size: 12px; white-space: pre-wrap;">{{ $ocrJob->raw_text }}</pre>
                                </div>
                            </div>
                        @endif

                        @if ($ocrJob->parsed_json)
                            <div>
                                <h6 class="fw-semibold mb-2">
                                    Full OCR.space Response
                                    <a href="#fullResponseCollapse" data-bs-toggle="collapse" class="small text-muted ms-2">(toggle)</a>
                                </h6>
                                <div class="collapse" id="fullResponseCollapse">
                                    <pre class="p-3 border rounded bg-dark text-light" style="max-height: 400px; overflow-y: auto; font-size: 12px; white-space: pre-wrap;" id="ocrFullResponse">{{ json_encode($ocrJob->parsed_json, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($purchaseBill->rewardTransactions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Reward Transactions</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Points</th>
                                        <th>Bonus</th>
                                        <th>Royalty</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseBill->rewardTransactions as $txn)
                                        <tr>
                                            <td>{{ $txn->user?->name ?? '—' }}</td>
                                            <td>{{ number_format($txn->points_earned ?? 0, 2) }}</td>
                                            <td>{{ number_format($txn->bonus_amount ?? 0, 2) }}</td>
                                            <td>{{ number_format($txn->royalty_amount ?? 0, 2) }}</td>
                                            <td><span class="badge bg-info-subtle text-info">{{ $txn->transaction_type ?? '—' }}</span></td>
                                            <td>
                                                @php $tClasses = ['completed' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'failed' => 'bg-danger-subtle text-danger']; @endphp
                                                <span class="badge {{ $tClasses[$txn->status] ?? 'bg-secondary-subtle text-secondary' }}">{{ $txn->status ?? '—' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

