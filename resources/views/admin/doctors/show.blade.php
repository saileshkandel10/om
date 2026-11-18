@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Doctor Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.doctors.index') }}">Doctors</a></li>
                        <li class="breadcrumb-item active">{{ $doctor->user?->name ?? $doctor->registration_no }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body p-4 rounded text-center img-bg"></div>
                <div class="position-relative">
                    <div class="shape overflow-hidden text-card-bg">
                        <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
                        </svg>
                    </div>
                </div>
                <div class="card-body mt-n6">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                        <i class="las la-stethoscope fs-1"></i>
                                    </div>
                                    @if ($doctor->status === 'active')
                                        <div class="position-absolute top-50 start-100 translate-middle">
                                            <span class="rounded-circle border border-3 border-white d-inline-block bg-success" style="width: 18px; height: 18px;"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $doctor->user?->name ?? 'N/A' }}</h5>
                                    <p class="text-muted mb-0">Reg: {{ $doctor->registration_no }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="iconoir-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php $sColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary', 'suspended' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                                    <span class="badge {{ $sColors[$doctor->status] ?? 'bg-warning-subtle text-warning' }}">{{ ucfirst($doctor->status) }}</span>
                                </div>
                                @if ($doctor->specialty)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-stethoscope fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Specialty :</span>
                                        <span>{{ $doctor->specialty }}</span>
                                    </div>
                                @endif
                                @if ($doctor->hospital_clinic)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-hospital fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Hospital :</span>
                                        <span>{{ $doctor->hospital_clinic }}</span>
                                    </div>
                                @endif
                                @if ($doctor->user?->email)
                                    <div class="text-muted mb-2 d-flex align-items-center">
                                        <i class="iconoir-mail-out fs-20 me-1"></i>
                                        <span class="text-body fw-semibold me-1">Email :</span>
                                        <span>{{ $doctor->user->email }}</span>
                                    </div>
                                @endif
                                @if ($doctor->approved_at)
                                    <div class="text-body d-flex align-items-center">
                                        <i class="iconoir-calendar fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Approved :</span>
                                        <span>{{ $doctor->approved_at->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-primary btn-sm"><i class="las la-edit me-1"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" class="d-inline" onsubmit="return confirm('Delete this doctor?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="las la-trash-alt me-1"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-chart-bar fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Dr. {{ $doctor->user?->name ?? 'N/A' }}</span>
                <span class="text-primary fw-normal">prescriptions overview</span>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-prescription-bottle fs-24 align-self-center text-info me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Total Prescriptions</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $doctor->prescriptions->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($doctor->prescriptions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Recent Prescriptions</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-primary btn-sm"><i class="las la-eye me-1"></i> View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient Ref</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                        <th>Confidence</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($doctor->prescriptions as $prescription)
                                        <tr>
                                            <td><a href="{{ route('admin.prescriptions.show', $prescription) }}" class="text-primary">{{ $prescription->patient_ref_code ?? '#' . $prescription->id }}</a></td>
                                            <td>
                                                @php $pClasses = ['completed' => 'bg-success-subtle text-success', 'processing' => 'bg-info-subtle text-info', 'failed' => 'bg-danger-subtle text-danger', 'pending' => 'bg-warning-subtle text-warning']; @endphp
                                                <span class="badge {{ $pClasses[$prescription->parsed_status] ?? 'bg-warning-subtle text-warning' }}">{{ $prescription->parsed_status ?? 'pending' }}</span>
                                            </td>
                                            <td>{{ $prescription->items_count ?? $prescription->items?->count() ?? 0 }}</td>
                                            <td>{{ $prescription->confidence_score ? number_format($prescription->confidence_score, 1) . '%' : '—' }}</td>
                                            <td>{{ $prescription->created_at->format('d/m/Y') }}</td>
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

