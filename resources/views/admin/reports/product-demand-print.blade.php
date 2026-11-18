<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Product Demand & Purchasing Behaviour Report — OM Connect</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #212529;
            background: #fff;
            padding: 20px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
        .report-header {
            border-bottom: 2px solid #CD0B6F;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
        }
        .summary-box .num {
            font-size: 20px;
            font-weight: bold;
            color: #CD0B6F;
        }
        table {
            font-size: 11px;
        }
        th {
            background-color: #f1f5f9 !important;
            color: #334155;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <i class="las la-print"></i> Print Report
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">
            Close
        </button>
    </div>

    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-0" style="color: #CD0B6F;">OM CONNECT</h3>
            <p class="text-muted mb-0">Product Demand & Geographic Purchasing Behaviour Report (Nepal 77 Districts)</p>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">Period: {{ $reportData['date_range']['start'] }} to {{ $reportData['date_range']['end'] }}</small>
            <small class="text-muted d-block">Generated: {{ \Carbon\Carbon::now()->format('M d, Y h:i A') }}</small>
        </div>
    </div>

    {{-- Summary KPIs --}}
    <div class="row g-2 mb-4">
        <div class="col">
            <div class="summary-box">
                <div class="text-muted small">Total Orders</div>
                <div class="num">{{ number_format($reportData['summary']['total_orders']) }}</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-box">
                <div class="text-muted small">Total Products Sold</div>
                <div class="num">{{ number_format($reportData['summary']['total_units_sold']) }}</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-box">
                <div class="text-muted small">Total Customers</div>
                <div class="num">{{ number_format($reportData['summary']['total_customers']) }}</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-box">
                <div class="text-muted small">Repeat Customers</div>
                <div class="num">{{ number_format($reportData['summary']['repeat_customers']) }} ({{ $reportData['summary']['repeat_customer_rate'] }}%)</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-box">
                <div class="text-muted small">Total Revenue</div>
                <div class="num">Rs. {{ number_format($reportData['summary']['total_revenue'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Top Trending Products --}}
    <h6 class="fw-bold mb-2">Top Trending Products</h6>
    <table class="table table-bordered table-sm mb-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-end">Units Sold</th>
                <th class="text-end">Orders</th>
                <th class="text-end">Customers</th>
                <th class="text-end">Revenue (NPR)</th>
                <th class="text-end">Growth</th>
                <th class="text-end">Demand Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($reportData['trending_products'], 0, 15) as $prod)
                <tr>
                    <td>{{ $prod['rank'] }}</td>
                    <td><strong>{{ $prod['name'] }}</strong></td>
                    <td>{{ $prod['category'] }}</td>
                    <td class="text-end fw-semibold">{{ number_format($prod['units_sold']) }}</td>
                    <td class="text-end">{{ number_format($prod['orders_count']) }}</td>
                    <td class="text-end">{{ number_format($prod['customers_count']) }}</td>
                    <td class="text-end">Rs. {{ number_format($prod['revenue'], 2) }}</td>
                    <td class="text-end">{{ $prod['growth_percent'] > 0 ? '+'.$prod['growth_percent'] : $prod['growth_percent'] }}%</td>
                    <td class="text-end fw-bold">{{ $prod['demand_score'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Top Districts --}}
    <h6 class="fw-bold mb-2">Top Purchasing Districts Across Nepal</h6>
    <table class="table table-bordered table-sm mb-4">
        <thead>
            <tr>
                <th>#</th>
                <th>District</th>
                <th>Province</th>
                <th class="text-end">Orders</th>
                <th class="text-end">Customers</th>
                <th class="text-end">Units Sold</th>
                <th class="text-end">Revenue (NPR)</th>
                <th class="text-end">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($reportData['district_demand'], 0, 15) as $dist)
                <tr>
                    <td>{{ $dist['rank'] }}</td>
                    <td><strong>{{ $dist['name'] }}</strong></td>
                    <td>{{ $dist['province_name'] }}</td>
                    <td class="text-end fw-semibold">{{ number_format($dist['orders_count']) }}</td>
                    <td class="text-end">{{ number_format($dist['customers_count']) }}</td>
                    <td class="text-end">{{ number_format($dist['units_sold']) }}</td>
                    <td class="text-end">Rs. {{ number_format($dist['revenue'], 2) }}</td>
                    <td class="text-end">{{ $dist['order_percentage'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4 pt-3 border-top text-center text-muted small">
        © {{ date('Y') }} OM Connect Medical System — Official Confidential Product Demand & Purchasing Analytics
    </div>
</body>
</html>
