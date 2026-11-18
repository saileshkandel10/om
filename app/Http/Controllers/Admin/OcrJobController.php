<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OcrJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrJobController extends Controller
{
    public function index(Request $request): View
    {
        $query = OcrJob::with('sourceFile');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('module')) {
            $query->where('module_name', $request->module);
        }

        $jobs = $query->latest()->get();

        return view('admin.ocr-jobs.index', compact('jobs'));
    }

    public function show(OcrJob $ocrJob): View
    {
        $ocrJob->load(['sourceFile', 'reviewQueue.assignedTo']);

        return view('admin.ocr-jobs.show', compact('ocrJob'));
    }
}
