@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Notice Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.notices.index') }}">Notices</a></li>
                        <li class="breadcrumb-item active">{{ $notice->title }}</li>
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
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 128px; height: 128px;">
                                        <i class="las la-bullhorn fs-1"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-3 align-self-end">
                                    <h5 class="m-0 fs-3 fw-bold">{{ $notice->title }}</h5>
                                    <p class="text-muted mb-0">{{ $notice->notice_type ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="iconoir-check-circle fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Status :</span>
                                    @php $sColors = ['published' => 'bg-success-subtle text-success', 'draft' => 'bg-secondary-subtle text-secondary', 'archived' => 'bg-warning-subtle text-warning']; @endphp
                                    <span class="badge {{ $sColors[$notice->status] ?? 'bg-secondary-subtle text-secondary' }}">{{ ucfirst($notice->status) }}</span>
                                </div>
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="las la-tag fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Type :</span>
                                    <span>{{ ucfirst($notice->notice_type) }}</span>
                                </div>
                                <div class="text-body mb-2 d-flex align-items-center">
                                    <i class="las la-users fs-20 me-1 text-muted"></i>
                                    <span class="text-body fw-semibold me-1">Audience :</span>
                                    <span>{{ ucfirst($notice->audience_type) }}</span>
                                </div>
                                @if ($notice->published_at)
                                    <div class="text-body d-flex align-items-center">
                                        <i class="las la-calendar fs-20 me-1 text-muted"></i>
                                        <span class="text-body fw-semibold me-1">Published :</span>
                                        <span>{{ $notice->published_at->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-primary btn-sm"><i class="las la-edit me-1"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" class="d-inline" onsubmit="return confirm('Delete this notice?');">
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
                <i class="las la-bullhorn fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">{{ $notice->title }}</span>
                <span class="text-primary fw-normal">notice body</span>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Notice Body</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($notice->body)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

