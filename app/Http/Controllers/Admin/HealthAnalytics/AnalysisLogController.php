<?php

namespace App\Http\Controllers\Admin\HealthAnalytics;

use App\Http\Controllers\Controller;
use App\Models\HealthAnalyticsLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalysisLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = HealthAnalyticsLog::with(['admin', 'patient']);

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        $logs = $query->latest()->paginate(20);

        return view('admin.health-analytics.logs.index', compact('logs'));
    }
}
