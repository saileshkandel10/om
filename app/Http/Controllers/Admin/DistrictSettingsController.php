<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\PurchaseBill;
use App\Services\DistrictExportService;
use App\Services\LocationNormalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DistrictSettingsController extends Controller
{
    public function __construct(
        protected LocationNormalizationService $normalizationService,
        protected DistrictExportService $exportService
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'districts');

        // Districts Query
        $districtQuery = District::with('province')
            ->withCount(['purchaseBills', 'userProfiles']);

        if ($request->filled('province_id')) {
            $districtQuery->where('province_id', $request->province_id);
        }

        if ($request->filled('status')) {
            $districtQuery->where('status', $request->status);
        }

        if ($request->filled('search') && $tab === 'districts') {
            $search = trim((string) $request->search);
            $districtQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('province', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $districts = $districtQuery->orderBy('province_id')->orderBy('name')->paginate(100, ['*'], 'districts_page')->withQueryString();
        $provinces = Province::where('status', 'active')->orderBy('id')->get();
        $allDistrictsList = District::where('status', 'active')->orderBy('name')->get();

        // Unmapped Bills Query & Stats
        $unmappedQuery = $this->normalizationService->getUnmappedBillsQuery($request->all());
        $unmappedBillsPaginator = $unmappedQuery->paginate(25, ['*'], 'unmapped_page')->withQueryString();
        $unmappedStats = $this->normalizationService->getUnmappedStats();

        $unmappedRows = [];
        foreach ($unmappedBillsPaginator->items() as $bill) {
            $unmappedRows[] = $this->normalizationService->formatUnmappedBillRow($bill);
        }

        return view('admin.settings.locations.districts', compact(
            'districts',
            'provinces',
            'allDistrictsList',
            'unmappedBillsPaginator',
            'unmappedRows',
            'unmappedStats',
            'tab'
        ));
    }

    public function update(Request $request, District $district): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $district->update($validated);

        return back()->with('success', __('District :name updated successfully.', ['name' => $district->name]));
    }

    public function normalize(): RedirectResponse
    {
        $results = $this->normalizationService->normalizeAllRecords();

        return back()->with('success', __('Successfully scanned and normalized :count records into official Nepal districts.', [
            'count' => $results['total_updated'],
        ]));
    }

    /**
     * Map a single bill to a district.
     */
    public function mapBill(Request $request, PurchaseBill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $district = District::findOrFail($validated['district_id']);
        $this->normalizationService->mapSingleBill($bill, $district, $validated['notes'] ?? null);

        return back()->with('success', __('Bill #:invoice successfully mapped to :district.', [
            'invoice' => $bill->invoice_number ?? $bill->id,
            'district' => $district->name,
        ]));
    }

    /**
     * Skip/Ignore a single bill from unmapped review.
     */
    public function skipBill(Request $request, PurchaseBill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->normalizationService->skipSingleBill($bill, $validated['reason'] ?? null);

        return back()->with('success', __('Bill #:invoice marked as skipped.', [
            'invoice' => $bill->invoice_number ?? $bill->id,
        ]));
    }

    /**
     * Bulk actions on unmapped bills (bulk map, bulk skip, bulk auto-match).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('bulk_action');
        $billIds = $request->input('bill_ids', []);

        if (empty($billIds) && $action !== 'auto_match_all') {
            return back()->with('error', __('Please select at least one bill.'));
        }

        switch ($action) {
            case 'assign_district':
                $districtId = (int) $request->input('bulk_district_id');
                if (! $districtId) {
                    return back()->with('error', __('Please select a district for bulk assignment.'));
                }
                $count = $this->normalizationService->bulkMapBills($billIds, $districtId);

                return back()->with('success', __('Successfully assigned :count bills to selected district.', ['count' => $count]));

            case 'skip_selected':
                $count = $this->normalizationService->bulkSkipBills($billIds);

                return back()->with('success', __('Marked :count bills as skipped.', ['count' => $count]));

            case 'auto_match_selected':
            case 'auto_match_all':
                $count = $this->normalizationService->bulkAutoMapSuggested($billIds);

                return back()->with('success', __('Successfully auto-mapped :count bills based on matching addresses.', ['count' => $count]));

            default:
                return back()->with('error', __('Invalid bulk action.'));
        }
    }

    /**
     * Load popup modal content for individual bill mapping.
     */
    public function billModal(PurchaseBill $bill): JsonResponse|View
    {
        $bill->load(['user.profile', 'pharmacy', 'items.product', 'uploadedFile']);
        $formatted = $this->normalizationService->formatUnmappedBillRow($bill);
        $districts = District::with('province')->where('status', 'active')->orderBy('name')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'bill' => $formatted,
            ]);
        }

        return view('admin.settings.locations.partials.bill-map-modal', compact('bill', 'formatted', 'districts'));
    }

    /**
     * Export districts, unmapped bills, or provincial coverage summary.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $type = $request->input('type', 'districts'); // districts, unmapped_bills, summary
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $timestamp = date('Y_m_d_His');

        switch ($type) {
            case 'unmapped_bills':
                $filename = 'unmapped_location_bills_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportUnmappedBills($tempPath, $format);
                break;

            case 'summary':
                $filename = 'province_district_coverage_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportCoverageSummary($tempPath, $format);
                break;

            case 'districts':
            default:
                $filename = 'nepal_77_districts_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportDistricts($tempPath, $format);
                break;
        }

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
