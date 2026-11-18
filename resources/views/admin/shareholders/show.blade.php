@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Shareholder Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.shareholders.index') }}">Shareholders</a></li>
                        <li class="breadcrumb-item active">{{ $shareholder->shareholder_name }}</li>
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
                                    <div class="rounded-circle bg-black text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                        {{ strtoupper(substr($shareholder->shareholder_name, 0, 2)) }}
                                    </div>
                                    @if ($shareholder->status === 'active')
                                        <div class="position-absolute top-50 start-100 translate-middle">
                                            <span class="rounded-circle border border-3 border-white d-inline-block bg-success" style="width: 18px; height: 18px;"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $shareholder->shareholder_name }}</h5>
                                    <p class="text-muted mb-0">BOID: {{ $shareholder->boid }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="iconoir-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php $sColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary']; @endphp
                                    <span class="badge {{ $sColors[$shareholder->status] ?? 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($shareholder->status) }}</span>
                                </div>
                                @if ($shareholder->email)
                                    <div class="text-muted mb-2 d-flex align-items-center">
                                        <i class="iconoir-mail-out fs-20 me-1"></i>
                                        <span class="text-body fw-semibold me-1">Email :</span>
                                        <span>{{ $shareholder->email }}</span>
                                    </div>
                                @endif
                                @if ($shareholder->mobile)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-phone fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Phone :</span>
                                        <span>{{ $shareholder->mobile }}</span>
                                    </div>
                                @endif
                                @if ($shareholder->source_note)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="iconoir-info-circle fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Source :</span>
                                        <span>{{ $shareholder->source_note }}</span>
                                    </div>
                                @endif
                                <div class="text-body d-flex align-items-center">
                                    <i class="iconoir-calendar fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Created :</span>
                                    <span>{{ $shareholder->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('admin.shareholders.edit', $shareholder) }}" class="btn btn-primary btn-sm"><i class="las la-edit me-1"></i> Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-users fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $shareholder->shareholder_name }}'s</span>
                <span class="text-primary fw-normal">shareholder overview</span>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-link fs-24 align-self-center text-info me-2"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="text-dark mb-0 fw-semibold fs-13">Linked Accounts</p>
                                    <h3 class="mt-1 mb-0 fs-18 fw-bold">{{ $shareholder->accounts->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($shareholder->accounts->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Linked User Accounts ({{ $shareholder->accounts->count() }})</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>BOID</th>
                                        <th>Shares Owned</th>
                                        <th>Verification</th>
                                        <th>Verified At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shareholder->accounts as $account)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="thumb-sm rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;">
                                                        {{ strtoupper(substr($account->user?->name ?? '?', 0, 2)) }}
                                                    </div>
                                                    <span class="ms-2">{{ $account->user?->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td><code>{{ $account->boid }}</code></td>
                                            <td>{{ number_format($account->shares_owned ?? 0) }}</td>
                                            <td>
                                                @php $vColors = ['verified' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger']; @endphp
                                                <span class="badge {{ $vColors[$account->verification_status] ?? 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($account->verification_status) }}</span>
                                            </td>
                                            <td>{{ $account->verified_at?->format('d/m/Y') ?? '—' }}</td>
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
                        <i class="las la-link fs-2 d-block mb-2"></i>
                        <span>No linked user accounts yet.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

