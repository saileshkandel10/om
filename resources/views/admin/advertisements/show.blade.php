@extends('layouts.admin')

@section('title', 'Advertisement: ' . $advertisement->title)

@push('styles')
<style>
    .banner-full-display {
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center mb-3">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-ad text-brand me-1"></i> {{ $advertisement->title }}
                    </h4>
                    <p class="text-muted small mb-0">
                        Marketing &amp; Campaigns &gt; <a href="{{ route('admin.advertisements.index') }}" class="text-brand text-decoration-underline">Advertisements</a> &gt; #{{ $advertisement->id }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.advertisements.edit', $advertisement) }}" class="btn btn-sm btn-brand">
                        <i class="las la-edit me-1"></i> Edit Banner
                    </a>
                    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Banner Render Preview --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-eye text-brand me-1"></i> Active Banner Display
                    </h5>
                    <div>
                        @if ($advertisement->isCurrentlyActive())
                            <span class="badge bg-success-subtle text-success fs-12 px-3 py-1 fw-bold">
                                <i class="las la-broadcast-tower me-1"></i> Live &amp; Broadcasting
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary fs-12 px-3 py-1 fw-bold">
                                <i class="las la-pause me-1"></i> Inactive / Off Schedule
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="banner-full-display mx-auto" style="max-width: 720px; background: {{ $advertisement->background_color ?: 'linear-gradient(135deg, #CD0B6F 0%, #830444 100%)' }}; color: {{ $advertisement->text_color ?: '#FFFFFF' }};">
                        @if ($advertisement->type === 'image' && $advertisement->image_url)
                            <img src="{{ $advertisement->image_url }}" alt="{{ $advertisement->title }}" class="img-fluid w-100" style="max-height: 260px; object-fit: cover;">
                        @endif

                        <div class="p-4 text-start d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-white text-dark fs-10 px-2 py-1 shadow-sm">SPONSORED / ANNOUNCEMENT</span>
                                    <span class="badge bg-white-subtle text-white fs-11">Role: {{ strtoupper($advertisement->target_role) }}</span>
                                </div>
                                <h4 class="fw-bold mb-2 fs-20">{{ $advertisement->title }}</h4>
                                @if ($advertisement->description)
                                    <p class="fs-13 mb-3 opacity-90" style="max-width: 600px;">{{ $advertisement->description }}</p>
                                @endif
                            </div>
                            @if ($advertisement->cta_text)
                                <div class="mt-2">
                                    <a href="{{ $advertisement->cta_action ?: '#' }}" target="_blank" class="btn btn-sm fw-bold px-4 py-2 shadow-sm rounded-pill" style="background-color: {{ $advertisement->button_color ?: '#FFFFFF' }}; color: {{ $advertisement->button_text_color ?: '#CD0B6F' }};">
                                        {{ $advertisement->cta_text }} <i class="las la-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Specifications and Analytics Row --}}
    <div class="row g-3">
        {{-- Analytics Metrics --}}
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-chart-bar text-brand me-1"></i> Engagement &amp; Performance
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Impressions</span>
                                <h3 class="mb-0 fw-bold text-primary">{{ number_format($advertisement->impressions_count) }}</h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Clicks</span>
                                <h3 class="mb-0 fw-bold text-warning">{{ number_format($advertisement->clicks_count) }}</h3>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-brand-soft rounded-3">
                                <span class="text-brand fs-11 text-uppercase fw-bold d-block">Click-Through Rate (CTR)</span>
                                <h2 class="mb-0 fw-bold text-brand">{{ $advertisement->ctr }}%</h2>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="las la-info-circle text-primary me-1"></i> Impressions and clicks are automatically tracked in real-time as users interact with the banner on web and mobile dashboards.
                    </div>
                </div>
            </div>
        </div>

        {{-- Configuration Specifications --}}
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-list-alt text-brand me-1"></i> Banner Specifications
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 30%;">Target Audience</td>
                                    <td>
                                        @php
                                            $roleColor = match(strtolower($advertisement->target_role)) {
                                                'patient', 'customer' => 'success',
                                                'doctor' => 'info',
                                                'pharmacy' => 'warning',
                                                'shareholder' => 'purple',
                                                default => 'primary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleColor }}-subtle text-{{ $roleColor }} px-2 py-1 fs-12 fw-semibold">
                                            {{ strtoupper($advertisement->target_role) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Advertisement Type</td>
                                    <td>{{ ucfirst($advertisement->type) }} Banner</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">CTA Destination</td>
                                    <td>
                                        @if ($advertisement->cta_action)
                                            <a href="{{ $advertisement->cta_action }}" target="_blank" class="text-brand text-decoration-underline fs-12">
                                                {{ $advertisement->cta_action }} <i class="las la-external-link-alt ms-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">None (Informational only)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Schedule Window</td>
                                    <td>
                                        @if ($advertisement->start_date && $advertisement->end_date)
                                            {{ $advertisement->start_date->format('M d, Y') }} &rarr; {{ $advertisement->end_date->format('M d, Y') }}
                                        @elseif ($advertisement->start_date)
                                            From {{ $advertisement->start_date->format('M d, Y') }}
                                        @elseif ($advertisement->end_date)
                                            Until {{ $advertisement->end_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">Permanent / No schedule restriction</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Display Priority Order</td>
                                    <td>{{ $advertisement->display_order }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Created Date</td>
                                    <td>{{ $advertisement->created_at->format('M d, Y (h:i A)') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <form method="POST" action="{{ route('admin.advertisements.toggle-active', $advertisement) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                {{ $advertisement->is_active ? 'Deactivate Banner' : 'Activate Banner' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.advertisements.edit', $advertisement) }}" class="btn btn-sm btn-brand">
                            <i class="las la-edit me-1"></i> Edit Configuration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
