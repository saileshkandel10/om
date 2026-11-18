@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Prescription Item Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.prescription-items.index') }}">Prescription Items</a></li>
                        <li class="breadcrumb-item active">Item #{{ $prescriptionItem->id }}</li>
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
                            <i class="las la-pills fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Prescription Item #{{ $prescriptionItem->id }}</h5>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Medicine</span>
                            <span class="fw-medium">{{ $prescriptionItem->medicine_name_raw ?? $prescriptionItem->product?->brand_name ?? '—' }}</span>
                        </div>
                        @if ($prescriptionItem->generic_name)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Generic Name</span>
                                <span>{{ $prescriptionItem->generic_name }}</span>
                            </div>
                        @endif
                        @if ($prescriptionItem->dosage_instruction)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Dosage</span>
                                <span>{{ $prescriptionItem->dosage_instruction }}</span>
                            </div>
                        @endif
                        @if ($prescriptionItem->duration)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Duration</span>
                                <span>{{ $prescriptionItem->duration }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Illness Category</span>
                            <span>{{ $prescriptionItem->illnessCategory?->name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Product</span>
                            <span>{{ $prescriptionItem->product?->brand_name ?? '—' }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Prescription</span>
                            <span><a href="{{ route('admin.prescriptions.show', $prescriptionItem->prescription) }}" class="text-primary">#{{ $prescriptionItem->prescription?->patient_ref_code ?? $prescriptionItem->prescription_id }}</a></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Doctor</span>
                            <span>{{ $prescriptionItem->prescription?->doctor?->user?->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-pills fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Item #{{ $prescriptionItem->id }}</span>
                <span class="text-primary fw-normal">details</span>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Prescription Details</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient Ref</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ route('admin.prescriptions.show', $prescriptionItem->prescription) }}" class="text-primary">{{ $prescriptionItem->prescription?->patient_ref_code ?? '#' . $prescriptionItem->prescription_id }}</a></td>
                                    <td>{{ $prescriptionItem->prescription?->doctor?->user?->name ?? '—' }}</td>
                                    <td>
                                        @php $sColors = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                                        <span class="badge {{ $sColors[$prescriptionItem->prescription?->parsed_status] ?? 'bg-warning-subtle text-warning' }}">{{ $prescriptionItem->prescription?->parsed_status ?? 'pending' }}</span>
                                    </td>
                                    <td>{{ $prescriptionItem->created_at->format('d/m/Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

