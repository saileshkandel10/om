<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $query = Campaign::withCount('recipients');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('campaign_type', $request->type);
        }

        $campaigns = $query->latest()->get();

        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $filename = 'campaigns_export_'.date('Y_m_d_His').'.'.$format;
        $tempPath = storage_path('app/temp_'.$filename);

        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($tempPath);

        $writer->addRow(Row::fromValues([
            'Campaign Name',
            'Type',
            'Target Segment',
            'Description',
            'Status',
            'Recipients Count',
            'Start Date',
            'End Date',
            'Created Date',
        ]));

        Campaign::withCount('recipients')->latest('id')->chunk(500, function ($campaigns) use ($writer) {
            foreach ($campaigns as $c) {
                $writer->addRow(Row::fromValues([
                    $c->name,
                    ucfirst($c->campaign_type),
                    $c->target_segment ?? 'All',
                    $c->description ?? '',
                    ucfirst($c->status),
                    $c->recipients_count,
                    $c->start_date ? $c->start_date->format('Y-m-d') : '',
                    $c->end_date ? $c->end_date->format('Y-m-d') : '',
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
                ]));
            }
        });

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function create(): View
    {
        return view('admin.campaigns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'campaign_type' => ['required', 'string', 'in:promotion,awareness,product'],
            'target_segment' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'status' => ['nullable', 'string', 'in:draft,active,completed,cancelled'],
        ]);

        Campaign::create($validated);

        return to_route('admin.campaigns.index')->with('success', __('Campaign created successfully.'));
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['recipients.user', 'recipients.illnessCategory']);

        return view('admin.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign): View
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'campaign_type' => ['required', 'string', 'in:promotion,awareness,product'],
            'target_segment' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'status' => ['nullable', 'string', 'in:draft,active,completed,cancelled'],
        ]);

        $campaign->update($validated);

        return to_route('admin.campaigns.index')->with('success', __('Campaign updated successfully.'));
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return to_route('admin.campaigns.index')->with('success', __('Campaign deleted successfully.'));
    }
}
