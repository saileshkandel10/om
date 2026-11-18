@extends('layouts.admin')

@section('title', 'Pharmacist Quiz Settings')

@push('styles')
<style>
    .preset-card-btn {
        cursor: pointer;
        padding: 12px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .preset-card-btn:hover {
        border-color: var(--om-brand-pink);
        background-color: var(--om-brand-light-pink);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(205, 11, 111, 0.1);
    }
    .setting-group-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 20px;
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
                        <i class="las la-sliders-h text-brand me-1"></i> Pharmacist Quiz System Settings
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.index') }}" class="text-brand text-decoration-underline">Pharmacist Quiz</a> &gt; Global Rules &amp; Assessment Settings
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-sm btn-outline-brand">
                        <i class="las la-database me-1"></i> Question Bank ({{ $stats['total_questions'] }})
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-folder me-1"></i> Categories
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-graduation-cap me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-check-circle me-1 fs-18 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="las la-exclamation-triangle me-1 fs-18 align-middle"></i> Please check the form errors below.
            <ul class="mb-0 mt-1 small ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Quick Presets --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-2 mb-md-0">
                            <strong class="text-dark fs-13"><i class="las la-magic text-brand me-1"></i> Quick Configuration Presets:</strong>
                            <small class="text-muted d-block">Click any preset profile to automatically pre-fill recommended parameters below.</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="preset-card-btn" onclick="applyPreset(5, 5, 60, true, true, true, true, 5, 25)">
                                <strong class="fs-12 text-brand d-block"><i class="las la-bolt me-1"></i> Quick Flash (5 Qs)</strong>
                                <small class="text-muted">5 Mins &bull; 60% Pass &bull; 25 pts</small>
                            </div>
                            <div class="preset-card-btn" onclick="applyPreset(10, 10, 70, true, true, true, true, 3, 50)">
                                <strong class="fs-12 text-brand d-block"><i class="las la-certificate me-1"></i> Standard CME (10 Qs)</strong>
                                <small class="text-muted">10 Mins &bull; 70% Pass &bull; 50 pts</small>
                            </div>
                            <div class="preset-card-btn" onclick="applyPreset(20, 20, 80, true, true, true, false, 1, 100)">
                                <strong class="fs-12 text-brand d-block"><i class="las la-award me-1"></i> Board Exam (20 Qs)</strong>
                                <small class="text-muted">20 Mins &bull; 80% Pass &bull; 100 pts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Form --}}
    <form method="POST" action="{{ route('admin.pharmacist-quiz.settings.update') }}" id="settingsForm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Main Parameters Column --}}
            <div class="col-lg-8">
                {{-- 1. Assessment Parameters --}}
                <div class="setting-group-box shadow-sm">
                    <h5 class="fw-bold text-dark fs-15 mb-3 border-bottom pb-2">
                        <i class="las la-brain text-brand me-1"></i> 1. Assessment &amp; Evaluation Parameters
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark fs-13">Questions per Quiz <span class="text-danger">*</span></label>
                            <input type="number" name="questions_per_quiz" id="inputQuestionsPerQuiz" class="form-control"
                                   value="{{ old('questions_per_quiz', $settings->questions_per_quiz) }}" min="1" max="100" required oninput="updateLiveSummary()">
                            <small class="text-muted">Number of questions served in each attempt.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark fs-13">Time Limit (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="time_limit_minutes" id="inputTimeLimit" class="form-control"
                                   value="{{ old('time_limit_minutes', $settings->time_limit_minutes) }}" min="1" max="180" required oninput="updateLiveSummary()">
                            <small class="text-muted">Countdown timer per attempt (e.g. 10 mins).</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark fs-13">Passing Percentage (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="passing_percentage" id="inputPassingPercentage" class="form-control"
                                       value="{{ old('passing_percentage', $settings->passing_percentage) }}" min="1" max="100" step="0.5" required oninput="updateLiveSummary()">
                                <span class="input-group-text bg-light">%</span>
                            </div>
                            <small class="text-muted">Minimum score percentage required to pass.</small>
                        </div>
                    </div>
                </div>

                {{-- 2. Randomization & Feedback Toggles --}}
                <div class="setting-group-box shadow-sm">
                    <h5 class="fw-bold text-dark fs-15 mb-3 border-bottom pb-2">
                        <i class="las la-random text-brand me-1"></i> 2. Randomization &amp; Educational Feedback
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong class="d-block text-dark fs-13">Randomize Questions</strong>
                                    <small class="text-muted">Shuffle question order for each attempt to prevent rote memorization.</small>
                                </div>
                                <div class="form-check form-switch ms-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_randomized_questions" id="switchRandQ" value="1"
                                           @checked(old('is_randomized_questions', $settings->is_randomized_questions)) onchange="updateLiveSummary()">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong class="d-block text-dark fs-13">Randomize Answer Choices (A,B,C,D)</strong>
                                    <small class="text-muted">Shuffle MCQ option positions dynamically for each question.</small>
                                </div>
                                <div class="form-check form-switch ms-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_randomized_options" id="switchRandOpt" value="1"
                                           @checked(old('is_randomized_options', $settings->is_randomized_options)) onchange="updateLiveSummary()">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong class="d-block text-dark fs-13">Show Clinical Explanations</strong>
                                    <small class="text-muted">Display rationales &amp; correct answers only after quiz submission.</small>
                                </div>
                                <div class="form-check form-switch ms-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="show_explanations" id="switchExpl" value="1"
                                           @checked(old('show_explanations', $settings->show_explanations)) onchange="updateLiveSummary()">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <strong class="d-block text-dark fs-13">Allow Retry Attempts</strong>
                                    <small class="text-muted">Enable pharmacists to retake the quiz up to max allowed attempts.</small>
                                </div>
                                <div class="form-check form-switch ms-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="allow_retry" id="switchRetry" value="1"
                                           @checked(old('allow_retry', $settings->allow_retry)) onchange="updateLiveSummary()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Attempts & Reward Points --}}
                <div class="setting-group-box shadow-sm">
                    <h5 class="fw-bold text-dark fs-15 mb-3 border-bottom pb-2">
                        <i class="las la-coins text-brand me-1"></i> 3. Attempts Limits &amp; Loyalty Rewards
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-13">Maximum Attempts Allowed <span class="text-danger">*</span></label>
                            <input type="number" name="max_attempts" id="inputMaxAttempts" class="form-control"
                                   value="{{ old('max_attempts', $settings->max_attempts) }}" min="1" max="50" required oninput="updateLiveSummary()">
                            <small class="text-muted">Maximum number of times a pharmacist can attempt the quiz.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-13">Reward Points on Passing <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="las la-coins text-warning"></i></span>
                                <input type="number" name="reward_points" id="inputRewardPoints" class="form-control"
                                       value="{{ old('reward_points', $settings->reward_points) }}" min="0" max="10000" step="1" required oninput="updateLiveSummary()">
                                <span class="input-group-text bg-light">pts</span>
                            </div>
                            <small class="text-muted">Credited directly to pharmacist's loyalty wallet upon first pass.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark fs-13">Pharmacist Instructions / Guidelines</label>
                            <textarea name="instructions" class="form-control" rows="2" placeholder="Guidelines shown before starting the quiz...">{{ old('instructions', $settings->instructions) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status, Schedule & Live Summary Column --}}
            <div class="col-lg-4">
                {{-- Master Activation & Schedule Card --}}
                <div class="card shadow-sm border-0 rounded-3 mb-3 sticky-top" style="top: 80px;">
                    <div class="card-header bg-brand-soft py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark fs-14">
                            <i class="las la-broadcast-tower text-brand me-1"></i> Module Status &amp; Schedule
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        {{-- Master Toggle --}}
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-dark fs-13">Module Active Status</strong>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="inputIsActive" value="1"
                                           @checked(old('is_active', $settings->is_active)) onchange="updateLiveSummary()" style="width: 36px; height: 20px;">
                                </div>
                            </div>
                            <small class="text-muted d-block" id="activeStatusDesc">
                                @if($settings->isCurrentlyActive())
                                    <span class="text-success fw-bold"><i class="las la-check-circle"></i> Currently Active &amp; Serving Quizzes</span>
                                @else
                                    <span class="text-secondary fw-bold"><i class="las la-pause-circle"></i> Module Currently Inactive / Off Schedule</span>
                                @endif
                            </small>
                        </div>

                        {{-- Scheduling Dates --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">Schedule Start Date (Optional)</label>
                            <input type="date" name="start_date" id="inputStartDate" class="form-control form-control-sm"
                                   value="{{ old('start_date', $settings->start_date?->format('Y-m-d')) }}" onchange="updateLiveSummary()">
                            <small class="text-muted">Quizzes will not be visible before this date.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">Schedule End Date (Optional)</label>
                            <input type="date" name="end_date" id="inputEndDate" class="form-control form-control-sm"
                                   value="{{ old('end_date', $settings->end_date?->format('Y-m-d')) }}" onchange="updateLiveSummary()">
                            <small class="text-muted">Quizzes will auto-expire after this date.</small>
                        </div>

                        {{-- Live Summary Box --}}
                        <div class="p-3 bg-brand-light border border-brand-subtle rounded-3 mb-4">
                            <strong class="text-brand fs-12 text-uppercase d-block mb-2">
                                <i class="las la-info-circle me-1"></i> Active Quiz Configuration:
                            </strong>
                            <ul class="list-unstyled mb-0 small text-dark d-flex flex-column gap-1">
                                <li>&bull; Questions Served: <strong id="sumQs">{{ $settings->questions_per_quiz }} Questions</strong></li>
                                <li>&bull; Time Allotted: <strong id="sumTime">{{ $settings->time_limit_minutes }} Minutes</strong></li>
                                <li>&bull; Pass Benchmark: <strong id="sumPass">{{ $settings->passing_percentage }}%</strong></li>
                                <li>&bull; Reward Value: <strong class="text-success" id="sumPoints">+{{ number_format($settings->reward_points, 0) }} pts</strong></li>
                                <li>&bull; Max Attempts: <strong id="sumAttempts">{{ $settings->max_attempts }} times</strong></li>
                                <li>&bull; Explanations: <strong id="sumExpl">{{ $settings->show_explanations ? 'Enabled' : 'Hidden' }}</strong></li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold shadow-sm">
                            <i class="las la-save me-1"></i> Save Quiz Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function applyPreset(qs, time, pass, randQ, randOpt, expl, retry, maxAtt, points) {
        document.getElementById('inputQuestionsPerQuiz').value = qs;
        document.getElementById('inputTimeLimit').value = time;
        document.getElementById('inputPassingPercentage').value = pass;
        document.getElementById('switchRandQ').checked = randQ;
        document.getElementById('switchRandOpt').checked = randOpt;
        document.getElementById('switchExpl').checked = expl;
        document.getElementById('switchRetry').checked = retry;
        document.getElementById('inputMaxAttempts').value = maxAtt;
        document.getElementById('inputRewardPoints').value = points;

        updateLiveSummary();
    }

    function updateLiveSummary() {
        const qs = document.getElementById('inputQuestionsPerQuiz').value || 10;
        const time = document.getElementById('inputTimeLimit').value || 10;
        const pass = document.getElementById('inputPassingPercentage').value || 70;
        const points = document.getElementById('inputRewardPoints').value || 50;
        const maxAtt = document.getElementById('inputMaxAttempts').value || 3;
        const expl = document.getElementById('switchExpl').checked;
        const isActive = document.getElementById('inputIsActive').checked;

        document.getElementById('sumQs').innerText = qs + ' Questions';
        document.getElementById('sumTime').innerText = time + ' Minutes';
        document.getElementById('sumPass').innerText = pass + '%';
        document.getElementById('sumPoints').innerText = '+' + points + ' pts';
        document.getElementById('sumAttempts').innerText = maxAtt + ' times';
        document.getElementById('sumExpl').innerText = expl ? 'Enabled' : 'Hidden';

        const desc = document.getElementById('activeStatusDesc');
        if (isActive) {
            desc.innerHTML = '<span class="text-success fw-bold"><i class="las la-check-circle"></i> Currently Active &amp; Serving Quizzes</span>';
        } else {
            desc.innerHTML = '<span class="text-secondary fw-bold"><i class="las la-pause-circle"></i> Module Currently Inactive / Off Schedule</span>';
        }
    }
</script>
@endpush
