@extends('layouts.admin')

@section('title', __('Health Analytics Audit Logs'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Health Analytics Audit Trail</h4>
                    <p class="text-muted small mb-0">Security and HIPAA-aligned audit log of all analytical inspections, reviews, and clinical mapping changes.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title fw-bold mb-0">System Activity Logs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>Admin User</th>
                            <th>Action Performed</th>
                            <th>Affected Patient</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, Y • h:i:s A') }}</td>
                                <td>
                                    <span class="fw-bold">{{ $log->admin?->name ?? 'Admin #'.$log->admin_id }}</span>
                                    <small class="text-muted d-block">{{ $log->admin?->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1">{{ $log->action }}</span>
                                </td>
                                <td>
                                    @if ($log->patient)
                                        <a href="{{ route('admin.health-analytics.patients.show', $log->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $log->patient->name }} (P-{{ $log->patient->id }})
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><code>{{ $log->ip_address ?? '127.0.0.1' }}</code></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No audit logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
