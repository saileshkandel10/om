@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Redemption Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.redemptions.index') }}">Redemptions</a></li>
                        <li class="breadcrumb-item active">Redemption #{{ $redemption->id }}</li>
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
                        <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                            <i class="las la-gift fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Redemption #{{ $redemption->id }}</h5>
                        <p class="text-muted mb-1">{{ $redemption->redemption_type ?? '—' }}</p>
                        <div class="mt-2">
                            <span class="badge bg-purple-subtle text-purple font-monospace fs-14 px-3 py-2 border border-purple">
                                <i class="las la-ticket-alt me-1"></i>{{ $redemption->redemption_code ?? 'RDM-'.$redemption->id }}
                            </span>
                            <div class="small text-muted mt-1"><i class="las la-gift text-warning me-1"></i>Lucky Draw Code</div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Redemption Code</span>
                            <span class="fw-bold font-monospace">{{ $redemption->redemption_code ?? 'RDM-'.$redemption->id }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            @php $sColors = ['approved' => 'bg-success-subtle text-success', 'pending' => 'bg-warning-subtle text-warning', 'rejected' => 'bg-danger-subtle text-danger', 'completed' => 'bg-info-subtle text-info']; @endphp
                            <span class="badge {{ $sColors[$redemption->status] ?? 'bg-warning-subtle text-warning' }}">{{ $redemption->status ?? 'pending' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Type</span>
                            <span class="badge bg-info-subtle text-info">{{ $redemption->redemption_type ?? '—' }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Points Used</span>
                            <span class="fw-bold fs-5">{{ number_format($redemption->points_used ?? 0, 2) }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Amount Value</span>
                            <span class="fw-bold">Rs {{ number_format($redemption->amount_value ?? 0, 2) }}</span>
                        </div>
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">User</span>
                            <span class="fw-medium">{{ $redemption->user?->name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Email</span>
                            <span>{{ $redemption->user?->email ?? '—' }}</span>
                        </div>
                        @if ($redemption->requested_at)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Requested</span>
                                <span>{{ $redemption->requested_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        @if ($redemption->approved_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Approved</span>
                                <span>{{ $redemption->approved_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-gift fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Redemption #{{ $redemption->id }}</span>
                <span class="text-primary fw-normal">update status</span>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-1 fs-13">Points Used</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($redemption->points_used ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-1 fs-13">Amount Value</p>
                            <h3 class="mb-0 fw-bold text-success">Rs {{ number_format($redemption->amount_value ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Update Redemption Status</h4>
                </div>
                <div class="card-body pt-0">
                    <form method="POST" action="{{ route('admin.redemptions.update', $redemption) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="pending" @selected($redemption->status === 'pending')>Pending</option>
                                <option value="approved" @selected($redemption->status === 'approved')>Approve</option>
                                <option value="rejected" @selected($redemption->status === 'rejected')>Reject</option>
                                <option value="completed" @selected($redemption->status === 'completed')>Complete</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="las la-save me-1"></i> Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

