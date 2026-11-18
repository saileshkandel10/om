@extends('layouts.admin')

@section('title', 'Pharmacist Question Bank')

@push('styles')
<style>
    .badge-diff-easy {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .badge-diff-medium {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .badge-diff-hard {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
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
                        <i class="las la-database text-brand me-1"></i> Pharmacist Question Bank
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.index') }}" class="text-brand text-decoration-underline">Pharmacist Quiz</a> &gt; Questions Repository
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <a href="{{ route('admin.pharmacist-quiz.questions.create') }}" class="btn btn-sm btn-brand shadow-sm">
                        <i class="las la-plus-circle me-1"></i> Add New Question
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.categories.index') }}" class="btn btn-sm btn-outline-brand">
                        <i class="las la-folder me-1"></i> Categories
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-graduation-cap me-1"></i> Quizzes
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

    {{-- Stats Cards --}}
    <div class="row mb-3 g-3">
        <div class="col-md-2 col-sm-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Total Questions</span>
                    <h4 class="mb-0 fw-bold text-dark mt-1">{{ number_format($stats['total']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #10B981 !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Active Questions</span>
                    <h4 class="mb-0 fw-bold text-success mt-1">{{ number_format($stats['active']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-4 col-4">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #15803D !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Easy Level</span>
                    <h4 class="mb-0 fw-bold text-success mt-1">{{ number_format($stats['easy']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-4 col-4">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #B45309 !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Medium Level</span>
                    <h4 class="mb-0 fw-bold text-warning mt-1">{{ number_format($stats['medium']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-4 col-4">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #B91C1C !important;">
                <div class="card-body p-3">
                    <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Hard Level</span>
                    <h4 class="mb-0 fw-bold text-danger mt-1">{{ number_format($stats['hard']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-brand-soft py-3 border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-list text-brand me-1"></i> Pharmacist Question Bank
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-white text-brand border border-brand-subtle px-3 py-2 fw-semibold">
                        {{ $questions->total() }} Questions in Bank
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter & Search Form --}}
            <form method="GET" action="{{ route('admin.pharmacist-quiz.questions.index') }}" class="row g-2 mb-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search question text, keyword..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <select name="difficulty" class="form-select form-select-sm">
                        <option value="">All Difficulties</option>
                        <option value="easy" @selected(request('difficulty') === 'easy')>Easy</option>
                        <option value="medium" @selected(request('difficulty') === 'medium')>Medium</option>
                        <option value="hard" @selected(request('difficulty') === 'hard')>Hard</option>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active Only</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive Only</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-brand">Filter</button>
                    <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 32%;">Question &amp; Medicine Reference</th>
                            <th style="width: 18%;">Category</th>
                            <th style="width: 10%;">Difficulty</th>
                            <th style="width: 18%;">Correct Answer</th>
                            <th style="width: 6%;">Points</th>
                            <th style="width: 6%;">Status</th>
                            <th style="width: 10%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questions as $q)
                            @php
                                $correctOpt = $q->options->firstWhere('is_correct', true);
                                $letter = $q->correct_option_letter;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <a href="{{ route('admin.pharmacist-quiz.questions.show', $q) }}" class="text-dark text-decoration-none">
                                            {{ $q->question_text }}
                                        </a>
                                    </div>
                                    @if ($q->product_reference || $q->product)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-muted border fs-11">
                                                <i class="las la-pills me-1 text-brand"></i> {{ $q->product_reference ?: $q->product?->brand_name }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($q->category)
                                        <span class="badge bg-brand-soft text-brand border border-brand-subtle px-2 py-1">
                                            {{ $q->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Uncategorized</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $diffClass = match($q->difficulty) {
                                            'easy' => 'badge-diff-easy',
                                            'hard' => 'badge-diff-hard',
                                            default => 'badge-diff-medium'
                                        };
                                    @endphp
                                    <span class="badge {{ $diffClass }} text-capitalize px-2 py-1 fs-11 fw-semibold">
                                        {{ $q->difficulty ?: 'Medium' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($correctOpt)
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success text-white me-2 fw-bold" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                {{ $letter ?: '✓' }}
                                            </span>
                                            <span class="small fw-semibold text-dark text-truncate" style="max-width: 160px;" title="{{ $correctOpt->option_text }}">
                                                {{ $correctOpt->option_text }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">No Answer Set</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold">{{ $q->points }} pt</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.pharmacist-quiz.questions.toggle-status', $q) }}">
                                        @csrf
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" @checked($q->is_active)>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.pharmacist-quiz.questions.show', $q) }}" class="btn btn-outline-brand" title="View Details">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pharmacist-quiz.questions.edit', $q) }}" class="btn btn-outline-secondary" title="Edit Question">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.pharmacist-quiz.questions.duplicate', $q) }}" class="d-inline" title="Duplicate Question">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info" title="Duplicate">
                                                <i class="las la-copy"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.pharmacist-quiz.questions.destroy', $q) }}" class="d-inline" onsubmit="return confirm('Delete this question from bank?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="las la-question-circle fs-48 text-muted d-block mb-2"></i>
                                    No questions found in Question Bank.
                                    <div class="mt-2">
                                        <a href="{{ route('admin.pharmacist-quiz.questions.create') }}" class="btn btn-sm btn-brand">
                                            Add First Question
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $questions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
