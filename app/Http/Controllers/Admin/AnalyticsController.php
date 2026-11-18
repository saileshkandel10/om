<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\RewardTransaction;
use App\Models\ShareholderAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function sales(Request $request): View
    {
        $period = $request->input('period', 'all');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');
        $categoryId = $request->input('category_id');
        $productId = $request->input('product_id');
        $pharmacyId = $request->input('pharmacy_id');
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_30_days':
                $startDate = now()->subDays(29)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'this_month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'custom':
                if ($startDateInput) {
                    $startDate = Carbon::parse($startDateInput)->startOfDay();
                }
                if ($endDateInput) {
                    $endDate = Carbon::parse($endDateInput)->endOfDay();
                }
                break;
        }

        $applyItemFilters = function ($query) use ($startDate, $endDate, $categoryId, $productId, $pharmacyId, $status, $search) {
            if ($startDate && $endDate) {
                $query->whereBetween(DB::raw('COALESCE(purchase_bills.invoice_date, purchase_bills.created_at)'), [
                    $startDate->toDateTimeString(),
                    $endDate->toDateTimeString(),
                ]);
            } elseif ($startDate) {
                $query->where(DB::raw('COALESCE(purchase_bills.invoice_date, purchase_bills.created_at)'), '>=', $startDate->toDateTimeString());
            } elseif ($endDate) {
                $query->where(DB::raw('COALESCE(purchase_bills.invoice_date, purchase_bills.created_at)'), '<=', $endDate->toDateTimeString());
            }

            if ($categoryId && $categoryId !== 'all') {
                $query->where('products.product_category_id', $categoryId);
            }

            if ($productId && $productId !== 'all') {
                $query->where('products.id', $productId);
            }

            if ($pharmacyId && $pharmacyId !== 'all') {
                $query->where('purchase_bills.pharmacy_id', $pharmacyId);
            }

            if ($status && $status !== 'all') {
                $query->where('purchase_bills.validation_status', $status);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('products.brand_name', 'like', "%{$search}%")
                        ->orWhere('products.generic_name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%")
                        ->orWhere('purchase_bills.invoice_number', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%")
                        ->orWhere('pharmacies.store_name', 'like', "%{$search}%")
                        ->orWhere('purchase_bills.pharmacy_name', 'like', "%{$search}%");
                });
            }
        };

        // 1. Core KPIs (Company catalogue products only)
        $kpiQuery = DB::table('purchase_bill_items')
            ->join('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('users', 'purchase_bills.user_id', '=', 'users.id')
            ->leftJoin('pharmacies', 'purchase_bills.pharmacy_id', '=', 'pharmacies.id')
            ->tap($applyItemFilters);

        $kpis = $kpiQuery->selectRaw('
            SUM(COALESCE(purchase_bill_items.amount, purchase_bill_items.quantity * purchase_bill_items.unit_price, 0)) as total_company_revenue,
            SUM(COALESCE(purchase_bill_items.quantity, 1)) as total_company_units,
            COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as total_company_invoices,
            COUNT(DISTINCT purchase_bill_items.product_id) as active_company_products_sold,
            AVG(COALESCE(purchase_bill_items.unit_price, 0)) as avg_company_unit_price
        ')->first();

        $totalCompanyRevenue = (float) ($kpis->total_company_revenue ?? 0);
        $totalCompanyUnits = (float) ($kpis->total_company_units ?? 0);
        $totalCompanyInvoices = (int) ($kpis->total_company_invoices ?? 0);
        $activeCompanyProductsSold = (int) ($kpis->active_company_products_sold ?? 0);
        $averageCompanyOrderValue = $totalCompanyInvoices > 0 ? $totalCompanyRevenue / $totalCompanyInvoices : 0;

        // Contextual Reference: Total Gross All-Brand Bills Amount
        $grossBillsQuery = DB::table('purchase_bills')
            ->whereIn('id', function ($q) use ($applyItemFilters) {
                $q->select('purchase_bill_items.purchase_bill_id')
                    ->from('purchase_bill_items')
                    ->join('products', 'purchase_bill_items.product_id', '=', 'products.id')
                    ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
                    ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                    ->leftJoin('users', 'purchase_bills.user_id', '=', 'users.id')
                    ->leftJoin('pharmacies', 'purchase_bills.pharmacy_id', '=', 'pharmacies.id')
                    ->tap($applyItemFilters);
            });
        $grossBillsAmount = (float) ($grossBillsQuery->sum('total_amount') ?? 0);
        $companySharePercentage = $grossBillsAmount > 0 ? ($totalCompanyRevenue / $grossBillsAmount) * 100 : 0;

        // 2. Trend Data (Monthly/Daily) for Company Products
        $driver = DB::getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', COALESCE(purchase_bills.invoice_date, purchase_bills.created_at))"
            : "DATE_FORMAT(COALESCE(purchase_bills.invoice_date, purchase_bills.created_at), '%Y-%m')";

        $salesTrend = DB::table('purchase_bill_items')
            ->join('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('users', 'purchase_bills.user_id', '=', 'users.id')
            ->leftJoin('pharmacies', 'purchase_bills.pharmacy_id', '=', 'pharmacies.id')
            ->tap($applyItemFilters)
            ->selectRaw("
                {$dateExpr} as period_label,
                SUM(COALESCE(purchase_bill_items.amount, purchase_bill_items.quantity * purchase_bill_items.unit_price, 0)) as revenue,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count
            ")
            ->groupBy('period_label')
            ->orderBy('period_label', 'asc')
            ->get();

        // 3. Category Breakdown (Company Products Only)
        $categorySales = DB::table('purchase_bill_items')
            ->join('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('users', 'purchase_bills.user_id', '=', 'users.id')
            ->leftJoin('pharmacies', 'purchase_bills.pharmacy_id', '=', 'pharmacies.id')
            ->tap($applyItemFilters)
            ->selectRaw('
                COALESCE(product_categories.id, 0) as category_id,
                COALESCE(product_categories.name, "Uncategorized") as category_name,
                SUM(COALESCE(purchase_bill_items.amount, purchase_bill_items.quantity * purchase_bill_items.unit_price, 0)) as revenue,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as units_sold,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as orders_count,
                COUNT(DISTINCT purchase_bill_items.product_id) as products_count
            ')
            ->groupBy('category_id', 'category_name')
            ->orderByDesc('revenue')
            ->get();

        // 4. Product-Wise Performance Table (Company Products Only)
        $productSales = DB::table('purchase_bill_items')
            ->join('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('users', 'purchase_bills.user_id', '=', 'users.id')
            ->leftJoin('pharmacies', 'purchase_bills.pharmacy_id', '=', 'pharmacies.id')
            ->tap($applyItemFilters)
            ->selectRaw('
                products.id as product_id,
                products.sku,
                products.brand_name,
                products.generic_name,
                products.strength,
                products.dosage_form,
                products.mrp,
                COALESCE(product_categories.name, "Uncategorized") as category_name,
                SUM(COALESCE(purchase_bill_items.quantity, 1)) as total_units_sold,
                SUM(COALESCE(purchase_bill_items.amount, purchase_bill_items.quantity * purchase_bill_items.unit_price, 0)) as total_revenue,
                COUNT(DISTINCT purchase_bill_items.purchase_bill_id) as total_orders_count,
                AVG(COALESCE(purchase_bill_items.unit_price, 0)) as avg_unit_price
            ')
            ->groupBy('products.id', 'products.sku', 'products.brand_name', 'products.generic_name', 'products.strength', 'products.dosage_form', 'products.mrp', 'category_name')
            ->orderByDesc('total_revenue')
            ->get();

        // 5. Individual Company Product Transactions / Line Items Log
        $companyTransactions = DB::table('purchase_bill_items')
            ->join('products', 'purchase_bill_items.product_id', '=', 'products.id')
            ->join('purchase_bills', 'purchase_bill_items.purchase_bill_id', '=', 'purchase_bills.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('users', 'purchase_bills.user_id', '=', 'users.id')
            ->leftJoin('pharmacies', 'purchase_bills.pharmacy_id', '=', 'pharmacies.id')
            ->tap($applyItemFilters)
            ->select([
                'purchase_bill_items.id as item_id',
                'purchase_bill_items.quantity',
                'purchase_bill_items.unit_price',
                'purchase_bill_items.amount as item_amount',
                'purchase_bill_items.batch_no',
                'purchase_bills.id as bill_id',
                'purchase_bills.invoice_number',
                'purchase_bills.invoice_date',
                'purchase_bills.created_at as bill_created_at',
                'purchase_bills.total_amount as gross_bill_total',
                'purchase_bills.validation_status',
                'products.id as product_id',
                'products.sku',
                'products.brand_name',
                'products.generic_name',
                'products.strength',
                'products.dosage_form',
                'product_categories.name as category_name',
                'users.id as user_id',
                'users.name as user_name',
                'pharmacies.store_name as pharmacy_store_name',
                'purchase_bills.pharmacy_name as bill_pharmacy_name',
            ])
            ->orderBy('purchase_bills.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Dropdown data for filter form
        $categories = ProductCategory::orderBy('name')->get();
        $products = Product::orderBy('brand_name')->get(['id', 'brand_name', 'generic_name', 'sku']);
        $pharmacies = Pharmacy::orderBy('store_name')->get(['id', 'store_name']);
        $totalCatalogueCount = Product::count();

        return view('admin.analytics.sales', compact(
            'totalCompanyRevenue',
            'totalCompanyUnits',
            'totalCompanyInvoices',
            'averageCompanyOrderValue',
            'activeCompanyProductsSold',
            'totalCatalogueCount',
            'grossBillsAmount',
            'companySharePercentage',
            'salesTrend',
            'categorySales',
            'productSales',
            'companyTransactions',
            'categories',
            'products',
            'pharmacies',
            'period',
            'startDateInput',
            'endDateInput',
            'categoryId',
            'productId',
            'pharmacyId',
            'status',
            'search'
        ));
    }

    public function productDemand(): View
    {
        $topProducts = Product::withCount(['purchaseBillItems', 'prescriptionItems'])
            ->havingRaw('purchase_bill_items_count > 0 OR prescription_items_count > 0')
            ->orderByDesc('purchase_bill_items_count')
            ->limit(20)
            ->get();

        return view('admin.analytics.product-demand', compact('topProducts'));
    }

    public function userBehaviour(): View
    {
        $totalUsers = User::count();
        $usersByRole = User::selectRaw('roles.name as role_name, COUNT(*) as count')
            ->join('user_has_roles', 'users.id', '=', 'user_has_roles.user_id')
            ->join('roles', 'user_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->get();

        $activePharmacies = Pharmacy::where('status', 'active')->orWhere('status', 'approved')->count();

        return view('admin.analytics.user-behaviour', compact('totalUsers', 'usersByRole', 'activePharmacies'));
    }

    public function reports(): View
    {
        $stats = [
            'total_revenue' => PurchaseBill::where('validation_status', 'approved')->sum('total_amount'),
            'total_points_awarded' => RewardTransaction::sum('points_earned'),
            'total_bills' => PurchaseBill::count(),
            'total_prescriptions' => Prescription::count(),
            'total_shareholders' => ShareholderAccount::where('verification_status', 'verified')->count(),
            'total_pharmacies' => Pharmacy::count(),
            'total_doctors' => Doctor::count(),
        ];

        return view('admin.analytics.reports', compact('stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $request->get('type', 'sales');
        $fileName = "om_connect_{$type}_report_".now()->format('Y_m_d_His').'.csv';

        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');

            switch ($type) {
                case 'sales':
                    fputcsv($handle, [
                        'Item ID',
                        'Invoice #',
                        'Invoice Date',
                        'Customer / Patient',
                        'Pharmacy / Source',
                        'Product SKU',
                        'Company Product Brand',
                        'Generic Name',
                        'Strength / Form',
                        'Category',
                        'Quantity Sold',
                        'Unit Price (Rs)',
                        'Company Sales Amount (Rs)',
                        'Gross Bill Total (Rs)',
                        'Validation Status',
                        'Created At',
                    ]);
                    PurchaseBillItem::with(['purchaseBill.user', 'purchaseBill.pharmacy', 'product.category'])
                        ->whereNotNull('product_id')
                        ->chunk(100, function ($items) use ($handle) {
                            foreach ($items as $item) {
                                $bill = $item->purchaseBill;
                                $product = $item->product;
                                fputcsv($handle, [
                                    $item->id,
                                    $bill?->invoice_number ?? "BILL-{$bill?->id}",
                                    $bill?->invoice_date?->toDateString() ?? $bill?->created_at?->toDateString(),
                                    $bill?->user?->name ?? 'Guest',
                                    $bill?->pharmacy?->store_name ?? ($bill?->pharmacy_name ?: 'Direct'),
                                    $product?->sku ?? 'N/A',
                                    $product?->brand_name ?? $item->product_name_raw,
                                    $product?->generic_name ?? 'N/A',
                                    ($product?->strength ? $product->strength.' ' : '').($product?->dosage_form ?? ''),
                                    $product?->category?->name ?? 'Uncategorized',
                                    $item->quantity,
                                    $item->unit_price,
                                    $item->amount,
                                    $bill?->total_amount,
                                    $bill?->validation_status,
                                    $item->created_at?->toDateTimeString(),
                                ]);
                            }
                        });
                    break;

                case 'rewards':
                    fputcsv($handle, ['ID', 'User', 'Type', 'Points Earned', 'Bonus', 'Royalty', 'Status', 'Date']);
                    RewardTransaction::with('user')->chunk(100, function ($transactions) use ($handle) {
                        foreach ($transactions as $t) {
                            fputcsv($handle, [
                                $t->id,
                                $t->user?->name,
                                $t->transaction_type,
                                $t->points_earned,
                                $t->bonus_amount,
                                $t->royalty_amount,
                                $t->status,
                                $t->created_at?->toDateTimeString(),
                            ]);
                        }
                    });
                    break;

                case 'shareholders':
                    fputcsv($handle, ['ID', 'User', 'BOID', 'Shares Owned', 'Verification Status', 'Verified At']);
                    ShareholderAccount::with('user')->chunk(100, function ($accounts) use ($handle) {
                        foreach ($accounts as $a) {
                            fputcsv($handle, [
                                $a->id,
                                $a->user?->name,
                                $a->boid,
                                $a->shares_owned,
                                $a->verification_status,
                                $a->verified_at,
                            ]);
                        }
                    });
                    break;

                case 'prescriptions':
                    fputcsv($handle, ['ID', 'Doctor', 'Patient Ref', 'Date', 'Source Type', 'Parsed Status', 'Notes']);
                    Prescription::with('doctor.user')->chunk(100, function ($prescriptions) use ($handle) {
                        foreach ($prescriptions as $p) {
                            fputcsv($handle, [
                                $p->id,
                                $p->doctor?->user?->name ?? "Dr. {$p->doctor?->id}",
                                $p->patient_ref_code,
                                $p->prescription_date,
                                $p->source_type,
                                $p->parsed_status,
                                $p->notes,
                            ]);
                        }
                    });
                    break;
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
