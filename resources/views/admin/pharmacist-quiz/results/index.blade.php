@extends('layouts.admin')

@section('title', 'Pharmacist Quiz Results & Analytics')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center mb-3">
                <div>
                    <h4 class="page-title mb-1 text-dark fw-bold">
                        <i class="las la-chart-bar text-brand me-1"></i> Pharmacist Quiz Results &amp; Analytics Dashboard
                    </h4>
                    <p class="text-muted small mb-0">
                        Pharmacy Operations &gt; <a href="{{ route('admin.pharmacist-quiz.index') }}" class="text-brand text-decoration-underline">Pharmacist Quiz</a> &gt; Performance &amp; Evaluation Logs
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
                    <a href="{{ route('admin.pharmacist-quiz.questions.index') }}" class="btn btn-sm btn-outline-brand">
                        <i class="las la-database me-1"></i> Question Bank
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-folder me-1"></i> Categories
                    </a>
                    <a href="{{ route('admin.pharmacist-quiz.settings.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="las la-cog me-1"></i> Quiz Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 8 Metric KPI Cards Dashboard --}}
    <div class="row mb-3 g-2">
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #CD0B6F !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-2 bg-brand-soft text-brand me-2 fs-20">
                        <i class="las la-question-circle"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-10 fw-bold d-block">Total Questions</span>
                        <h5 class="mb-0 fw-bold text-dark">{{ number_format($stats['total_questions']) }}</h5>
                        <small class="text-success fs-10"><i class="las la-check"></i> {{ $stats['active_questions'] }} Active</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #1E3A5F !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary me-2 fs-20">
                        <i class="las la-users"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-10 fw-bold d-block">Total Pharmacists</span>
                        <h5 class="mb-0 fw-bold text-primary">{{ number_format($stats['total_pharmacists']) }}</h5>
                        <small class="text-muted fs-10">Unique Participants</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #0D9488 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-2 bg-info-subtle text-info me-2 fs-20">
                        <i class="las la-history"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-10 fw-bold d-block">Total Attempts</span>
                        <h5 class="mb-0 fw-bold text-info">{{ number_format($stats['total_attempts']) }}</h5>
                        <small class="text-muted fs-10">{{ $stats['completed_attempts'] }} Completed</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #10B981 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-2 bg-success-subtle text-success me-2 fs-20">
                        <i class="las la-trophy"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-10 fw-bold d-block">Passed Attempts</span>
                        <h5 class="mb-0 fw-bold text-success">{{ number_format($stats['passed_attempts']) }}</h5>
                        <small class="text-danger fs-10">{{ $stats['failed_attempts'] }} Failed</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-6">
            <div class="card stat-card-accent shadow-sm border-0 h-100" style="border-top-color: #8B5CF6 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle p-2 bg-purple-subtle text-purple me-2 fs-20">
                        <i class="las la-chart-pie"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fs-10 fw-bold d-block">Average Score</span>
                        <h5 class="mb-0 fw-bold text-purple">{{ $stats['average_score'] }}%</h5>
                        <small class="text-muted fs-10">Across all completed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card shadow-sm border-0 rounded-3 mb-3">
        <div class="card-header bg-brand-soft py-2 border-0">
            <h6 class="card-title mb-0 fw-bold text-dark fs-13">
                <i class="las la-filter text-brand me-1"></i> Advanced Filter &amp; Search Panel
            </h6>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.pharmacist-quiz.results.index') }}" class="row g-2">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Search Pharmacist / Quiz</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="las la-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Name, email, quiz title..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2 col-sm-6 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Topic Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Quiz</label>
                    <select name="quiz_id" class="form-select form-select-sm">
                        <option value="">All Quizzes</option>
                        @foreach ($quizzes as $q)
                            <option value="{{ $q->id }}" @selected(request('quiz_id') == $q->id)>{{ $q->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Result Status</label>
                    <select name="result" class="form-select form-select-sm">
                        <option value="">All Results</option>
                        <option value="passed" @selected(request('result') === 'passed')>Passed Only</option>
                        <option value="failed" @selected(request('result') === 'failed')>Failed Only</option>
                        <option value="in_progress" @selected(request('result') === 'in_progress')>In Progress</option>
                        <option value="expired" @selected(request('result') === 'expired')>Expired</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="recent" @selected(request('sort', 'recent') === 'recent')>Most Recent First</option>
                        <option value="highest_score" @selected(request('sort') === 'highest_score')>Highest Score (%)</option>
                        <option value="lowest_score" @selected(request('sort') === 'lowest_score')>Lowest Score (%)</option>
                        <option value="longest_time" @selected(request('sort') === 'longest_time')>Longest Time Taken</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Min Score %</label>
                    <input type="number" name="min_score" class="form-control form-control-sm" placeholder="0" min="0" max="100" value="{{ request('min_score') }}">
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label fs-11 text-muted fw-bold mb-1">Max Score %</label>
                    <input type="number" name="max_score" class="form-control form-control-sm" placeholder="100" min="0" max="100" value="{{ request('max_score') }}">
                </div>

                <div class="col-md-4 col-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-brand flex-fill">Apply Filters</button>
                    <a href="{{ route('admin.pharmacist-quiz.results.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Results Table Card --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-brand-soft py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark fs-14">
                <i class="las la-history text-brand me-1"></i> Pharmacist Quiz Attempts &amp; Evaluation Logs
            </h5>
            <span class="badge bg-white text-brand border border-brand-subtle px-3 py-1 fs-12 fw-semibold">
                {{ $attempts->total() }} Logged Attempts
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pharmacist</th>
                            <th>Quiz &amp; Category</th>
                            <th>Attempt</th>
                            <th>Score / Points</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Time Taken</th>
                            <th>Started / Completed</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attempts as $attempt)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-brand-soft text-brand fw-bold d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                            {{ strtoupper(substr($attempt->user?->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block">{{ $attempt->user?->name ?? 'Unknown Pharmacist' }}</strong>
                                            <small class="text-muted">{{ $attempt->user?->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $attempt->quiz?->title ?? 'Pharmacy Assessment' }}</div>
                                    @if ($attempt->quiz?->category)
                                        <span class="badge bg-brand-soft text-brand border border-brand-subtle px-2 py-1 fs-10">
                                            {{ $attempt->quiz->category->name }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">Attempt #{{ $attempt->attempt_number }}</span>
                                </td>
                                <td>
                                    <strong>{{ $attempt->score }} / {{ $attempt->total_points }}</strong>
                                    <div class="fs-10 text-muted">
                                        <span class="text-success"><i class="las la-check"></i> {{ $attempt->correct_answers_count }}</span> &bull;
                                        <span class="text-danger"><i class="las la-times"></i> {{ $attempt->incorrect_answers_count }}</span>
                                        @if ($attempt->unanswered_count > 0)
                                            &bull; <span class="text-muted"><i class="las la-minus"></i> {{ $attempt->unanswered_count }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong class="fs-13 {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($attempt->percentage, 2) }}%
                                    </strong>
                                </td>
                                <td>
                                    @if ($attempt->status === 'completed')
                                        @if ($attempt->is_passed)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="las la-check-circle me-1"></i> PASSED
                                            </span>
                                            @if ($attempt->reward_points_awarded > 0)
                                                <span class="badge bg-success ms-1 fs-10">+{{ number_format($attempt->reward_points_awarded, 0) }} pts</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="las la-times-circle me-1"></i> FAILED
                                            </span>
                                        @endif
                                    @elseif ($attempt->status === 'in_progress')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                            <i class="las la-hourglass-half me-1"></i> IN PROGRESS
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            {{ strtoupper($attempt->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($attempt->time_taken_formatted)
                                        <span class="small text-muted"><i class="las la-clock text-warning me-1"></i> {{ $attempt->time_taken_formatted }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fs-11 text-dark">{{ $attempt->started_at ? $attempt->started_at->format('M d, Y h:i A') : '—' }}</div>
                                    @if ($attempt->completed_at)
                                        <div class="fs-10 text-muted">Finished: {{ $attempt->completed_at->format('h:i A') }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.pharmacist-quiz.results.show', $attempt) }}" class="btn btn-sm btn-outline-brand py-1 px-2" title="View Detailed Audit">
                                        <i class="las la-eye me-1"></i> Audit Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="las la-history fs-48 text-muted d-block mb-2"></i>
                                    No quiz attempt logs found matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $attempts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
