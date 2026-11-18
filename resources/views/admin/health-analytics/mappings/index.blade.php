@extends('layouts.admin')

@section('title', __('Medicine–Condition Mapping'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Medicine–Condition Mapping</h4>
                    <p class="text-muted small mb-0">Authorized clinical configuration linking pharmaceutical active ingredients to analytical categories.</p>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Add New Mapping Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Add Clinical Mapping</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.health-analytics.mappings.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Medicine</label>
                            <select name="medicine_id" class="form-select form-select-sm" required>
                                <option value="">Select Medicine</option>
                                @foreach ($medicines as $med)
                                    <option value="{{ $med->id }}">{{ $med->brand_name }} ({{ $med->generic_name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Health Category</label>
                            <select name="health_category_id" class="form-select form-select-sm" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Evidence Level</label>
                            <select name="evidence_level" class="form-select form-select-sm" required>
                                <option value="High">High Evidence</option>
                                <option value="Moderate">Moderate Evidence</option>
                                <option value="Low">Low Evidence</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Clinical Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Primary therapeutic indication notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold" style="background-color: #CD0B6F; border-color: #CD0B6F;">
                            Save Mapping
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mappings Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Active Mappings ({{ $mappings->total() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Medicine</th>
                                    <th>Health Category</th>
                                    <th>Evidence Level</th>
                                    <th>Notes</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mappings as $mapping)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $mapping->medicine->brand_name }}</div>
                                            <small class="text-muted">{{ $mapping->medicine->generic_name }}</small>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $mapping->healthCategory->color }}20; color: {{ $mapping->healthCategory->color }}; border: 1px solid {{ $mapping->healthCategory->color }}40;">
                                                {{ $mapping->healthCategory->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $mapping->evidence_level }}</span>
                                        </td>
                                        <td class="small text-muted" style="max-width: 200px;">{{ $mapping->notes ?? '—' }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.health-analytics.mappings.destroy', $mapping) }}" onsubmit="return confirm('Remove this mapping?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                                    <i class="iconoir-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No mappings defined yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3">
                        {{ $mappings->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
