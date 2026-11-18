@extends('layouts.admin')

@section('title', __('Medication Analysis'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center py-3">
                <div>
                    <h4 class="page-title fw-bold text-dark mb-1">Medication Analysis</h4>
                    <p class="text-muted small mb-0">Canonical therapeutic classification, generic active ingredients, and indications.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title fw-bold mb-0">Medicines Directory & Associations</h5>
        </div>
        <div class="card-body pt-0">
            <form method="GET" action="{{ route('admin.health-analytics.medications.index') }}" class="row gy-2 gx-2 mb-4 align-items-end p-3 bg-light rounded-3">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Search Medicine</label>
                    <input type="search" name="search" class="form-control form-control-sm" placeholder="Brand, generic, or therapeutic class..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Health Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold" style="background-color: #CD0B6F; border-color: #CD0B6F;">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine Brand</th>
                            <th>Generic Name</th>
                            <th>Therapeutic Class</th>
                            <th>Active Ingredients</th>
                            <th>Indicated Health Category</th>
                            <th class="text-end">Total Detections</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $med)
                            <tr>
                                <td class="fw-bold text-dark">{{ $med->brand_name }}</td>
                                <td>{{ $med->generic_name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $med->therapeutic_class ?? 'General Pharma' }}</span></td>
                                <td class="text-muted small">{{ $med->active_ingredients ?? $med->generic_name }}</td>
                                <td>
                                    @foreach ($med->indications as $ind)
                                        <span class="badge" style="background-color: {{ $ind->healthCategory->color }}20; color: {{ $ind->healthCategory->color }}; border: 1px solid {{ $ind->healthCategory->color }}40;">
                                            {{ $ind->healthCategory->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ $evidenceStats[$med->brand_name] ?? 0 }} records
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No medicines found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $medicines->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
