@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Prescription Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.prescriptions.index') }}">Prescriptions</a></li>
                        <li class="breadcrumb-item active">#{{ $prescription->patient_ref_code ?? $prescription->id }}</li>
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
                            <i class="las la-prescription fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Prescription #{{ $prescription->patient_ref_code ?? $prescription->id }}</h5>
                        <p class="text-muted mb-0">{{ $prescription->created_at->format('M d, Y') }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @php $sColors = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                            <span class="badge {{ $sColors[$prescription->parsed_status] ?? 'bg-warning-subtle text-warning' }}">{{ $prescription->parsed_status ?? 'pending' }}</span>
                        </div>
                        @if ($prescription->confidence_score)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Confidence</span>
                                <span>{{ number_format($prescription->confidence_score, 1) }}%</span>
                            </div>
                        @endif
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Patient Ref</span>
                            <span class="fw-medium">{{ $prescription->patient_ref_code ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Doctor</span>
                            <span class="fw-medium">{{ $prescription->doctor?->user?->name ?? '—' }}</span>
                        </div>
                        @if ($prescription->doctor?->specialty)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Specialty</span>
                                <span>{{ $prescription->doctor->specialty }}</span>
                            </div>
                        @endif
                        @if ($prescription->doctor?->hospital_clinic)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Hospital</span>
                                <span>{{ $prescription->doctor->hospital_clinic }}</span>
                            </div>
                        @endif
                        @if ($prescription->uploadedFile)
                            <hr>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Uploaded File</span>
                                <span><a href="{{ route('admin.uploaded-files.show', $prescription->uploadedFile) }}" class="text-primary">{{ $prescription->uploadedFile->original_name ?? 'File' }}</a></span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3">
                        <form method="POST" action="{{ route('admin.prescriptions.destroy', $prescription) }}" class="d-inline" onsubmit="return confirm('Delete this prescription?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100"><i class="las la-trash-alt me-1"></i> Delete Prescription</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-prescription fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Prescription #{{ $prescription->patient_ref_code ?? $prescription->id }}</span>
                <span class="text-primary fw-normal">items</span>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-1 fs-13">Total Items</p>
                            <h3 class="mb-0 fw-bold">{{ $prescription->items->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            @if ($prescription->items->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Prescription Items ({{ $prescription->items->count() }})</h4>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Generic Name</th>
                                        <th>Dosage</th>
                                        <th>Duration</th>
                                        <th>Illness Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($prescription->items as $item)
                                        <tr>
                                            <td>{{ $item->medicine_name_raw ?? $item->product?->brand_name ?? '—' }}</td>
                                            <td>{{ $item->generic_name ?? '—' }}</td>
                                            <td>{{ $item->dosage_instruction ?? '—' }}</td>
                                            <td>{{ $item->duration ?? '—' }}</td>
                                            <td>{{ $item->illnessCategory?->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-4">
                        <i class="las la-pills fs-2 d-block mb-2"></i>
                        <span>No items recorded for this prescription.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

