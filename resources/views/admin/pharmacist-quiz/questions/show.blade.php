@extends('layouts.admin')

@section('title', 'Question #' . $question->id . ' Details')

@push('styles')
<style>
    .opt-detail-card {
        border-radius: 12px;
        padding: 12px 16px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .opt-detail-card.is-correct-opt {
        border-color: #10B981;
        background: #ecfdf5;
    }
    .opt-badge-pill {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        margin-right: 12px;
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
                        <i class="las la-question-circle text-brand me-1"></i> Question #{{ $question->id }}
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="text-brand text-decoration-underline">Question Bank</a> &gt; #{{ $question->id }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.pharmacist-quiz.questions.duplicate', $question) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-info">
                            <i class="las la-copy me-1"></i> Duplicate
                        </button>
                    </form>
                    <a href="{{ route('admin.pharmacist-quiz.questions.edit', $question) }}" class="btn btn-sm btn-brand">
                        <i class="las la-edit me-1"></i> Edit Question
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Bank
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Question Card --}}
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header bg-brand-soft py-3 border-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-brand-soft text-brand border border-brand-subtle me-2 px-2 py-1 fs-12">
                            {{ $question->category?->name ?? 'General Pharmacology' }}
                        </span>
                        @php
                            $diffClass = match($question->difficulty) {
                                'easy' => 'badge-diff-easy',
                                'hard' => 'badge-diff-hard',
                                default => 'badge-diff-medium'
                            };
                        @endphp
                        <span class="badge {{ $diffClass }} text-capitalize px-2 py-1 fs-11">
                            {{ $question->difficulty ?: 'Medium' }}
                        </span>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark border fs-12 fw-bold">{{ $question->points }} Point(s)</span>
                        @if ($question->is_active)
                            <span class="badge bg-success-subtle text-success ms-1">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary ms-1">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-4 fs-17" style="line-height: 1.5;">
                        {{ $question->question_text }}
                    </h5>

                    <h6 class="fw-bold text-muted fs-12 text-uppercase mb-3" style="letter-spacing: 0.5px;">Multiple Choice Options:</h6>

                    @php
                        $letters = ['A', 'B', 'C', 'D'];
                    @endphp

                    @foreach ($question->options as $idx => $opt)
                        <div class="opt-detail-card {{ $opt->is_correct ? 'is-correct-opt' : '' }}">
                            <div class="opt-badge-pill {{ $opt->is_correct ? 'bg-success text-white' : 'bg-light text-dark' }}">
                                {{ $letters[$idx] ?? ($idx + 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-semibold {{ $opt->is_correct ? 'text-success' : 'text-dark' }} fs-14">
                                    {{ $opt->option_text }}
                                </span>
                            </div>
                            @if ($opt->is_correct)
                                <span class="badge bg-success px-3 py-2 rounded-pill fs-11 fw-bold">
                                    <i class="las la-check-circle me-1"></i> CORRECT ANSWER
                                </span>
                            @endif
                        </div>
                    @endforeach

                    @if ($question->explanation)
                        <div class="mt-4 p-3 bg-brand-light rounded-3 border border-brand-subtle">
                            <strong class="text-brand fs-13 d-block mb-1">
                                <i class="las la-lightbulb me-1"></i> Clinical Explanation &amp; Literature Rationale:
                            </strong>
                            <p class="mb-0 text-dark fs-13" style="line-height: 1.5;">
                                {{ $question->explanation }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta & Associated Info --}}
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-14">
                        <i class="las la-info-circle text-brand me-1"></i> Question Metadata
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 fs-13">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 40%;">Category</td>
                                    <td>{{ $question->category?->name ?? 'Uncategorized' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Difficulty</td>
                                    <td class="text-capitalize fw-bold">{{ $question->difficulty ?: 'Medium' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Medicine Ref</td>
                                    <td>
                                        @if ($question->product_reference || $question->product)
                                            <span class="badge bg-light text-dark border">
                                                <i class="las la-pills text-brand me-1"></i> {{ $question->product_reference ?: $question->product?->brand_name }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Linked Quiz</td>
                                    <td>
                                        @if ($question->quiz)
                                            <a href="{{ route('admin.pharmacist-quiz.index', ['quiz_id' => $question->quiz_id]) }}" class="text-brand fw-semibold text-decoration-underline">
                                                {{ $question->quiz->title }}
                                            </a>
                                        @else
                                            <span class="badge bg-light text-muted border">Question Bank (Unassigned)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Display Order</td>
                                    <td>{{ $question->display_order }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Created Date</td>
                                    <td>{{ $question->created_at ? $question->created_at->format('M d, Y') : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Last Updated</td>
                                    <td>{{ $question->updated_at ? $question->updated_at->format('M d, Y') : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <form method="POST" action="{{ route('admin.pharmacist-quiz.questions.toggle-status', $question) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                {{ $question->is_active ? 'Deactivate Question' : 'Activate Question' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
