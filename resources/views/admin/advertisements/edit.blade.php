@extends('layouts.admin')

@section('title', 'Edit Advertisement: ' . $advertisement->title)

@push('styles')
<style>
    .color-preset-chip {
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        margin: 3px;
        display: inline-flex;
        align-items: center;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }
    .color-preset-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }
    .color-preset-chip.active-preset {
        border-color: #000000 !important;
        transform: scale(1.03);
    }
    .live-preview-container {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 18px;
        padding: 20px;
        min-height: 240px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mobile-banner-card {
        width: 100%;
        max-width: 480px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
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
                        <i class="las la-edit text-brand me-1"></i> Edit Advertisement Banner
                    </h4>
                    <p class="text-muted small mb-0">
                        Marketing &amp; Campaigns &gt; <a href="{{ route('admin.advertisements.index') }}" class="text-brand text-decoration-underline">Advertisements</a> &gt; Edit #{{ $advertisement->id }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.advertisements.show', $advertisement) }}" class="btn btn-sm btn-outline-brand">
                        <i class="las la-eye me-1"></i> View Analytics
                    </a>
                    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Banners
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Form & Live Preview Row --}}
    <form method="POST" action="{{ route('admin.advertisements.update', $advertisement) }}" enctype="multipart/form-data" id="adForm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Form Column --}}
            <div class="col-lg-7 mb-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-brand-soft py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="las la-sliders-h text-brand me-1"></i> Advertisement Configuration
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3 p-3 rounded-3 small">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Advertisement Type Selector --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark fs-13">Advertisement Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline p-3 border rounded-3 flex-fill hover-bg" style="cursor: pointer;" onclick="document.getElementById('typeText').click();">
                                    <input class="form-check-input" type="radio" name="type" id="typeText" value="text" {{ old('type', $advertisement->type) === 'text' ? 'checked' : '' }} onchange="toggleTypeFields('text')">
                                    <label class="form-check-label fw-semibold" for="typeText">
                                        <i class="las la-font text-brand me-1"></i> Rich Text Advertisement
                                        <small class="d-block text-muted fw-normal">Custom styled background, typography &amp; CTA button.</small>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline p-3 border rounded-3 flex-fill hover-bg" style="cursor: pointer;" onclick="document.getElementById('typeImage').click();">
                                    <input class="form-check-input" type="radio" name="type" id="typeImage" value="image" {{ old('type', $advertisement->type) === 'image' ? 'checked' : '' }} onchange="toggleTypeFields('image')">
                                    <label class="form-check-label fw-semibold" for="typeImage">
                                        <i class="las la-image text-primary me-1"></i> Upload Image Banner
                                        <small class="d-block text-muted fw-normal">Upload 16:5 or 3:1 banner graphic.</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Basic Information --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark fs-13">Banner Headline / Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="titleInput" class="form-control" value="{{ old('title', $advertisement->title) }}" placeholder="e.g. Free Blood Sugar Checkup This Weekend" required oninput="updatePreview()">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark fs-13">Description / Tagline</label>
                                <textarea name="description" id="descInput" class="form-control" rows="2" placeholder="Brief promotional description..." oninput="updatePreview()">{{ old('description', $advertisement->description) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">CTA Button Label</label>
                                <input type="text" name="cta_text" id="ctaTextInput" class="form-control" value="{{ old('cta_text', $advertisement->cta_text) }}" placeholder="e.g. Learn More, Book Now, Claim Offer" oninput="updatePreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">CTA Destination Link / Action</label>
                                <input type="text" name="cta_action" id="ctaActionInput" class="form-control" value="{{ old('cta_action', $advertisement->cta_action) }}" placeholder="e.g. https://... or /patient/bills">
                            </div>
                        </div>

                        {{-- Image Upload Section --}}
                        <div id="imageSection" class="mb-4 p-3 border rounded-3 bg-light" style="display: {{ old('type', $advertisement->type) === 'image' ? 'block' : 'none' }};">
                            <label class="form-label fw-bold text-dark fs-13"><i class="las la-upload text-primary me-1"></i> Banner Graphic Image</label>
                            @if ($advertisement->image_url)
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">Current Image:</small>
                                    <img src="{{ $advertisement->image_url }}" alt="" class="img-fluid rounded border" style="max-height: 80px;">
                                </div>
                            @endif
                            <input type="file" name="image" id="imageInput" class="form-control" accept="image/*" onchange="previewUploadedImage(this)">
                            <small class="text-muted d-block mt-1">Leave empty to keep existing image. Recommended aspect ratio: <strong>16:5</strong> or <strong>3:1</strong>.</small>
                        </div>

                        {{-- Text Color Customization Section --}}
                        <div id="textSection" class="mb-4 p-3 border rounded-3 bg-brand-light" style="display: {{ old('type', $advertisement->type) === 'text' ? 'block' : 'none' }};">
                            <h6 class="fw-bold text-dark mb-2"><i class="las la-palette text-brand me-1"></i> Color Presets &amp; Custom Palette</h6>
                            
                            {{-- Color Presets --}}
                            <div class="mb-3">
                                <label class="form-label fs-12 text-muted fw-semibold">Quick Style Presets:</label>
                                <div>
                                    @foreach ($presets as $key => $preset)
                                        <span class="color-preset-chip" style="background: {{ $preset['bg'] }}; color: {{ $preset['text'] }};"
                                              onclick="applyColorPreset('{{ $preset['bg'] }}', '{{ $preset['text'] }}', '{{ $preset['btn_bg'] }}', '{{ $preset['btn_text'] }}', this)">
                                            {{ $preset['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-12 fw-semibold">Background (CSS / Hex)</label>
                                    <input type="text" name="background_color" id="bgColorInput" class="form-control form-control-sm" value="{{ old('background_color', $advertisement->background_color ?: 'linear-gradient(135deg, #CD0B6F 0%, #830444 100%)') }}" oninput="updatePreview()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 fw-semibold">Text / Heading Color</label>
                                    <div class="d-flex gap-2">
                                        <input type="color" id="textColorPicker" class="form-control form-control-color form-control-sm" value="{{ old('text_color', $advertisement->text_color ?: '#FFFFFF') }}" onchange="document.getElementById('textColorInput').value = this.value; updatePreview();">
                                        <input type="text" name="text_color" id="textColorInput" class="form-control form-control-sm" value="{{ old('text_color', $advertisement->text_color ?: '#FFFFFF') }}" oninput="document.getElementById('textColorPicker').value = this.value; updatePreview();">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 fw-semibold">Button Background</label>
                                    <div class="d-flex gap-2">
                                        <input type="color" id="btnColorPicker" class="form-control form-control-color form-control-sm" value="{{ old('button_color', $advertisement->button_color ?: '#FFFFFF') }}" onchange="document.getElementById('btnColorInput').value = this.value; updatePreview();">
                                        <input type="text" name="button_color" id="btnColorInput" class="form-control form-control-sm" value="{{ old('button_color', $advertisement->button_color ?: '#FFFFFF') }}" oninput="document.getElementById('btnColorPicker').value = this.value; updatePreview();">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 fw-semibold">Button Text Color</label>
                                    <div class="d-flex gap-2">
                                        <input type="color" id="btnTextColorPicker" class="form-control form-control-color form-control-sm" value="{{ old('button_text_color', $advertisement->button_text_color ?: '#CD0B6F') }}" onchange="document.getElementById('btnTextColorInput').value = this.value; updatePreview();">
                                        <input type="text" name="button_text_color" id="btnTextColorInput" class="form-control form-control-sm" value="{{ old('button_text_color', $advertisement->button_text_color ?: '#CD0B6F') }}" oninput="document.getElementById('btnTextColorPicker').value = this.value; updatePreview();">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Targeting & Scheduling --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Target Audience / Role <span class="text-danger">*</span></label>
                                <select name="target_role" id="roleInput" class="form-select" required onchange="updatePreview()">
                                    <option value="all" @selected(old('target_role', $advertisement->target_role) === 'all')>All Users (Global)</option>
                                    <option value="patient" @selected(old('target_role', $advertisement->target_role) === 'patient' || old('target_role', $advertisement->target_role) === 'customer')>Patient / Customer</option>
                                    <option value="doctor" @selected(old('target_role', $advertisement->target_role) === 'doctor')>Doctor</option>
                                    <option value="pharmacy" @selected(old('target_role', $advertisement->target_role) === 'pharmacy')>Pharmacy</option>
                                    <option value="shareholder" @selected(old('target_role', $advertisement->target_role) === 'shareholder')>Shareholder</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Display Priority Order</label>
                                <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $advertisement->display_order) }}" min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Schedule Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $advertisement->start_date?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Schedule End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $advertisement->end_date?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-2 bg-light rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" name="is_active" id="isActiveSwitch" value="1" @checked(old('is_active', $advertisement->is_active))>
                                    <label class="form-check-label fw-semibold text-dark" for="isActiveSwitch">
                                        Banner Active &amp; Eligible for Display
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.advertisements.index') }}" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-brand px-4 py-2 fw-semibold">
                                <i class="las la-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Interactive Preview Column --}}
            <div class="col-lg-5 mb-3">
                <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top: 80px;">
                    <div class="card-header bg-brand-soft py-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="las la-mobile text-brand me-1"></i> Live Dashboard Preview
                        </h5>
                        <span class="badge bg-white text-brand border border-brand-subtle px-2 py-1 fs-11" id="previewRoleBadge">
                            Target: {{ strtoupper($advertisement->target_role) }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="live-preview-container">
                            <div class="mobile-banner-card" id="previewCard" style="background: {{ $advertisement->background_color ?: 'linear-gradient(135deg, #CD0B6F 0%, #830444 100%)' }}; color: {{ $advertisement->text_color ?: '#ffffff' }};">
                                <div id="imagePreviewBox" style="display: {{ $advertisement->type === 'image' ? 'block' : 'none' }}; height: 160px; background-size: cover; background-position: center; background-image: url('{{ $advertisement->image_url }}');"></div>
                                <div id="textPreviewContent" class="p-4 d-flex flex-column justify-content-between" style="min-height: 140px;">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-white text-dark fs-10 px-2 py-1 shadow-sm">SPONSORED / NOTICE</span>
                                            <i class="las la-bullhorn fs-18 opacity-75"></i>
                                        </div>
                                        <h5 class="fw-bold mb-1 fs-16" id="previewTitle">{{ $advertisement->title }}</h5>
                                        <p class="fs-12 mb-3 opacity-90" id="previewDesc">{{ $advertisement->description }}</p>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm fw-bold px-3 py-1 shadow-sm rounded-pill" id="previewBtn" style="background-color: {{ $advertisement->button_color ?: '#ffffff' }}; color: {{ $advertisement->button_text_color ?: '#CD0B6F' }}; font-size: 12px;">
                                            <span id="previewBtnText">{{ $advertisement->cta_text ?: 'Learn More' }}</span> <i class="las la-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Preview Wallet Context --}}
                        <div class="mt-3 p-3 bg-white border rounded-3 shadow-sm opacity-75">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 rounded-circle bg-brand-soft text-brand me-2">
                                        <i class="las la-wallet fs-18"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block fs-11">LOYALTY WALLET BALANCE</small>
                                        <strong class="text-dark fs-14">NPR 2,500.00</strong>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success fs-11">500 pts</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleTypeFields(type) {
        if (type === 'image') {
            document.getElementById('imageSection').style.display = 'block';
            document.getElementById('textSection').style.display = 'none';
        } else {
            document.getElementById('imageSection').style.display = 'none';
            document.getElementById('textSection').style.display = 'block';
        }
        updatePreview();
    }

    function applyColorPreset(bg, text, btnBg, btnText, chip) {
        document.getElementById('bgColorInput').value = bg;
        document.getElementById('textColorInput').value = text;
        document.getElementById('textColorPicker').value = text.startsWith('#') ? text : '#FFFFFF';
        document.getElementById('btnColorInput').value = btnBg;
        document.getElementById('btnColorPicker').value = btnBg.startsWith('#') ? btnBg : '#FFFFFF';
        document.getElementById('btnTextColorInput').value = btnText;
        document.getElementById('btnTextColorPicker').value = btnText.startsWith('#') ? btnText : '#000000';

        document.querySelectorAll('.color-preset-chip').forEach(c => c.classList.remove('active-preset'));
        if (chip) chip.classList.add('active-preset');

        updatePreview();
    }

    function previewUploadedImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgBox = document.getElementById('imagePreviewBox');
                imgBox.style.backgroundImage = `url('${e.target.result}')`;
                imgBox.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updatePreview() {
        const type = document.querySelector('input[name="type"]:checked')?.value || 'text';
        const title = document.getElementById('titleInput').value || 'Advertisement Title';
        const desc = document.getElementById('descInput').value || '';
        const ctaText = document.getElementById('ctaTextInput').value || 'Learn More';
        const role = document.getElementById('roleInput').value || 'all';

        const bg = document.getElementById('bgColorInput').value || '#1E3A5F';
        const textColor = document.getElementById('textColorInput').value || '#FFFFFF';
        const btnColor = document.getElementById('btnColorInput').value || '#FFFFFF';
        const btnTextColor = document.getElementById('btnTextColorInput').value || '#CD0B6F';

        document.getElementById('previewTitle').innerText = title;
        document.getElementById('previewDesc').innerText = desc;
        document.getElementById('previewBtnText').innerText = ctaText;
        document.getElementById('previewRoleBadge').innerText = 'Target: ' + role.toUpperCase();

        const previewCard = document.getElementById('previewCard');
        const previewBtn = document.getElementById('previewBtn');
        const imgBox = document.getElementById('imagePreviewBox');

        if (type === 'image') {
            imgBox.style.display = 'block';
            previewCard.style.background = '#ffffff';
            previewCard.style.color = '#1E3A5F';
            previewBtn.style.backgroundColor = '#CD0B6F';
            previewBtn.style.color = '#ffffff';
        } else {
            imgBox.style.display = 'none';
            previewCard.style.background = bg;
            previewCard.style.color = textColor;
            previewBtn.style.backgroundColor = btnColor;
            previewBtn.style.color = btnTextColor;
        }

        if (!ctaText.trim()) {
            previewBtn.style.display = 'none';
        } else {
            previewBtn.style.display = 'inline-block';
        }
    }

    document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
