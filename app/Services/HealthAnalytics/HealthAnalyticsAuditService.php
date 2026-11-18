<?php

namespace App\Services\HealthAnalytics;

use App\Models\HealthAnalyticsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthAnalyticsAuditService
{
    /**
     * Log an admin action in Health Analytics.
     */
    public function log(string $action, ?int $patientId = null, ?array $details = null, ?Request $request = null): HealthAnalyticsLog
    {
        $adminId = Auth::id() ?? 1;

        return HealthAnalyticsLog::create([
            'admin_id' => $adminId,
            'patient_id' => $patientId,
            'action' => $action,
            'module' => 'health_analytics',
            'details' => $details,
            'ip_address' => $request?->ip() ?? request()->ip(),
            'user_agent' => substr($request?->userAgent() ?? request()->userAgent() ?? '', 0, 500),
        ]);
    }
}
