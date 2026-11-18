@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Notification Details</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
                        <li class="breadcrumb-item active">Notification #{{ $notification->id }}</li>
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
                            <i class="las la-bell fs-1"></i>
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">Notification #{{ $notification->id }}</h5>
                        <p class="text-muted mb-0">{{ $notification->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Channel</span>
                            <span class="badge bg-info-subtle text-info">{{ $notification->channel ?? '—' }}</span>
                        </div>
                        @if ($notification->read_at)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Read Status</span>
                                <span class="badge bg-success-subtle text-success">Read</span>
                            </div>
                        @else
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Read Status</span>
                                <span class="badge bg-warning-subtle text-warning">Unread</span>
                            </div>
                        @endif
                        <hr>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Recipient</span>
                            <span class="fw-medium">{{ $notification->user?->name ?? '—' }}</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Email</span>
                            <span>{{ $notification->user?->email ?? '—' }}</span>
                        </div>
                        @if ($notification->read_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Read At</span>
                                <span>{{ $notification->read_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                <i class="las la-bell fs-5 text-primary"></i>
                <span class="text-primary fw-semibold">Notification #{{ $notification->id }}</span>
                <span class="text-primary fw-normal">content</span>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Title</h4>
                </div>
                <div class="card-body pt-0">
                    <p class="fw-semibold fs-5 mb-0">{{ $notification->title ?? '—' }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Message</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($notification->message ?? '')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

