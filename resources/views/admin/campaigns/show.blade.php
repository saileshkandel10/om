@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Campaign Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.campaigns.index') }}">Campaigns</a></li>
                        <li class="breadcrumb-item active">{{ $campaign->name }}</li>
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
                                        <i class="las la-megaphone fs-1"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $campaign->name }}</h5>
                                    <p class="text-muted mb-0">{{ $campaign->campaign_type ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="iconoir-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php $sColors = ['active' => 'bg-success-subtle text-success', 'inactive' => 'bg-secondary-subtle text-secondary', 'draft' => 'bg-warning-subtle text-warning', 'completed' => 'bg-info-subtle text-info']; @endphp
                                    <span class="badge {{ $sColors[$campaign->status] ?? 'bg-warning-subtle text-warning' }}">{{ ucfirst($campaign->status) }}</span>
                                </div>
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="las la-tag fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Type :</span>
                                    <span>{{ ucfirst($campaign->campaign_type) }}</span>
                                </div>
                                @if ($campaign->start_date)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="las la-calendar-start fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Start :</span>
                                        <span>{{ $campaign->start_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                                @if ($campaign->end_date)
                                    <div class="text-body mb-2 d-flex align-items-center">
                                        <i class="las la-calendar-end fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">End :</span>
                                        <span>{{ $campaign->end_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-megaphone fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $campaign->name }}</span>
                <span class="text-primary fw-normal">campaign details</span>
            </div>
            @if ($campaign->description)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Description</h4>
                    </div>
                    <div class="card-body pt-0">
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($campaign->description)) !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

