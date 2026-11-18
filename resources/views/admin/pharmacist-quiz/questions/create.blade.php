@extends('layouts.admin')

@section('title', 'Add Question - Pharmacist Question Bank')

@push('styles')
<style>
    .option-card {
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .option-card:focus-within, .option-card.active-correct {
        border-color: #10B981 !important;
        background-color: #f0fdf4 !important;
    }
    .opt-letter-badge {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
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
                        <i class="las la-plus-circle text-brand me-1"></i> Add Question to Question Bank
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="text-brand text-decoration-underline">Question Bank</a> &gt; Add Question
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Questions
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Row --}}
    <form method="POST" action="{{ route('admin.pharmacist-quiz.questions.store') }}" id="questionForm">
        @csrf

        <div class="row">
            {{-- Form Inputs Column --}}
            <div class="col-lg-8 mb-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-brand-soft py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="las la-edit text-brand me-1"></i> Question Details &amp; MCQ Options
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

                        {{-- Category & Difficulty --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Quiz Category <span class="text-danger">*</span></label>
                                <select name="quiz_category_id" id="categoryInput" class="form-select" required onchange="updateLivePreview()">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('quiz_category_id', $preselectedCategory) == $cat->id)>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Difficulty Level <span class="text-danger">*</span></label>
                                <select name="difficulty" id="difficultyInput" class="form-select" required onchange="updateLivePreview()">
                                    <option value="easy" @selected(old('difficulty') === 'easy')>Easy (Fundamental Concepts)</option>
                                    <option value="medium" @selected(old('difficulty', 'medium') === 'medium')>Medium (Standard Clinical Practice)</option>
                                    <option value="hard" @selected(old('difficulty') === 'hard')>Hard (Advanced Pharmacology &amp; Interactions)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Question Text --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark fs-13">Question Text <span class="text-danger">*</span></label>
                            <textarea name="question_text" id="questionTextInput" class="form-control" rows="3" placeholder="Enter the complete question prompt clearly..." required oninput="updateLivePreview()">{{ old('question_text') }}</textarea>
                        </div>

                        {{-- 4 MCQ Options with Radio Button for Correct Answer --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold text-dark fs-13 mb-0">
                                    <i class="las la-list-ol text-brand me-1"></i> Multiple Choice Options (4 Required) <span class="text-danger">*</span>
                                </label>
                                <span class="badge bg-success-subtle text-success fs-11">
                                    <i class="las la-check-circle me-1"></i> Select the radio button for the correct answer
                                </span>
                            </div>

                            @php
                                $letters = ['A', 'B', 'C', 'D'];
                                $optColors = ['bg-primary-subtle text-primary', 'bg-info-subtle text-info', 'bg-warning-subtle text-warning', 'bg-purple-subtle text-purple'];
                            @endphp

                            @for ($i = 0; $i < 4; $i++)
                                <div class="card border p-2 mb-2 option-card" id="optCard_{{ $i }}">
                                    <div class="d-flex align-items-center">
                                        <div class="opt-letter-badge {{ $optColors[$i] }} me-2">
                                            {{ $letters[$i] }}
                                        </div>
                                        <div class="flex-grow-1 me-3">
                                            <input type="text" name="options[{{ $i }}]" id="optInput_{{ $i }}"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('options.'.$i) }}"
                                                   placeholder="Enter Option {{ $letters[$i] }} text..." required oninput="updateLivePreview()">
                                        </div>
                                        <div class="form-check form-check-inline me-0">
                                            <input class="form-check-input" type="radio" name="correct_option" id="correctOpt_{{ $i }}"
                                                   value="{{ $i }}" @checked(old('correct_option', 0) == $i)
                                                   onchange="highlightCorrectOption({{ $i }}); updateLivePreview();" style="width: 20px; height: 20px; cursor: pointer;">
                                            <label class="form-check-label fs-12 fw-semibold text-muted ms-1" for="correctOpt_{{ $i }}" style="cursor: pointer;">
                                                Correct Answer
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        {{-- Explanation / Clinical Rationale --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark fs-13">Clinical Explanation &amp; Rationale (Optional)</label>
                            <textarea name="explanation" id="explanationInput" class="form-control" rows="2" placeholder="Explain why the correct answer is right and reference clinical literature or guidelines..." oninput="updateLivePreview()">{{ old('explanation') }}</textarea>
                            <small class="text-muted">Shown to pharmacists after submitting their quiz attempt for educational learning.</small>
                        </div>

                        {{-- Medicine / Product Reference & Quiz Assignment --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Medicine / Product Reference</label>
                                <input type="text" name="product_reference" id="productRefInput" class="form-control" value="{{ old('product_reference') }}" placeholder="e.g. Paracetamol 500mg, Amoxicillin, Cefixime">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark fs-13">Optional Quiz Assignment</label>
                                <select name="quiz_id" class="form-select">
                                    <option value="">Standalone Question Bank (Unassigned)</option>
                                    @foreach ($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}" @selected(old('quiz_id', $preselectedQuiz) == $quiz->id)>
                                            {{ $quiz->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark fs-13">Points Value</label>
                                <input type="number" name="points" class="form-control" value="{{ old('points', 1) }}" min="1">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark fs-13">Display Order</label>
                                <input type="number" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0">
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch p-2 bg-light rounded-3 w-100">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" name="is_active" id="isActiveSwitch" value="1" @checked(old('is_active', true))>
                                    <label class="form-check-label fw-semibold text-dark fs-12" for="isActiveSwitch">
                                        Question Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-brand px-4 py-2 fw-semibold">
                                <i class="las la-save me-1"></i> Save to Question Bank
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Preview Column --}}
            <div class="col-lg-4 mb-3">
                <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top: 80px;">
                    <div class="card-header bg-brand-soft py-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark fs-14">
                            <i class="las la-eye text-brand me-1"></i> Question Preview
                        </h5>
                        <span class="badge bg-white text-brand border border-brand-subtle fs-11" id="previewDiffBadge">
                            MEDIUM
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <span class="badge bg-brand-soft text-brand mb-2 fs-10" id="previewCatBadge">Category</span>
                            <h6 class="fw-bold text-dark fs-13 mb-3" id="previewQuestionText">
                                Question prompt will appear here...
                            </h6>

                            <div class="d-flex flex-column gap-2" id="previewOptionsContainer">
                                @foreach (['A', 'B', 'C', 'D'] as $idx => $let)
                                    <div class="p-2 border rounded bg-white d-flex align-items-center" id="prevOptBox_{{ $idx }}">
                                        <span class="badge bg-secondary me-2 fw-bold" id="prevOptBadge_{{ $idx }}">{{ $let }}</span>
                                        <span class="small text-dark text-truncate" id="prevOptText_{{ $idx }}">Option {{ $let }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-3 bg-brand-light rounded-3 border border-brand-subtle" id="previewExplBox">
                            <strong class="text-brand fs-11 d-block mb-1"><i class="las la-lightbulb me-1"></i> Clinical Explanation:</strong>
                            <p class="small text-muted mb-0 fst-italic" id="previewExplText">Explanation text will appear here.</p>
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
    function highlightCorrectOption(selectedIndex) {
        for (let i = 0; i < 4; i++) {
            const card = document.getElementById(`optCard_${i}`);
            if (card) {
                if (i === selectedIndex) {
                    card.classList.add('active-correct');
                } else {
                    card.classList.remove('active-correct');
                }
            }
        }
    }

    function updateLivePreview() {
        const qText = document.getElementById('questionTextInput').value || 'Question prompt will appear here...';
        document.getElementById('previewQuestionText').innerText = qText;

        const diff = document.getElementById('difficultyInput').value || 'medium';
        document.getElementById('previewDiffBadge').innerText = diff.toUpperCase();

        const catSelect = document.getElementById('categoryInput');
        const catName = catSelect.options[catSelect.selectedIndex]?.text || 'General';
        document.getElementById('previewCatBadge').innerText = catName;

        const expl = document.getElementById('explanationInput').value || 'Explanation text will appear here.';
        document.getElementById('previewExplText').innerText = expl;

        const selectedCorrect = document.querySelector('input[name="correct_option"]:checked')?.value || 0;

        for (let i = 0; i < 4; i++) {
            const optVal = document.getElementById(`optInput_${i}`)?.value || `Option ${String.fromCharCode(65 + i)}`;
            const prevText = document.getElementById(`prevOptText_${i}`);
            const prevBox = document.getElementById(`prevOptBox_${i}`);
            const prevBadge = document.getElementById(`prevOptBadge_${i}`);

            if (prevText) prevText.innerText = optVal;

            if (parseInt(selectedCorrect) === i) {
                prevBox.style.borderColor = '#10B981';
                prevBox.style.backgroundColor = '#ecfdf5';
                prevBadge.className = 'badge bg-success me-2 fw-bold';
            } else {
                prevBox.style.borderColor = '#e2e8f0';
                prevBox.style.backgroundColor = '#ffffff';
                prevBadge.className = 'badge bg-secondary me-2 fw-bold';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectedCorrect = document.querySelector('input[name="correct_option"]:checked')?.value || 0;
        highlightCorrectOption(parseInt(selectedCorrect));
        updateLivePreview();
    });
</script>
@endpush
