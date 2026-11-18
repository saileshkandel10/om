<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');

        if ($request->filled('module')) {
            $query->where('module_name', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action_name', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->get();

        return view('admin.audit-logs.index', compact('logs'));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
