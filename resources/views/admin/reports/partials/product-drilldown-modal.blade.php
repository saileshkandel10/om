<div class="product-drilldown-content">
    <div class="card mb-3 border-0 bg-light-subtle shadow-none">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">{{ $productName }}</h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        {{ $product?->category?->name ?? 'Pharmaceutical' }}
                    </span>
                    @if($product?->sku)
                        <span class="badge bg-light text-muted border ms-1">SKU: {{ $product->sku }}</span>
                    @endif
                </div>
                <div class="text-end">
                    <span class="fs-18 fw-bold text-primary">Rs. {{ number_format($totalRev, 2) }}</span>
                    <small class="text-muted d-block">{{ number_format($totalUnits) }} units across {{ number_format($totalOrders) }} orders</small>
                </div>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-2 fs-14">Geographic Demand by District</h6>
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>District</th>
                    <th>Province</th>
                    <th class="text-end">Units Sold</th>
                    <th class="text-end">Orders</th>
                    <th class="text-end">Customers</th>
                    <th class="text-end">Revenue (NPR)</th>
                    <th class="text-end">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($districtBreakdown as $row)
                    @php
                        $pct = $totalUnits > 0 ? round(($row->units_sold / $totalUnits) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $row->district_name }}</strong></td>
                        <td><span class="badge bg-light text-dark border">{{ $row->province_name }}</span></td>
                        <td class="text-end fw-bold text-primary">{{ number_format($row->units_sold) }}</td>
                        <td class="text-end">{{ number_format($row->orders_count) }}</td>
                        <td class="text-end">{{ number_format($row->customers_count) }}</td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($row->revenue, 2) }}</td>
                        <td class="text-end">{{ $pct }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted">No purchase records found for this product.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
