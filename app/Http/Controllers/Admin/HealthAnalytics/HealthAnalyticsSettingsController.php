<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HealthAnalyticsSettingsController extends Controller
{
    public function index(): View
    {
        $categories = HealthCategory::orderBy('name')->get();
        $autoAnalyzeBills = Cache::get('health_analytics_auto_analyze_bills', true);
        $minimumRecordsForPattern = Cache::get('health_analytics_min_records_pattern', 2);

        return view('admin.health-analytics.settings.index', compact(
            'categories',
            'autoAnalyzeBills',
            'minimumRecordsForPattern'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_analyze_bills' => ['nullable', 'boolean'],
            'min_records_pattern' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        Cache::put('health_analytics_auto_analyze_bills', (bool) ($validated['auto_analyze_bills'] ?? false));
        Cache::put('health_analytics_min_records_pattern', (int) $validated['min_records_pattern']);

        return back()->with('success', __('Health Analytics settings updated successfully.'));
    }
}
