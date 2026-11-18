<div class="patient-profile-modal-content">
    {{-- Top Patient Summary Card --}}
    <div class="card mb-3 border-0 bg-light-subtle shadow-none">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="thumb-xl rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-20" style="width: 60px; height: 60px;">
                        {{ strtoupper(substr($user->name, 0, 1) . (strpos($user->name, ' ') !== false ? substr(strrchr($user->name, ' '), 1, 1) : '')) }}
                    </div>
                </div>
                <div class="col">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="mb-0 fw-bold text-dark">{{ $user->name }}</h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">P-{{ 1000 + $user->id }}</span>
                        @if(($user->status ?? 'active') === 'active')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($user->status) }}</span>
                        @endif
                    </div>
                    <div class="text-muted small d-flex flex-wrap gap-3">
                        <span><i class="las la-phone me-1 text-primary"></i>{{ $user->mobile ?? '—' }}</span>
                        <span><i class="las la-envelope me-1 text-primary"></i>{{ $user->email ?? '—' }}</span>
                        <span><i class="las la-calendar me-1 text-primary"></i>DOB: {{ $user->profile?->dob ? \Carbon\Carbon::parse($user->profile->dob)->format('M d, Y') : '—' }} ({{ $user->profile?->dob ? \Carbon\Carbon::parse($user->profile->dob)->age.' yrs' : '—' }})</span>
                        <span><i class="las la-venus-mars me-1 text-primary"></i>{{ ucfirst($user->profile?->gender ?? '—') }}</span>
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="las la-map-marker-alt me-1 text-danger"></i>
                        {{ implode(', ', array_filter([$user->profile?->address, $user->profile?->district, $user->profile?->province])) ?: 'Address Not Recorded' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Diagnosed Illness Categories --}}
    <div class="card mb-3 border">
        <div class="card-header bg-light py-2">
            <h6 class="card-title mb-0 fs-14 d-flex align-items-center justify-content-between">
                <span><i class="las la-stethoscope text-primary me-1"></i> Diagnosed Illness Categories</span>
                <span class="badge bg-primary">{{ $patientIllnesses->count() }} Records</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Illness Category</th>
                            <th>Diagnosis Date</th>
                            <th>Diagnosed By / Source</th>
                            <th>Linked Product</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patientIllnesses as $illness)
                            <tr>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle fs-12 fw-semibold">
                                        {{ $illness->illnessCategory?->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>{{ $illness->detected_at ? $illness->detected_at->format('M d, Y') : '—' }}</td>
                                <td>{{ $illness->diagnosed_by ?? ucfirst(str_replace('_', ' ', $illness->source_type ?? '—')) }}</td>
                                <td>
                                    @if($illness->product)
                                        <span class="text-dark fw-medium">{{ $illness->product->brand_name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">{{ ucfirst($illness->status ?? 'active') }}</span>
                                </td>
                                <td class="small text-muted">{{ $illness->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">No illness records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Purchase Bills & Medical History --}}
    <div class="card mb-3 border">
        <div class="card-header bg-light py-2">
            <h6 class="card-title mb-0 fs-14 d-flex align-items-center justify-content-between">
                <span><i class="las la-file-invoice-dollar text-success me-1"></i> Purchase Bills & Visits</span>
                <span class="badge bg-success">{{ $bills->count() }} Bills</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Pharmacy / Store</th>
                            <th>Items / Medicines</th>
                            <th>Total (NPR)</th>
                            <th>OCR Status</th>
                            <th>Validation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-primary">#{{ $bill->invoice_number ?? $bill->id }}</span>
                                </td>
                                <td>{{ $bill->invoice_date ? \Carbon\Carbon::parse($bill->invoice_date)->format('M d, Y') : $bill->created_at->format('M d, Y') }}</td>
                                <td>{{ $bill->pharmacy?->store_name ?? '—' }}</td>
                                <td>
                                    @if($bill->items->count() > 0)
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($bill->items as $item)
                                                <li>
                                                    • {{ $item->product?->brand_name ?? $item->product_name_raw ?? 'Medicine' }}
                                                    @if($item->quantity) (Qty: {{ $item->quantity }}) @endif
                                                    @if($item->product?->illnessCategory)
                                                        <span class="badge bg-light text-primary border">{{ $item->product->illnessCategory->name }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted small">No items recorded</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">Rs. {{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                <td>
                                    @if($bill->ocr_status === 'completed')
                                        <span class="badge bg-success-subtle text-success">Completed</span>
                                    @elseif($bill->ocr_status === 'processing')
                                        <span class="badge bg-warning-subtle text-warning">Processing</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($bill->ocr_status ?? 'pending') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bill->validation_status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($bill->validation_status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ ucfirst($bill->validation_status ?? 'pending') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">No purchase bills recorded for this patient.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
