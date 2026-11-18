<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function index(): View
    {
        $threshold = Cache::get('ocr_verification_threshold', 5000.00);
        $pointRate = Cache::get('default_point_rate', 10.00);
        $royaltyRate = Cache::get('shareholder_royalty_rate', 2.00);

        return view('admin.settings.system', compact('threshold', 'pointRate', 'royaltyRate'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ocr_verification_threshold' => ['required', 'numeric', 'min:0'],
            'default_point_rate' => ['required', 'numeric', 'min:0'],
            'shareholder_royalty_rate' => ['required', 'numeric', 'min:0'],
        ]);

        Cache::put('ocr_verification_threshold', (float) $validated['ocr_verification_threshold']);
        Cache::put('default_point_rate', (float) $validated['default_point_rate']);
        Cache::put('shareholder_royalty_rate', (float) $validated['shareholder_royalty_rate']);

        return back()->with('success', __('System verification rules & thresholds updated successfully.'));
    }
}
