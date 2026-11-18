<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OcrReviewQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrReviewQueueController extends Controller
{
    public function index(Request $request): View
    {
        $query = OcrReviewQueue::with(['ocrJob', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('review_status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $reviews = $query->latest()->get();

        return view('admin.ocr-review-queue.index', compact('reviews'));
    }

    public function show(OcrReviewQueue $ocrReviewQueue): View
    {
        $ocrReviewQueue->load(['ocrJob.sourceFile', 'assignedTo']);

        return view('admin.ocr-review-queue.show', compact('ocrReviewQueue'));
    }

    public function update(Request $request, OcrReviewQueue $ocrReviewQueue): RedirectResponse
    {
        $validated = $request->validate([
            'review_status' => ['required', 'string', 'in:pending,approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['reviewed_at'] = now();
        $ocrReviewQueue->update($validated);

        return to_route('admin.ocr-review-queue.index')->with('success', __('Review updated successfully.'));
    }
}
