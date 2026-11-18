<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShareholderMaster;
use App\Services\ShareholderExportService;
use App\Services\ShareholderImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShareholderController extends Controller
{
    public function index(Request $request): View
    {
        $query = ShareholderMaster::withCount('accounts');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('shareholder_name', 'like', "%{$search}%")
                    ->orWhere('boid', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 1000);
        $allowedPerPage = [25, 50, 100, 250, 500, 1000, 2000];
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 1000;
        }

        $shareholders = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.shareholders.index', compact('shareholders', 'perPage'));
    }

    public function show(ShareholderMaster $shareholder): View
    {
        $shareholder->load(['accounts.user', 'accounts' => fn ($q) => $q->with('user')]);

        return view('admin.shareholders.show', compact('shareholder'));
    }

    public function edit(ShareholderMaster $shareholder): View
    {
        return view('admin.shareholders.edit', compact('shareholder'));
    }

    public function update(Request $request, ShareholderMaster $shareholder): RedirectResponse
    {
        $validated = $request->validate([
            'shareholder_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:active,inactive,pending'],
            'source_note' => ['nullable', 'string', 'max:500'],
        ]);

        $shareholder->update($validated);

        return to_route('admin.shareholders.index')->with('success', __('Shareholder updated successfully.'));
    }

    /**
     * Import shareholders from an uploaded Excel or CSV file.
     */
    public function import(Request $request, ShareholderImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:51200'],
            'on_duplicate' => ['nullable', 'string', 'in:skip,update'],
            'default_status' => ['nullable', 'string', 'in:active,inactive,pending'],
            'source_note' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return back()->with('error', __('The uploaded file is invalid or could not be processed.'));
        }

        $stats = $importService->import($file->getRealPath(), [
            'on_duplicate' => $request->input('on_duplicate', 'skip'),
            'default_status' => $request->input('default_status', 'active'),
            'source_note' => $request->input('source_note'),
        ]);

        $summaryParts = [];
        $summaryParts[] = "{$stats['imported']} imported";

        if ($stats['updated'] > 0) {
            $summaryParts[] = "{$stats['updated']} updated";
        }
        if ($stats['duplicates_skipped'] > 0) {
            $summaryParts[] = "{$stats['duplicates_skipped']} duplicates skipped";
        }
        if ($stats['invalid_skipped'] > 0) {
            $summaryParts[] = "{$stats['invalid_skipped']} invalid skipped";
        }

        $summaryMessage = __('Import summary: :summary (Total rows processed: :total).', [
            'summary' => implode(', ', $summaryParts),
            'total' => $stats['total_rows'],
        ]);

        if (! empty($stats['errors']) && $stats['imported'] === 0 && $stats['updated'] === 0) {
            return back()->with('error', $stats['errors'][0])->with('import_stats', $stats);
        }

        return to_route('admin.shareholders.index')
            ->with('success', $summaryMessage)
            ->with('import_stats', $stats);
    }

    /**
     * Download a sample template file for importing shareholders.
     */
    public function sample(Request $request, ShareholderImportService $importService): BinaryFileResponse
    {
        $format = $request->query('format') === 'csv' ? 'csv' : 'xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'sample_shareholders_').'.'.$format;

        $importService->generateSampleFile($tempPath, $format);

        return response()->download($tempPath, 'shareholders_template.'.$format, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export shareholders to an Excel (.xlsx) or CSV (.csv) file.
     */
    public function export(Request $request, ShareholderExportService $exportService): BinaryFileResponse
    {
        $request->validate([
            'scope' => ['nullable', 'string', 'in:current,all'],
            'format' => ['nullable', 'string', 'in:xlsx,csv'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $scope = $request->input('scope', 'all');
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $date = now()->format('Y-m-d');
        $scopeTag = $scope === 'current' ? 'page_'.($request->input('page', 1)) : 'all';
        $filename = "shareholders_{$scopeTag}_{$date}.{$format}";

        $tempPath = tempnam(sys_get_temp_dir(), 'export_shareholders_').'.'.$format;

        $exportService->export($tempPath, [
            'scope' => $scope,
            'format' => $format,
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'page' => (int) $request->input('page', 1),
            'per_page' => (int) $request->input('per_page', 1000),
        ]);

        $contentType = $format === 'csv'
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->download($tempPath, $filename, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend(true);
    }

    public function exclusiveRewards(): View
    {
        $shareholders = ShareholderMaster::where('status', 'active')
            ->withCount('accounts')
            ->latest()
            ->get();

        return view('admin.shareholder-zone.exclusive-rewards', compact('shareholders'));
    }
}
