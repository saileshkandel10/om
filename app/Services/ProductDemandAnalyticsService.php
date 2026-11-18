<?php

namespace App\Services;

use App\Models\District;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Province;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductDemandAnalyticsService
{
    /**
     * Resolve date ranges for current period and previous equivalent period.
     *
     * @return array{0: Carbon, 1: Carbon, 2: Carbon, 3: Carbon} [currStart, currEnd, prevStart, prevEnd]
     */
    public function resolveDateRanges(array $filters): array
    {
        $preset = $filters['date_preset'] ?? 'last_30_days';
        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $currStart = $now->copy()->startOfDay();
                $currEnd = $now->copy()->endOfDay();
                $days = 1;
                break;
            case 'yesterday':
                $currStart = $now->copy()->subDay()->startOfDay();
                $currEnd = $now->copy()->subDay()->endOfDay();
                $days = 1;
                break;
            case 'last_7_days':
                $currStart = $now->copy()->subDays(6)->startOfDay();
                $currEnd = $now->copy()->endOfDay();
                $days = 7;
                break;
            case 'this_month':
                $currStart = $now->copy()->startOfMonth();
                $currEnd = $now->copy()->endOfDay();
                $days = max(1, $currStart->diffInDays($currEnd) + 1);
                break;
            case 'last_month':
                $currStart = $now->copy()->subMonth()->startOfMonth();
                $currEnd = $now->copy()->subMonth()->endOfMonth();
                $days = max(1, $currStart->diffInDays($currEnd) + 1);
                break;
            case 'this_quarter':
                $currStart = $now->copy()->startOfQuarter();
                $currEnd = $now->copy()->endOfDay();
                $days = max(1, $currStart->diffInDays($currEnd) + 1);
                break;
            case 'this_year':
                $currStart = $now->copy()->startOfYear();
                $currEnd = $now->copy()->endOfDay();
                $days = max(1, $currStart->diffInDays($currEnd) + 1);
                break;
            case 'custom':
                $currStart = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : $now->copy()->subDays(29)->startOfDay();
                $currEnd = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : $now->copy()->endOfDay();
                $days = max(1, $currStart->diffInDays($currEnd) + 1);
                break;
            case 'all_time':
                $currStart = Carbon::parse('2020-01-01')->startOfDay();
                $currEnd = $now->copy()->endOfDay();
                $days = max(1, $currStart->diffInDays($currEnd) + 1);
                break;
            case 'last_30_days':
            default:
                $currStart = $now->copy()->subDays(29)->startOfDay();
                $currEnd = $now->copy()->endOfDay();
                $days = 30;
                break;
        }

        $prevEnd = $currStart->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subDays((int) $days)->startOfDay();

        return [$currStart, $currEnd, $prevStart, $prevEnd];
    }

    /**
     * Build base query for qualifying purchase bills / orders with applied filters.
     */
    public function buildBillsQuery(array $filters, ?Carbon $startDate = null, ?Carbon $endDate = null): Builder
    {
        $query = PurchaseBill::query();

        // 1. Order Status Filter (Default: exclude cancelled orders unless explicitly asked)
        if (! empty($filters['order_status']) && $filters['order_status'] !== 'all') {
            $query->where('order_status', $filters['order_status']);
        } else {
            $query->where('order_status', '!=', 'cancelled')
                ->where('validation_status', '!=', 'rejected');
        }

        // 2. Payment Status Filter
        if (! empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }

        // 3. Date constraints
        if ($startDate) {
            $startStr = $startDate->format('Y-m-d 00:00:00');
            $query->where(function (Builder $q) use ($startStr) {
                $q->where('invoice_date', '>=', $startStr)
                    ->orWhere(function (Builder $sub) use ($startStr) {
                        $sub->whereNull('invoice_date')->where('created_at', '>=', $startStr);
                    });
            });
        }
        if ($endDate) {
            $endStr = $endDate->format('Y-m-d 23:59:59');
            $query->where(function (Builder $q) use ($endStr) {
                $q->where('invoice_date', '<=', $endStr)
                    ->orWhere(function (Builder $sub) use ($endStr) {
                        $sub->whereNull('invoice_date')->where('created_at', '<=', $endStr);
                    });
            });
        }

        // 4. Province Filter
        if (! empty($filters['province_id']) && $filters['province_id'] !== 'all') {
            $query->where('province_id', $filters['province_id']);
        }

        // 5. District Filter
        if (! empty($filters['district_id']) && $filters['district_id'] !== 'all') {
            $query->where('district_id', $filters['district_id']);
        }

        // 6. Product / Product Category / Brand Filters
        if (! empty($filters['product_id']) && $filters['product_id'] !== 'all') {
            $prodId = $filters['product_id'];
            $query->whereHas('items', fn ($iq) => $iq->where('product_id', $prodId));
        }

        if (! empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $catId = $filters['category_id'];
            $query->whereHas('items.product', fn ($pq) => $pq->where('product_category_id', $catId));
        }

        if (! empty($filters['illness_category_id']) && $filters['illness_category_id'] !== 'all') {
            $illCatId = $filters['illness_category_id'];
            $query->whereHas('items.product', fn ($pq) => $pq->where('illness_category_id', $illCatId));
        }

        if (! empty($filters['brand'])) {
            $brand = trim((string) $filters['brand']);
            $query->whereHas('items.product', fn ($pq) => $pq->where('brand_name', 'like', "%{$brand}%"));
        }

        // 7. Customer Type (New vs Returning)
        if (! empty($filters['customer_type']) && $filters['customer_type'] !== 'all') {
            if ($filters['customer_type'] === 'new' && $startDate) {
                $query->whereHas('user', function (Builder $uq) use ($startDate) {
                    $uq->whereHas('purchaseBills', function (Builder $bq) use ($startDate) {
                        $bq->havingRaw('MIN(created_at) >= ?', [$startDate]);
                    });
                });
            } elseif ($filters['customer_type'] === 'returning') {
                $query->whereHas('user', function (Builder $uq) {
                    $uq->has('purchaseBills', '>', 1);
                });
            }
        }

        return $query;
    }

    /**
     * Compute full report dashboard dataset.
     */
    public function getDemandReportData(array $filters): array
    {
        [$currStart, $currEnd, $prevStart, $prevEnd] = $this->resolveDateRanges($filters);

        $currQuery = $this->buildBillsQuery($filters, $currStart, $currEnd);
        $prevQuery = $this->buildBillsQuery($filters, $prevStart, $prevEnd);

        $billIds = (clone $currQuery)->pluck('id')->toArray();
        $customerIds = (clone $currQuery)->whereNotNull('user_id')->pluck('user_id')->unique()->toArray();

        // 1. Dashboard Summary KPI Cards
        $totalOrders = count($billIds);
        $totalRevenue = (float) (clone $currQuery)->sum('total_amount');
        $totalCustomers = count($customerIds);
        $averageOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0.0;

        // Total products sold and unique products
        $itemStats = empty($billIds) ? null : DB::table('purchase_bill_items')
            ->whereIn('purchase_bill_id', $billIds)
            ->selectRaw('SUM(COALESCE(quantity, 1)) as total_units, COUNT(DISTINCT COALESCE(product_id, product_name_raw)) as unique_products')
            ->first();

        $totalUnitsSold = (float) ($itemStats?->total_units ?? 0);
        $uniqueProductsPurchased = (int) ($itemStats?->unique_products ?? 0);

        // Repeat customers count (customers with >1 order in lifetime/period)
        $repeatCustomersCount = empty($customerIds) ? 0 : PurchaseBill::whereIn('user_id', $customerIds)
            ->where('order_status', '!=', 'cancelled')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $newCustomersCount = max(0, $totalCustomers - $repeatCustomersCount);
        $repeatCustomerRate = $totalCustomers > 0 ? round(($repeatCustomersCount / $totalCustomers) * 100, 1) : 0.0;

        // Top Purchasing District & Most Demanded Product
        $topDistrictRow = empty($billIds) ? null : District::whereHas('purchaseBills', fn ($q) => $q->whereIn('id', $billIds))
            ->withCount(['purchaseBills' => fn ($q) => $q->whereIn('id', $billIds)])
            ->orderByDesc('purchase_bills_count')
            ->first();

        $topPurchasingDistrict = $topDistrictRow?->name ?? '—';

        $topProductRow = empty($billIds) ? null : DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->whereIn('purchase_bill_items.purchase_bill_id', $billIds)
            ->selectRaw('COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name, SUM(COALESCE(purchase_bill_items.quantity, 1)) as units')
            ->groupBy('product_name')
            ->orderByDesc('units')
            ->first();

        $mostDemandedProduct = $topProductRow?->product_name ?? '—';

        // 2. Top Trending Products with Growth & Demand Score
        $trendingProducts = $this->calculateTrendingProducts($filters, $currStart, $currEnd, $prevStart, $prevEnd);

        // 3. Geographic District Demand & Ranking
        $districtDemand = $this->calculateDistrictDemand($filters, $currStart, $currEnd, $totalRevenue, $totalOrders);

        // 4. Province-Level Demand
        $provinceDemand = $this->calculateProvinceDemand($filters, $currStart, $currEnd, $totalRevenue);

        // 5. Geographic Coverage (Nepal 77 Districts)
        $districtCoverage = $this->calculateDistrictCoverage($billIds);

        // 6. Product Demand Time-Series Chart
        $timeSeriesDemand = $this->calculateDemandTimeSeries($filters, $currStart, $currEnd);

        // 7. Customer Purchasing Behaviour & Frequency Analysis
        $customerBehaviour = $this->calculateCustomerBehaviour($customerIds, $billIds, $totalOrders, $totalRevenue, $totalUnitsSold);

        // 8. Purchasing Behaviour by Day of Week & Hour
        $timeAnalysis = $this->calculatePurchaseTimeAnalysis($billIds);

        // 9. Product Category Performance
        $categoryDemand = $this->calculateCategoryDemand($billIds);

        // 10. Product × District Matrix
        $productDistrictMatrix = $this->calculateProductDistrictMatrix($billIds);

        // 11. Most Demanded Product for Each District
        $districtTopProducts = $this->calculateDistrictTopProducts($billIds);

        // 12. Fastest Growing and Low Demand Products
        $fastestGrowing = collect($trendingProducts)->filter(fn ($p) => $p['growth_percent'] > 0)->sortByDesc('growth_percent')->take(10)->values()->all();
        $lowDemandProducts = $this->calculateLowDemandProducts();

        // 13. Dropdowns Metadata
        $provinces = Province::with('activeDistricts')->where('status', 'active')->orderBy('id')->get();
        $districts = District::with('province')->where('status', 'active')->orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        $productsList = Product::where('is_active', true)->orderBy('brand_name')->get();

        return [
            'summary' => [
                'total_orders' => $totalOrders,
                'total_units_sold' => $totalUnitsSold,
                'unique_products_purchased' => $uniqueProductsPurchased,
                'total_customers' => $totalCustomers,
                'repeat_customers' => $repeatCustomersCount,
                'new_customers' => $newCustomersCount,
                'repeat_customer_rate' => $repeatCustomerRate,
                'average_order_value' => $averageOrderValue,
                'most_demanded_product' => $mostDemandedProduct,
                'top_purchasing_district' => $topPurchasingDistrict,
                'total_revenue' => $totalRevenue,
            ],
            'trending_products' => $trendingProducts,
            'fastest_growing' => $fastestGrowing,
            'low_demand_products' => $lowDemandProducts,
            'district_demand' => $districtDemand,
            'province_demand' => $provinceDemand,
            'district_coverage' => $districtCoverage,
            'time_series_demand' => $timeSeriesDemand,
            'customer_behaviour' => $customerBehaviour,
            'time_analysis' => $timeAnalysis,
            'category_demand' => $categoryDemand,
            'product_district_matrix' => $productDistrictMatrix,
            'district_top_products' => $districtTopProducts,
            'provinces' => $provinces,
            'districts' => $districts,
            'categories' => $categories,
            'products' => $productsList,
            'filters' => $filters,
            'date_range' => [
                'start' => $currStart->format('Y-m-d'),
                'end' => $currEnd->format('Y-m-d'),
                'prev_start' => $prevStart->format('Y-m-d'),
                'prev_end' => $prevEnd->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Calculate trending products ranked by configurable demand score and growth.
     */
    protected function calculateTrendingProducts(array $filters, Carbon $currStart, Carbon $currEnd, Carbon $prevStart, Carbon $prevEnd): array
    {
        $currBills = $this->buildBillsQuery($filters, $currStart, $currEnd)->pluck('id')->toArray();
        $prevBills = $this->buildBillsQuery($filters, $prevStart, $prevEnd)->pluck('id')->toArray();

        if (empty($currBills)) {
            return [];
        }

        // Current period product stats
        $currItems = DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->whereIn('purchase_bill_items.purchase_bill_id', $currBills)
            ->selectRaw('
                COALESCE(products.id, 0) as product_id,
                COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name,
                COALESCE(product_categories.name, "General") as category_name,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count,
                COUNT(DISTINCT purchase_bills.user_id) as customers_count,
                SUM(COALESCE(purchase_bill_items.amount, COALESCE(purchase_bill_items.quantity, 1) * COALESCE(purchase_bill_items.unit_price, 0))) as revenue
            ')
            ->groupByRaw('COALESCE(products.id, 0), COALESCE(products.brand_name, purchase_bill_items.product_name_raw), COALESCE(product_categories.name, "General")')
            ->get();

        // Previous period product stats for growth
        $prevUnitsMap = [];
        if (! empty($prevBills)) {
            $prevRows = DB::table('purchase_bill_items')
                ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
                ->whereIn('purchase_bill_items.purchase_bill_id', $prevBills)
                ->selectRaw('COALESCE(products.id, 0) as product_id, COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name, SUM(COALESCE(purchase_bill_items.quantity, 1)) as prev_units')
                ->groupByRaw('COALESCE(products.id, 0), COALESCE(products.brand_name, purchase_bill_items.product_name_raw)')
                ->get();

            foreach ($prevRows as $pr) {
                $prevUnitsMap[$pr->product_name] = (float) $pr->prev_units;
            }
        }

        $results = [];

        foreach ($currItems as $row) {
            $units = (float) $row->units_sold;
            $orders = (int) $row->orders_count;
            $customers = (int) $row->customers_count;
            $revenue = (float) $row->revenue;
            $prevUnits = $prevUnitsMap[$row->product_name] ?? 0.0;

            if ($prevUnits > 0) {
                $growth = round((($units - $prevUnits) / $prevUnits) * 100, 1);
            } else {
                $growth = $units > 0 ? 100.0 : 0.0;
            }

            $growthBonus = max(0, min(100, $growth)) * 0.15;
            $demandScore = round(($units * 0.40) + ($orders * 2.5) + ($customers * 2.0) + $growthBonus, 1);

            $results[] = [
                'product_id' => $row->product_id,
                'name' => $row->product_name,
                'category' => $row->category_name,
                'units_sold' => $units,
                'previous_units' => $prevUnits,
                'orders_count' => $orders,
                'customers_count' => $customers,
                'revenue' => $revenue,
                'growth_percent' => $growth,
                'demand_score' => $demandScore,
            ];
        }

        usort($results, fn ($a, $b) => $b['demand_score'] <=> $a['demand_score']);

        $rank = 1;
        foreach ($results as &$item) {
            $item['rank'] = $rank++;
        }

        return $results;
    }

    /**
     * Calculate district-level demand ranking across Nepal.
     */
    protected function calculateDistrictDemand(array $filters, Carbon $currStart, Carbon $currEnd, float $totalRevenue, int $totalOrders): array
    {
        $bills = $this->buildBillsQuery($filters, $currStart, $currEnd)->get();
        $districts = District::with('province')->where('status', 'active')->orderBy('name')->get();

        $districtStats = [];
        $billsByDistrict = $bills->groupBy('district_id');

        foreach ($districts as $district) {
            $dBills = $billsByDistrict->get($district->id, collect());
            $orders = $dBills->count();
            $rev = (float) $dBills->sum('total_amount');
            $customers = $dBills->whereNotNull('user_id')->pluck('user_id')->unique()->count();

            $dBillIds = $dBills->pluck('id')->toArray();
            $units = empty($dBillIds) ? 0 : (float) DB::table('purchase_bill_items')
                ->whereIn('purchase_bill_id', $dBillIds)
                ->sum(DB::raw('COALESCE(quantity, 1)'));

            $orderPct = $totalOrders > 0 ? round(($orders / $totalOrders) * 100, 1) : 0.0;
            $revPct = $totalRevenue > 0 ? round(($rev / $totalRevenue) * 100, 1) : 0.0;

            $districtStats[] = [
                'district_id' => $district->id,
                'name' => $district->name,
                'code' => $district->code,
                'province_id' => $district->province_id,
                'province_name' => $district->province?->name ?? 'Unknown',
                'orders_count' => $orders,
                'customers_count' => $customers,
                'units_sold' => $units,
                'revenue' => $rev,
                'order_percentage' => $orderPct,
                'revenue_percentage' => $revPct,
                'has_demand' => $orders > 0,
            ];
        }

        usort($districtStats, fn ($a, $b) => $b['orders_count'] <=> $a['orders_count']);

        $rank = 1;
        foreach ($districtStats as &$ds) {
            $ds['rank'] = $rank++;
        }

        return $districtStats;
    }

    /**
     * Calculate Province-Level Demand.
     */
    protected function calculateProvinceDemand(array $filters, Carbon $currStart, Carbon $currEnd, float $totalRevenue): array
    {
        $bills = $this->buildBillsQuery($filters, $currStart, $currEnd)->get();
        $provinces = Province::with('districts')->where('status', 'active')->orderBy('id')->get();

        $result = [];
        $billsByProvince = $bills->groupBy('province_id');

        foreach ($provinces as $prov) {
            $pBills = $billsByProvince->get($prov->id, collect());
            $orders = $pBills->count();
            $rev = (float) $pBills->sum('total_amount');
            $customers = $pBills->whereNotNull('user_id')->pluck('user_id')->unique()->count();

            $pBillIds = $pBills->pluck('id')->toArray();
            $units = empty($pBillIds) ? 0 : (float) DB::table('purchase_bill_items')
                ->whereIn('purchase_bill_id', $pBillIds)
                ->sum(DB::raw('COALESCE(quantity, 1)'));

            $pct = $totalRevenue > 0 ? round(($rev / $totalRevenue) * 100, 1) : 0.0;

            $result[] = [
                'province_id' => $prov->id,
                'name' => $prov->name,
                'code' => $prov->code,
                'districts_count' => $prov->districts->count(),
                'orders_count' => $orders,
                'customers_count' => $customers,
                'units_sold' => $units,
                'revenue' => $rev,
                'revenue_percentage' => $pct,
            ];
        }

        return $result;
    }

    /**
     * Calculate Geographic Demand Coverage for all 77 districts of Nepal.
     */
    protected function calculateDistrictCoverage(array $billIds): array
    {
        $allDistricts = District::with('province')->where('status', 'active')->orderBy('name')->get();
        $totalDistrictsCount = $allDistricts->count();

        $activeDistrictIds = empty($billIds) ? [] : PurchaseBill::whereIn('id', $billIds)
            ->whereNotNull('district_id')
            ->pluck('district_id')
            ->unique()
            ->toArray();

        $coveredDistricts = [];
        $zeroDemandDistricts = [];

        foreach ($allDistricts as $dist) {
            if (in_array($dist->id, $activeDistrictIds, true)) {
                $coveredDistricts[] = $dist;
            } else {
                $zeroDemandDistricts[] = [
                    'id' => $dist->id,
                    'name' => $dist->name,
                    'province_name' => $dist->province?->name ?? 'Nepal',
                    'code' => $dist->code,
                ];
            }
        }

        $coveredCount = count($coveredDistricts);
        $zeroCount = count($zeroDemandDistricts);
        $coveragePct = $totalDistrictsCount > 0 ? round(($coveredCount / $totalDistrictsCount) * 100, 1) : 0.0;

        return [
            'total_districts' => $totalDistrictsCount,
            'covered_districts_count' => $coveredCount,
            'zero_demand_districts_count' => $zeroCount,
            'coverage_percentage' => $coveragePct,
            'zero_demand_districts' => $zeroDemandDistricts,
        ];
    }

    /**
     * Calculate time-series demand (Daily / Weekly / Monthly).
     */
    protected function calculateDemandTimeSeries(array $filters, Carbon $start, Carbon $end): array
    {
        $bills = $this->buildBillsQuery($filters, $start, $end)->with('items')->get();

        $daysCount = $start->diffInDays($end) + 1;
        $groupBy = $daysCount <= 31 ? 'daily' : ($daysCount <= 90 ? 'weekly' : 'monthly');

        $labels = [];
        $unitsData = [];
        $ordersData = [];
        $revenueData = [];

        if ($groupBy === 'daily') {
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');
                $labels[] = $d->format('M d');

                $dayBills = $bills->filter(function ($b) use ($dateKey) {
                    $bDate = $b->invoice_date ? $b->invoice_date->format('Y-m-d') : $b->created_at->format('Y-m-d');

                    return $bDate === $dateKey;
                });

                $ordersData[] = $dayBills->count();
                $revenueData[] = (float) $dayBills->sum('total_amount');
                $unitsData[] = (float) $dayBills->sum(fn ($b) => $b->items->sum('quantity') ?: 1);
            }
        } elseif ($groupBy === 'weekly') {
            for ($d = $start->copy(); $d->lte($end); $d->addWeek()) {
                $weekEnd = $d->copy()->endOfWeek();
                $labels[] = $d->format('M d').' - '.$weekEnd->format('M d');

                $weekBills = $bills->filter(function ($b) use ($d, $weekEnd) {
                    $bDate = $b->invoice_date ?? $b->created_at;

                    return $bDate >= $d && $bDate <= $weekEnd;
                });

                $ordersData[] = $weekBills->count();
                $revenueData[] = (float) $weekBills->sum('total_amount');
                $unitsData[] = (float) $weekBills->sum(fn ($b) => $b->items->sum('quantity') ?: 1);
            }
        } else {
            for ($m = $start->copy()->startOfMonth(); $m->lte($end); $m->addMonth()) {
                $monthKey = $m->format('Y-m');
                $labels[] = $m->format('M Y');

                $monthBills = $bills->filter(function ($b) use ($monthKey) {
                    $bDate = $b->invoice_date ? $b->invoice_date->format('Y-m') : $b->created_at->format('Y-m');

                    return $bDate === $monthKey;
                });

                $ordersData[] = $monthBills->count();
                $revenueData[] = (float) $monthBills->sum('total_amount');
                $unitsData[] = (float) $monthBills->sum(fn ($b) => $b->items->sum('quantity') ?: 1);
            }
        }

        return [
            'mode' => $groupBy,
            'labels' => $labels,
            'units' => $unitsData,
            'orders' => $ordersData,
            'revenue' => $revenueData,
        ];
    }

    /**
     * Customer Purchasing Behaviour Analysis (Frequency brackets, AOV, repeat metrics).
     */
    protected function calculateCustomerBehaviour(array $customerIds, array $billIds, int $totalOrders, float $totalRevenue, float $totalUnits): array
    {
        $frequencyBrackets = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5+' => 0,
        ];

        if (! empty($customerIds)) {
            $userCounts = PurchaseBill::whereIn('user_id', $customerIds)
                ->where('order_status', '!=', 'cancelled')
                ->select('user_id', DB::raw('COUNT(*) as total_purchases'))
                ->groupBy('user_id')
                ->get();

            foreach ($userCounts as $uc) {
                $c = (int) $uc->total_purchases;
                if ($c === 1) {
                    $frequencyBrackets['1']++;
                } elseif ($c === 2) {
                    $frequencyBrackets['2']++;
                } elseif ($c === 3) {
                    $frequencyBrackets['3']++;
                } elseif ($c === 4) {
                    $frequencyBrackets['4']++;
                } else {
                    $frequencyBrackets['5+']++;
                }
            }
        }

        $totalCust = count($customerIds);
        $avgOrdersPerCustomer = $totalCust > 0 ? round($totalOrders / $totalCust, 2) : 0.0;
        $avgProductsPerOrder = $totalOrders > 0 ? round($totalUnits / $totalOrders, 2) : 0.0;
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0.0;

        // Monthly Customer Behaviour Table (last 6 months)
        $monthlyBehaviour = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $mBills = PurchaseBill::whereBetween('created_at', [$mStart, $mEnd])
                ->where('order_status', '!=', 'cancelled')
                ->get();

            $mOrders = $mBills->count();
            $mRev = (float) $mBills->sum('total_amount');
            $mCusts = $mBills->whereNotNull('user_id')->pluck('user_id')->unique()->toArray();
            $mTotalCusts = count($mCusts);

            $mRepeat = empty($mCusts) ? 0 : PurchaseBill::whereIn('user_id', $mCusts)
                ->where('created_at', '<', $mStart)
                ->where('order_status', '!=', 'cancelled')
                ->distinct('user_id')
                ->count('user_id');

            $mNew = max(0, $mTotalCusts - $mRepeat);
            $mAov = $mOrders > 0 ? round($mRev / $mOrders, 2) : 0.0;
            $mRate = $mTotalCusts > 0 ? round(($mRepeat / $mTotalCusts) * 100, 1) : 0.0;

            $monthlyBehaviour[] = [
                'month' => $mStart->format('M Y'),
                'new_customers' => $mNew,
                'returning_customers' => $mRepeat,
                'orders_count' => $mOrders,
                'aov' => $mAov,
                'repeat_rate' => $mRate,
                'revenue' => $mRev,
            ];
        }

        return [
            'frequency_brackets' => $frequencyBrackets,
            'avg_orders_per_customer' => $avgOrdersPerCustomer,
            'avg_products_per_order' => $avgProductsPerOrder,
            'avg_order_value' => $avgOrderValue,
            'monthly_behaviour' => $monthlyBehaviour,
        ];
    }

    /**
     * Purchasing Behaviour by Day of Week & Hour.
     */
    protected function calculatePurchaseTimeAnalysis(array $billIds): array
    {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $daysCounts = array_fill_keys($dayNames, 0);
        $hoursCounts = array_fill(0, 24, 0);

        if (! empty($billIds)) {
            $bills = PurchaseBill::whereIn('id', $billIds)->select('created_at', 'invoice_date')->get();
            foreach ($bills as $b) {
                $dt = $b->created_at ?? ($b->invoice_date ? Carbon::parse($b->invoice_date) : now());
                $day = $dt->format('l');
                if (isset($daysCounts[$day])) {
                    $daysCounts[$day]++;
                }
                $hour = (int) $dt->format('G');
                if (isset($hoursCounts[$hour])) {
                    $hoursCounts[$hour]++;
                }
            }
        }

        return [
            'days' => $daysCounts,
            'hours' => $hoursCounts,
        ];
    }

    /**
     * Product Category Demand breakdown with growth.
     */
    protected function calculateCategoryDemand(array $billIds): array
    {
        if (empty($billIds)) {
            return [];
        }

        $rows = DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->whereIn('purchase_bill_items.purchase_bill_id', $billIds)
            ->selectRaw('
                COALESCE(product_categories.id, 0) as category_id,
                COALESCE(product_categories.name, "General Products") as category_name,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count,
                COUNT(DISTINCT purchase_bills.user_id) as customers_count,
                SUM(COALESCE(purchase_bill_items.amount, COALESCE(purchase_bill_items.quantity, 1) * COALESCE(purchase_bill_items.unit_price, 0))) as revenue
            ')
            ->groupByRaw('COALESCE(product_categories.id, 0), COALESCE(product_categories.name, "General Products")')
            ->orderByDesc('units_sold')
            ->get();

        $result = [];
        $totalRev = $rows->sum('revenue');

        foreach ($rows as $r) {
            $rev = (float) $r->revenue;
            $pct = $totalRev > 0 ? round(($rev / $totalRev) * 100, 1) : 0.0;

            $result[] = [
                'category_id' => $r->category_id,
                'name' => $r->category_name,
                'units_sold' => (float) $r->units_sold,
                'orders_count' => (int) $r->orders_count,
                'customers_count' => (int) $r->customers_count,
                'revenue' => $rev,
                'percentage' => $pct,
            ];
        }

        return $result;
    }

    /**
     * Product × District Cross-Tabulation Matrix.
     */
    protected function calculateProductDistrictMatrix(array $billIds): array
    {
        if (empty($billIds)) {
            return ['products' => [], 'districts' => [], 'matrix' => []];
        }

        $topProducts = DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->whereIn('purchase_bill_items.purchase_bill_id', $billIds)
            ->selectRaw('COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name, SUM(COALESCE(purchase_bill_items.quantity, 1)) as units')
            ->groupByRaw('COALESCE(products.brand_name, purchase_bill_items.product_name_raw)')
            ->orderByDesc('units')
            ->limit(8)
            ->pluck('product_name')
            ->toArray();

        $topDistricts = PurchaseBill::whereIn('purchase_bills.id', $billIds)
            ->join('districts', 'purchase_bills.district_id', '=', 'districts.id')
            ->select('districts.name', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('districts.name')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->pluck('districts.name')
            ->toArray();

        $crossRows = DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->join('districts', 'purchase_bills.district_id', '=', 'districts.id')
            ->whereIn('purchase_bills.id', $billIds)
            ->whereIn(DB::raw('COALESCE(products.brand_name, purchase_bill_items.product_name_raw)'), $topProducts)
            ->whereIn('districts.name', $topDistricts)
            ->selectRaw('districts.name as district_name, COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name, SUM(COALESCE(purchase_bill_items.quantity, 1)) as units')
            ->groupByRaw('districts.name, COALESCE(products.brand_name, purchase_bill_items.product_name_raw)')
            ->get();

        $matrix = [];
        foreach ($topDistricts as $dist) {
            $matrix[$dist] = [];
            $distTotal = 0;
            foreach ($topProducts as $prod) {
                $matched = $crossRows->first(fn ($r) => $r->district_name === $dist && $r->product_name === $prod);
                $u = $matched ? (float) $matched->units : 0;
                $matrix[$dist][$prod] = $u;
                $distTotal += $u;
            }
            $matrix[$dist]['_total'] = $distTotal;
        }

        return [
            'products' => $topProducts,
            'districts' => $topDistricts,
            'matrix' => $matrix,
        ];
    }

    /**
     * Top product for each district across Nepal.
     */
    protected function calculateDistrictTopProducts(array $billIds): array
    {
        if (empty($billIds)) {
            return [];
        }

        $rows = DB::table('purchase_bill_items')
            ->leftJoin('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->join('districts', 'purchase_bills.district_id', '=', 'districts.id')
            ->whereIn('purchase_bills.id', $billIds)
            ->selectRaw('
                districts.id as district_id,
                districts.name as district_name,
                COALESCE(products.brand_name, purchase_bill_items.product_name_raw) as product_name,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count
            ')
            ->groupByRaw('districts.id, districts.name, COALESCE(products.brand_name, purchase_bill_items.product_name_raw)')
            ->orderBy('districts.name')
            ->orderByDesc('units_sold')
            ->get()
            ->groupBy('district_name');

        $result = [];
        foreach ($rows as $distName => $items) {
            $top = $items->first();
            if ($top) {
                $result[] = [
                    'district_name' => $distName,
                    'top_product' => $top->product_name,
                    'units_sold' => (float) $top->units_sold,
                    'orders_count' => (int) $top->orders_count,
                ];
            }
        }

        return $result;
    }

    /**
     * Low Demand / Inactive Products.
     */
    protected function calculateLowDemandProducts(): array
    {
        $allProducts = Product::with('category')->where('is_active', true)->get();
        $result = [];

        foreach ($allProducts as $p) {
            $lastItem = PurchaseBillItem::where('product_id', $p->id)
                ->latest('created_at')
                ->first();

            $totalUnits = (float) PurchaseBillItem::where('product_id', $p->id)->sum('quantity');
            $orders = PurchaseBillItem::where('product_id', $p->id)->count();

            $lastDate = $lastItem?->created_at;
            $daysSince = $lastDate ? Carbon::now()->diffInDays($lastDate) : 999;

            if ($totalUnits <= 5) {
                $result[] = [
                    'product_id' => $p->id,
                    'name' => $p->brand_name,
                    'category' => $p->category?->name ?? 'General',
                    'units_sold' => $totalUnits,
                    'orders_count' => $orders,
                    'last_sale_date' => $lastDate ? $lastDate->format('M d, Y') : 'Never Sold',
                    'days_since_sale' => $daysSince === 999 ? '—' : $daysSince.' days ago',
                    'revenue' => (float) PurchaseBillItem::where('product_id', $p->id)->sum('amount'),
                ];
            }
        }

        return array_slice($result, 0, 15);
    }
}
