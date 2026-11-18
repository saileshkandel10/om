@extends('layouts.admin')

@section('title', 'Attempt #' . $attempt->id . ' Audit - Pharmacist Quiz')

@push('styles')
<style>
    .audit-q-card {
        border-radius: 14px;
        padding: 16px;
        border: 1.5px solid #e2e8f0;
        margin-bottom: 12px;
        background: #ffffff;
    }
    .audit-q-card.is-correct-q {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }
    .audit-q-card.is-wrong-q {
        border-color: #fecaca;
        background: #fef2f2;
    }
    .audit-q-card.is-unanswered-q {
        border-color: #e2e8f0;
        background: #f8fafc;
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
                        <i class="las la-file-signature text-brand me-1"></i> Pharmacist Quiz Attempt #{{ $attempt->id }} Audit
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.results.index') }}" class="text-brand text-decoration-underline">Results Dashboard</a> &gt; Attempt #{{ $attempt->id }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.pharmacist-quiz.results.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Results Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Overview & Performance Cards --}}
    <div class="row g-3 mb-4">
        {{-- Pharmacist Profile Card --}}
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold text-dark fs-13">
                        <i class="las la-user-graduate text-brand me-1"></i> Pharmacist Candidate Profile
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-brand-soft text-brand fw-bold d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 46px; height: 46px; font-size: 16px;">
                            {{ strtoupper(substr($attempt->user?->name ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark fs-14">{{ $attempt->user?->name ?? 'Unknown' }}</h6>
                            <small class="text-muted d-block">{{ $attempt->user?->email }}</small>
                            <small class="text-muted">{{ $attempt->user?->mobile ?? 'Mobile: —' }}</small>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="fs-12 text-dark d-flex flex-column gap-1">
                        <div>&bull; Quiz: <strong class="text-brand">{{ $attempt->quiz?->title }}</strong></div>
                        <div>&bull; Category: <span class="badge bg-brand-soft text-brand">{{ $attempt->quiz?->category?->name ?? 'General' }}</span></div>
                        <div>&bull; Attempt Quota: <strong>Attempt #{{ $attempt->attempt_number }}</strong></div>
                        <div>&bull; Duration: <strong>{{ $attempt->time_taken_formatted ?: '—' }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Score & Verdict Card --}}
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 rounded-3 h-100 text-center">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold text-dark fs-13">
                        <i class="las la-trophy text-brand me-1"></i> Performance &amp; Evaluation Verdict
                    </h6>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <div class="mb-2">
                        @if ($attempt->is_passed)
                            <span class="badge bg-success-subtle text-success fs-14 px-3 py-2 border border-success-subtle rounded-pill">
                                <i class="las la-check-circle me-1"></i> PASSED ASSESSMENT
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger fs-14 px-3 py-2 border border-danger-subtle rounded-pill">
                                <i class="las la-times-circle me-1"></i> FAILED ASSESSMENT
                            </span>
                        @endif
                    </div>
                    <h2 class="fw-bold mb-0 {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                        {{ number_format($attempt->percentage, 2) }}%
                    </h2>
                    <small class="text-muted d-block mt-1">Score: <strong>{{ $attempt->score }} / {{ $attempt->total_points }} Points</strong></small>

                    @if ($attempt->reward_points_awarded > 0)
                        <div class="mt-2">
                            <span class="badge bg-success fs-11 px-3 py-1">
                                <i class="las la-coins me-1"></i> +{{ number_format($attempt->reward_points_awarded, 0) }} Reward Points Credited
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Breakdown Stats Card --}}
        <div class="col-lg-4 col-md-12">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h6 class="card-title mb-0 fw-bold text-dark fs-13">
                        <i class="las la-chart-pie text-brand me-1"></i> Answers Breakdown
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 text-center mb-2">
                        <div class="col-4">
                            <div class="p-2 bg-success-subtle rounded-3">
                                <strong class="fs-16 text-success d-block">{{ $attempt->correct_answers_count }}</strong>
                                <small class="text-success fs-10 fw-bold">Correct</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-danger-subtle rounded-3">
                                <strong class="fs-16 text-danger d-block">{{ $attempt->incorrect_answers_count }}</strong>
                                <small class="text-danger fs-10 fw-bold">Incorrect</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-secondary-subtle rounded-3">
                                <strong class="fs-16 text-muted d-block">{{ $attempt->unanswered_count }}</strong>
                                <small class="text-muted fs-10 fw-bold">Unanswered</small>
                            </div>
                        </div>
                    </div>
                    <div class="fs-11 text-muted">
                        <div>Started: <strong>{{ $attempt->started_at ? $attempt->started_at->format('M d, Y h:i A') : '—' }}</strong></div>
                        <div>Completed: <strong>{{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y h:i A') : '—' }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Question-by-Question Detailed Audit --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-brand-soft py-3 border-0">
            <h5 class="card-title mb-0 fw-bold text-dark fs-14">
                <i class="las la-tasks text-brand me-1"></i> Question-by-Question Audit Review
            </h5>
        </div>
        <div class="card-body p-4">
            @foreach ($attempt->answers as $idx => $ans)
                @php
                    $isCorrect = $ans->is_correct;
                    $isUnanswered = $ans->selected_option_id === null;
                    $cardClass = $isUnanswered ? 'is-unanswered-q' : ($isCorrect ? 'is-correct-q' : 'is-wrong-q');
                    $correctOption = $ans->question?->options->firstWhere('is_correct', true);
                @endphp

                <div class="audit-q-card {{ $cardClass }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <span class="badge {{ $isCorrect ? 'bg-success' : ($isUnanswered ? 'bg-secondary' : 'bg-danger') }} me-2 fs-12 px-2 py-1">
                                Q{{ $idx + 1 }}
                            </span>
                            <strong class="text-dark fs-14">{{ $ans->question_text_snapshot ?: $ans->question?->question_text }}</strong>
                        </div>
                        <div>
                            @if ($isCorrect)
                                <span class="badge bg-success-subtle text-success border border-success-subtle fs-11">
                                    <i class="las la-check-circle me-1"></i> Correct (+{{ $ans->points_earned }} pt)
                                </span>
                            @elseif ($isUnanswered)
                                <span class="badge bg-secondary-subtle text-secondary border fs-11">
                                    <i class="las la-minus-circle me-1"></i> Unanswered (0 pt)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-11">
                                    <i class="las la-times-circle me-1"></i> Incorrect (0 pt)
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-white">
                                <small class="text-muted d-block fs-10 fw-bold text-uppercase">Pharmacist Submitted Choice:</small>
                                <span class="fw-semibold {{ $isCorrect ? 'text-success' : 'text-danger' }} fs-13">
                                    {{ $ans->selectedOption?->option_text ?? '— (Unanswered)' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-white">
                                <small class="text-muted d-block fs-10 fw-bold text-uppercase">Correct Answer Choice:</small>
                                <span class="fw-semibold text-success fs-13">
                                    {{ $correctOption?->option_text ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($ans->explanation_snapshot || $ans->question?->explanation)
                        <div class="mt-2 p-2 bg-brand-light border border-brand-subtle rounded small text-dark">
                            <strong class="text-brand fs-11"><i class="las la-lightbulb me-1"></i> Clinical Rationale:</strong>
                            {{ $ans->explanation_snapshot ?: $ans->question?->explanation }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
