<form method="POST" action="{{ route('admin.settings.locations.bills.map', $bill) }}">
    @csrf
    <div class="row g-3">
        <div class="col-12">
            <div class="p-3 bg-light rounded border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary fs-12">Invoice #{{ $formatted['invoice_number'] }}</span>
                    <span class="fw-bold fs-15 text-dark">Rs. {{ number_format($formatted['total_amount'], 2) }}</span>
                </div>
                <div class="row g-2 small text-muted">
                    <div class="col-6">
                        <strong><i class="las la-user me-1 text-primary"></i>Customer:</strong> {{ $formatted['customer_name'] }}
                        @if($formatted['customer_phone'] !== '—') ({{ $formatted['customer_phone'] }}) @endif
                    </div>
                    <div class="col-6">
                        <strong><i class="las la-clinic-medical me-1 text-success"></i>Pharmacy:</strong> {{ $formatted['pharmacy_name'] }}
                    </div>
                    <div class="col-6">
                        <strong><i class="las la-calendar me-1"></i>Date:</strong> {{ $formatted['invoice_date'] }}
                    </div>
                    <div class="col-6">
                        <strong><i class="las la-boxes me-1"></i>Items:</strong> {{ $formatted['items_count'] }} items
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold small mb-1">Extracted Raw Address Strings:</label>
            <div class="p-2 border rounded bg-white small text-break">
                <i class="las la-map-marker-alt text-danger me-1"></i> {{ $formatted['raw_address'] }}
            </div>
            @if(!empty($formatted['ocr_snippet']))
                <div class="mt-2 p-2 bg-light-subtle border rounded small text-muted">
                    <strong>OCR Text Snippet:</strong> {{ $formatted['ocr_snippet'] }}
                </div>
            @endif
        </div>

        <div class="col-12">
            <label class="form-label fw-bold small">Assign Official District <span class="text-danger">*</span></label>
            <select name="district_id" class="form-select" required>
                <option value="">Choose District...</option>
                @foreach($districts as $d)
                    <option value="{{ $d->id }}" {{ $formatted['suggested_district_id'] === $d->id ? 'selected' : '' }}>
                        {{ $d->name }} ({{ $d->province?->name ?? 'Nepal' }})
                        @if($formatted['suggested_district_id'] === $d->id) ★ (Suggested Match) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold small">Notes (Optional)</label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Verified via customer delivery address">
        </div>
    </div>

    <div class="modal-footer px-0 pb-0 pt-3 mt-3 border-top d-flex justify-content-between">
        <div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="skipCurrentBill({{ $bill->id }})">
                <i class="las la-forward me-1"></i> Skip / Ignore Order
            </button>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="las la-check me-1"></i> Save District Assignment
            </button>
        </div>
    </div>
</form>

<form id="skipBillForm_{{ $bill->id }}" method="POST" action="{{ route('admin.settings.locations.bills.skip', $bill) }}" style="display:none;">
    @csrf
    <input type="hidden" name="reason" value="Skipped from individual modal review">
</form>
