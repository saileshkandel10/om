<div class="district-drilldown-content">
    <div class="card mb-3 border-0 bg-light-subtle shadow-none">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">{{ $district->name }} District</h4>
                    <span class="badge bg-primary">{{ $district->province?->name ?? 'Nepal' }}</span>
                    @if($district->code)
                        <span class="badge bg-light text-muted border ms-1">Code: {{ $district->code }}</span>
                    @endif
                </div>
                <div class="text-end">
                    <span class="fs-18 fw-bold text-success">Rs. {{ number_format($totalRevenue, 2) }}</span>
                    <small class="text-muted d-block">{{ number_format($totalOrders) }} orders · {{ number_format($totalCustomers) }} customers</small>
                </div>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-2 fs-14">Top Products Purchased in {{ $district->name }}</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th class="text-end">Units Sold</th>
                    <th class="text-end">Orders</th>
                    <th class="text-end">Revenue (NPR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productsBreakdown as $prod)
                    <tr>
                        <td><strong>{{ $prod->product_name }}</strong></td>
                        <td class="text-end fw-bold text-primary">{{ number_format($prod->units_sold) }}</td>
                        <td class="text-end">{{ number_format($prod->orders_count) }}</td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($prod->revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-3 text-muted">No product purchases recorded in {{ $district->name }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h6 class="fw-bold mb-2 fs-14">Recent Orders in {{ $district->name }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills->take(10) as $b)
                    <tr>
                        <td><code>#{{ $b->invoice_number ?? $b->id }}</code></td>
                        <td>{{ $b->invoice_date ? $b->invoice_date->format('M d, Y') : $b->created_at->format('M d, Y') }}</td>
                        <td>{{ $b->user?->name ?? 'Direct Customer' }}</td>
                        <td><span class="badge bg-success-subtle text-success">{{ ucfirst($b->order_status ?? 'delivered') }}</span></td>
                        <td class="text-end fw-bold">Rs. {{ number_format($b->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No orders recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
