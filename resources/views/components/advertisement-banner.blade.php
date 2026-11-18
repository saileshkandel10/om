@php
    $targetRole = $role ?? 'all';
    if ($targetRole === 'all' && auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('doctor')) {
            $targetRole = 'doctor';
        } elseif ($user->hasRole('shareholder')) {
            $targetRole = 'shareholder';
        } elseif ($user->hasRole('pharmacy')) {
            $targetRole = 'pharmacy';
        } elseif ($user->hasRole('customer') || $user->hasRole('patient')) {
            $targetRole = 'patient';
        }
    }

    $activeBanners = \App\Models\Advertisement::active()
        ->forRole($targetRole)
        ->orderBy('display_order', 'asc')
        ->orderByDesc('id')
        ->get();

    $carouselId = 'adBannerCarousel_' . uniqid();
@endphp

@if ($activeBanners->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div id="{{ $carouselId }}" class="carousel slide shadow-sm" data-bs-ride="carousel" data-bs-interval="4500" style="border-radius: 16px; overflow: hidden;">
                {{-- Carousel Indicators --}}
                @if ($activeBanners->count() > 1)
                    <div class="carousel-indicators mb-2">
                        @foreach ($activeBanners as $idx => $banner)
                            <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $idx }}" class="{{ $idx === 0 ? 'active' : '' }}" aria-current="{{ $idx === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $idx + 1 }}" style="width: 8px; height: 8px; border-radius: 50%;"></button>
                        @endforeach
                    </div>
                @endif

                {{-- Carousel Items --}}
                <div class="carousel-inner">
                    @foreach ($activeBanners as $idx => $banner)
                        <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}" data-ad-id="{{ $banner->id }}">
                            <div class="card border-0 mb-0" style="background: {{ $banner->background_color ?: 'linear-gradient(135deg, #CD0B6F 0%, #830444 100%)' }}; color: {{ $banner->text_color ?: '#FFFFFF' }}; min-height: 120px;">
                                @if ($banner->type === 'image' && $banner->image_url)
                                    <div class="position-relative">
                                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="d-block w-100" style="max-height: 200px; object-fit: cover;">
                                        @if ($banner->cta_text && $banner->cta_action)
                                            <a href="{{ $banner->cta_action }}" target="_blank" onclick="trackAdClick({{ $banner->id }})" class="btn btn-sm fw-bold px-3 py-1 shadow-sm rounded-pill position-absolute bottom-0 end-0 m-3" style="background-color: {{ $banner->button_color ?: '#FFFFFF' }}; color: {{ $banner->button_text_color ?: '#CD0B6F' }}; z-index: 2;">
                                                {{ $banner->cta_text }} <i class="las la-arrow-right ms-1"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative">
                                        <div class="pe-md-4 mb-2 mb-md-0">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-white text-dark fs-10 px-2 py-1 shadow-sm">SPONSORED / NOTICE</span>
                                                <i class="las la-bullhorn fs-16 opacity-75"></i>
                                            </div>
                                            <h5 class="fw-bold mb-1 fs-16 fs-md-18" style="color: inherit;">{{ $banner->title }}</h5>
                                            @if ($banner->description)
                                                <p class="mb-0 fs-12 opacity-90" style="max-width: 600px; color: inherit;">{{ $banner->description }}</p>
                                            @endif
                                        </div>
                                        @if ($banner->cta_text)
                                            <div class="flex-shrink-0 mt-2 mt-md-0">
                                                <a href="{{ $banner->cta_action ?: '#' }}" target="_blank" onclick="trackAdClick({{ $banner->id }})" class="btn btn-sm fw-bold px-4 py-2 shadow-sm rounded-pill" style="background-color: {{ $banner->button_color ?: '#FFFFFF' }}; color: {{ $banner->button_text_color ?: '#CD0B6F' }}; font-size: 13px;">
                                                    {{ $banner->cta_text }} <i class="las la-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Controls --}}
                @if ($activeBanners->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev" style="width: 5%;">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next" style="width: 5%;">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @pushOnce('scripts')
    <script>
        function trackAdClick(adId) {
            if (!adId) return;
            fetch(`/api/v1/advertisements/${adId}/click`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.carousel-item[data-ad-id]').forEach(function(item) {
                const adId = item.getAttribute('data-ad-id');
                if (adId) {
                    fetch(`/api/v1/advertisements/${adId}/impression`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).catch(() => {});
                }
            });
        });
    </script>
    @endPushOnce
@endif
