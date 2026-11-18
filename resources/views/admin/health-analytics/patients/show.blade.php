@extends('layouts.admin')

@section('title', __('Patient Health Analytics - ') . $patient->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Patient Health Analytics: {{ $patient->name }}</h4>
                    <p class="text-muted small mb-0">Patient ID: P-{{ $patient->id }} • Joined: {{ $patient->created_at->format('M d, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.health-analytics.patients.reanalyze', $patient) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary" style="color: #CD0B6F; border-color: #CD0B6F;">
                            <i class="iconoir-refresh me-1"></i> Re-Analyze Records
                        </button>
                    </form>
                    <a href="{{ route('admin.health-analytics.patients.index') }}" class="btn btn-sm btn-outline-secondary">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mandatory Medical Safety Notice -->
    <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-4" role="alert">
        <div class="bg-warning text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <i class="iconoir-warning-triangle fs-4"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Administrative Medication Pattern Analytics</h6>
            <p class="mb-0 small fw-medium text-dark">
                <strong>Important Notice:</strong> This is a medication-based analytical insight and does not confirm a medical diagnosis. It must not be interpreted as a disease prediction or clinical recommendation.
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Patient Information Left Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Patient Profile</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold fs-3 shadow-sm mb-2" style="width:70px;height:70px;background-color:#CD0B6F;">
                            {{ $patient->initials() }}
                        </div>
                        <h5 class="fw-bold mb-0">{{ $patient->name }}</h5>
                        <p class="text-muted small mb-0">{{ $patient->email }}</p>
                        <span class="badge bg-primary-subtle text-primary mt-2 text-capitalize">Patient Role</span>
                    </div>
                    <hr>
                    <div class="vstack gap-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Patient ID</span>
                            <span class="fw-semibold">P-{{ $patient->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Contact Phone</span>
                            <span class="fw-semibold">{{ $patient->mobile ?? 'Not specified' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Bills</span>
                            <span class="fw-semibold">{{ $patient->purchaseBills()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medication Timeline -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Medication Timeline</h5>
                </div>
                <div class="card-body">
                    @forelse ($timeline as $period => $items)
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark fs-12 text-uppercase mb-2" style="letter-spacing: 0.5px;">{{ $period }}</h6>
                            <div class="vstack gap-2">
                                @foreach ($items as $item)
                                    <div class="p-2 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-13">{{ $item['medicine_name'] }}</div>
                                            <small class="text-muted">{{ $item['generic_name'] ?? $item['category'] }}</small>
                                        </div>
                                        <span class="badge bg-white text-dark border">{{ $item['source_reference'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No timeline events recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Detected Health Patterns Right Area -->
        <div class="col-lg-8">
            <h5 class="fw-bold text-dark mb-3">Observed Medication Patterns & Analytical Insights</h5>

            @forelse ($analytics as $an)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge mb-2 px-3 py-1.5" style="background-color: {{ $an->healthCategory->color }}20; color: {{ $an->healthCategory->color }}; border: 1px solid {{ $an->healthCategory->color }}40; font-size: 13px;">
                                    <i class="{{ $an->healthCategory->icon }} me-1"></i> {{ $an->healthCategory->name }}
                                </span>
                                <h5 class="fw-bold text-dark mb-1">{{ $an->association_level }}</h5>
                            </div>
                            <div>
                                @php
                                    $statusColor = match ($an->status) {
                                        'confirmed_pattern' => 'success',
                                        'not_applicable' => 'secondary',
                                        'needs_review' => 'danger',
                                        default => 'warning'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-3 py-2 fs-12 text-capitalize">
                                    {{ str_replace('_', ' ', $an->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-3">
                            <p class="mb-0 text-dark fs-13" style="line-height: 1.6;">
                                <i class="iconoir-notes me-1 text-muted"></i> {{ $an->explanation }}
                            </p>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <div class="p-2 border rounded-3 text-center bg-white">
                                    <small class="text-muted d-block">Supporting Medicines</small>
                                    <span class="fw-bold fs-16">{{ $an->supporting_medicines_count }} unique meds</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 border rounded-3 text-center bg-white">
                                    <small class="text-muted d-block">First Detected</small>
                                    <span class="fw-bold fs-14">{{ $an->first_detected_date?->format('M d, Y') ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 border rounded-3 text-center bg-white">
                                    <small class="text-muted d-block">Latest Detected</small>
                                    <span class="fw-bold fs-14">{{ $an->latest_detected_date?->format('M d, Y') ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Traceable Source Evidence -->
                        <h6 class="fw-bold fs-13 text-muted text-uppercase mb-2">Source Records & Evidence:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Detected Medicine</th>
                                        <th>Active Ingredient</th>
                                        <th>Date</th>
                                        <th>Source Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($an->evidences as $ev)
                                        <tr>
                                            <td class="fw-semibold">{{ $ev->medicine_name }}</td>
                                            <td class="text-muted small">{{ $ev->generic_name ?? '—' }}</td>
                                            <td>{{ $ev->detected_date?->format('M d, Y') }}</td>
                                            <td>
                                                @if ($ev->source_type === 'purchase_bill')
                                                    <a href="{{ route('admin.purchase-bills.show', $ev->source_id) }}" class="badge bg-light text-primary border text-decoration-none" target="_blank">
                                                        <i class="iconoir-open-new-window me-1"></i> {{ $ev->source_reference }}
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-dark border">{{ $ev->source_reference }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Clinical Review Actions -->
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">Clinical Audit & Decision:</small>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('admin.health-analytics.reviews.submit', $an) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="confirmed_pattern">
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="iconoir-check me-1"></i> Confirm Pattern
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.health-analytics.reviews.submit', $an) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="needs_review">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="iconoir-eye me-1"></i> Needs Review
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.health-analytics.reviews.submit', $an) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="not_applicable">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        Not Applicable
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="iconoir-activity fs-1 text-muted opacity-50 mb-3 d-block"></i>
                    <h5>No Health Analytics Patterns Detected Yet</h5>
                    <p class="small">Upload purchase bills or medical prescriptions for this patient to generate analytical insights.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
