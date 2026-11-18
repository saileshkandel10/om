@extends('layouts.admin')

@section('title', 'Pharmacist Quiz Module')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center mb-3">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-graduation-cap text-brand me-1"></i> Pharmacist Quiz &amp; Knowledge Module
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; Manage knowledge assessments, pharmacology quiz banks, and reward point allocations.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <a href="{{ route('admin.pharmacist-quiz.questions.create') }}" class="btn btn-sm btn-brand shadow-sm">
                        <i class="las la-plus-circle me-1"></i> Add Question
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-sm btn-outline-brand">
                        <i class="las la-database me-1"></i> Question Bank
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-folder me-1"></i> Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-3 g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-brand-soft text-brand me-3">
                        <i class="las la-question-circle fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Total Quizzes</span>
                        <h4 class="mb-0 fw-bold text-dark mt-1">{{ number_format($stats['total_quizzes']) }}</h4>
                        <small class="text-success fs-11"><i class="las la-check-circle"></i> {{ $stats['active_quizzes'] }} Active</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #1E3A5F !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary me-3">
                        <i class="las la-database fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Question Bank</span>
                        <h4 class="mb-0 fw-bold text-primary mt-1">{{ number_format($stats['total_questions']) }}</h4>
                        <small class="text-muted fs-11">Active MCQ Items</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #0D9488 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-success-subtle text-success me-3">
                        <i class="las la-user-graduate fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Pharmacist Attempts</span>
                        <h4 class="mb-0 fw-bold text-success mt-1">{{ number_format($stats['total_attempts']) }}</h4>
                        <small class="text-muted fs-11">{{ $stats['passed_attempts'] }} Passed</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #F59E0B !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning me-3">
                        <i class="las la-trophy fs-24"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-11 fw-semibold d-block">Passing Rate</span>
                        <h4 class="mb-0 fw-bold text-warning mt-1">{{ $stats['pass_rate'] }}%</h4>
                        <small class="text-muted fs-11">Average Score</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-pills bg-white p-2 rounded-3 border shadow-sm" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'quizzes' ? 'active' : '' }}" id="quizzes-tab" data-bs-toggle="pill" href="#quizzesPane" role="tab">
                        <i class="las la-list me-1 fs-16"></i>
                        Quizzes Directory ({{ $quizzes->total() }})
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link {{ $tab === 'categories' ? 'active' : '' }}" id="categories-tab" data-bs-toggle="pill" href="#categoriesPane" role="tab">
                        <i class="las la-folder me-1 fs-16"></i>
                        Categories ({{ $categories->count() }})
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link {{ $tab === 'attempts' ? 'active' : '' }}" id="attempts-tab" data-bs-toggle="pill" href="#attemptsPane" role="tab">
                        <i class="las la-history me-1 fs-16"></i>
                        Recent Pharmacist Attempts Log
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        {{-- ======================================================= --}}
        {{-- TAB 1: QUIZZES DIRECTORY --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'quizzes' ? 'show active' : '' }}" id="quizzesPane" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="las la-clipboard-list text-brand me-1"></i> Active Pharmacist Quizzes
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Search Filter --}}
                    <form method="GET" action="{{ route('admin.pharmacist-quiz.index') }}" class="row g-2 mb-3 align-items-center">
                        <input type="hidden" name="tab" value="quizzes">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search quiz title, description..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-brand">Search</button>
                            <a href="{{ route('admin.pharmacist-quiz.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Quiz Title</th>
                                    <th>Category</th>
                                    <th>Questions &amp; Duration</th>
                                    <th>Passing Mark</th>
                                    <th>Reward Points</th>
                                    <th>Pharmacist Attempts</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($quizzes as $quiz)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $quiz->title }}</div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px;">{{ $quiz->description ?: 'Knowledge assessment' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-brand-soft text-brand border border-brand-subtle px-2 py-1">
                                                {{ $quiz->category?->name ?? 'General' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fs-12">
                                                <div><i class="las la-question-circle text-primary me-1"></i> {{ $quiz->questions_count }} Questions</div>
                                                <div class="text-muted"><i class="las la-clock text-warning me-1"></i> {{ $quiz->time_limit_minutes > 0 ? $quiz->time_limit_minutes.' mins' : 'Unlimited' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fs-12 fw-bold">{{ $quiz->passing_percentage }}%</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success fs-12 fw-bold">
                                                <i class="las la-coins me-1"></i> +{{ number_format($quiz->reward_points, 0) }} pts
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $quiz->attempts_count }}</span> <span class="text-muted fs-11">times</span>
                                        </td>
                                        <td>
                                            @if ($quiz->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="las la-graduation-cap fs-48 text-muted d-block mb-2"></i>
                                            No pharmacist quizzes created yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $quizzes->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- TAB 2: CATEGORIES --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'categories' ? 'show active' : '' }}" id="categoriesPane" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-folder-open text-brand me-1"></i> Quiz Categories
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Active Quizzes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $cat)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle p-2 bg-brand-soft text-brand me-2">
                                                    <i class="las la-tag"></i>
                                                </div>
                                                <strong class="text-dark">{{ $cat->name }}</strong>
                                            </div>
                                        </td>
                                        <td><code>{{ $cat->slug }}</code></td>
                                        <td class="text-muted small">{{ $cat->description ?: '—' }}</td>
                                        <td>
                                            <span class="badge bg-brand-soft text-brand px-2 py-1">{{ $cat->quizzes_count }} Quizzes</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No quiz categories configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- TAB 3: ATTEMPTS LOG --}}
        {{-- ======================================================= --}}
        <div class="tab-pane fade {{ $tab === 'attempts' ? 'show active' : '' }}" id="attemptsPane" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand-soft py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="las la-history text-brand me-1"></i> Pharmacist Quiz Attempts &amp; Results Log
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Attempt ID</th>
                                    <th>Pharmacist</th>
                                    <th>Quiz</th>
                                    <th>Score / Points</th>
                                    <th>Percentage</th>
                                    <th>Result</th>
                                    <th>Reward Points</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentAttempts as $attempt)
                                    <tr>
                                        <td><code>#{{ $attempt->id }}</code></td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $attempt->user?->name ?? 'Pharmacist' }}</div>
                                            <small class="text-muted">{{ $attempt->user?->email }}</small>
                                        </td>
                                        <td>{{ $attempt->quiz?->title }}</td>
                                        <td>{{ $attempt->score }} / {{ $attempt->total_points }}</td>
                                        <td><strong>{{ $attempt->percentage }}%</strong></td>
                                        <td>
                                            @if ($attempt->is_passed)
                                                <span class="badge bg-success-subtle text-success px-2 py-1">Passed</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($attempt->reward_points_awarded > 0)
                                                <span class="badge bg-success">+{{ $attempt->reward_points_awarded }} pts</span>
                                            @else
                                                <span class="text-muted fs-11">0 pts</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y h:i A') : $attempt->started_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No quiz attempts logged yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
