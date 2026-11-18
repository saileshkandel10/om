@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Product Details</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                            <li class="breadcrumb-item active">{{ $product->brand_name }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mt-3">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->brand_name }}"
                                    class="rounded img-fluid" style="max-width: 180px; max-height: 180px; object-fit: cover;">
                            @else
                                <div class="rounded bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 140px; height: 140px; font-size: 48px;">
                                    <i class="las la-pills"></i>
                                </div>
                            @endif
                        </div>
                        <h5 class="mt-3 mb-1 fw-semibold">{{ $product->brand_name }}</h5>
                        @if ($product->sku)
                            <p class="text-muted mb-2"><small>SKU: {{ $product->sku }}</small></p>
                        @endif
                        <div class="mb-3">
                            @php($statusColor = $product->is_active ? 'success' : 'secondary')
                            <span class="badge bg-{{ $statusColor }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>

                        <hr>

                        <div class="text-start">
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Category</span>
                                <span class="fw-medium">{{ $product->category?->name ?? '—' }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Illness Category</span>
                                <span class="fw-medium">{{ $product->illnessCategory?->name ?? '—' }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Generic Name</span>
                                <span class="fw-medium">{{ $product->generic_name ?? '—' }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Dosage Form</span>
                                <span class="fw-medium">{{ $product->dosage_form ?? '—' }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Strength</span>
                                <span class="fw-medium">{{ $product->strength ?? '—' }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Pack Size</span>
                                <span class="fw-medium">{{ $product->pack_size ?? '—' }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">MRP</span>
                                <span class="fw-medium">{{ $product->mrp ? number_format($product->mrp, 2) : '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Created</span>
                                <span class="fw-medium">{{ $product->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm"><i class="las la-edit me-1"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="las la-trash-alt me-1"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="bg-primary-subtle p-2 border-dashed border-primary rounded mb-3 d-flex align-items-center gap-2">
                    <i class="las la-chart-bar fs-5 text-primary"></i>
                                    <span class="text-primary fw-semibold">{{ $product->brand_name }}</span>
                    <span class="text-primary fw-normal">overview</span>
                </div>

                @if ($product->batches->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title">Product Batches ({{ $product->batches->count() }})</h4>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('admin.product-batches.index') }}" class="btn btn-primary btn-sm"><i class="las la-eye me-1"></i> View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Batch No.</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Manufacturing</th>
                                            <th>Expiry</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($product->batches as $batch)
                                            <tr>
                                                <td>{{ $batch->batch_no ?? '—' }}</td>
                                                <td>{{ number_format($batch->quantity ?? 0) }}</td>
                                                <td>{{ $batch->unit_price ? number_format($batch->unit_price, 2) : '—' }}</td>
                                                <td>{{ $batch->manufacturing_date?->format('d/m/Y') ?? '—' }}</td>
                                                <td>
                                                    @if ($batch->expiry_date)
                                                        <span class="{{ $batch->expiry_date->isPast() ? 'text-danger' : 'text-success' }}">
                                                            {{ $batch->expiry_date->format('d/m/Y') }}
                                                        </span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Recent Purchase Bills</h4>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.purchase-bills.index') }}" class="btn btn-primary btn-sm"><i class="las la-eye me-1"></i> View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Pharmacy</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($product->purchaseBillItems as $item)
                                        <tr>
                                            <td><a href="{{ route('admin.purchase-bills.show', $item->purchaseBill) }}" class="text-primary">{{ $item->purchaseBill?->invoice_number ?? '—' }}</a></td>
                                            <td>{{ $item->purchaseBill?->pharmacy?->store_name ?? '—' }}</td>
                                            <td>{{ number_format($item->quantity ?? 0) }}</td>
                                            <td>{{ $item->unit_price ? number_format($item->unit_price, 2) : '—' }}</td>
                                            <td>{{ $item->purchaseBill?->invoice_date?->format('d/m/Y') ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No purchase bill records found.</td>
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

