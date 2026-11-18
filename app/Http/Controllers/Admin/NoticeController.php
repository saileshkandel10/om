<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Notice::query();

        if ($request->filled('type')) {
            $query->where('notice_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('audience')) {
            $query->where('audience_type', $request->audience);
        }

        $notices = $query->latest()->get();

        return view('admin.notices.index', compact('notices'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $filename = 'notices_export_'.date('Y_m_d_His').'.'.$format;
        $tempPath = storage_path('app/temp_'.$filename);

        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($tempPath);

        $writer->addRow(Row::fromValues([
            'Notice Title',
            'Notice Type',
            'Target Audience',
            'Status',
            'Published Date',
            'Created Date',
        ]));

        Notice::latest('id')->chunk(500, function ($notices) use ($writer) {
            foreach ($notices as $n) {
                $writer->addRow(Row::fromValues([
                    $n->title,
                    strtoupper($n->notice_type),
                    ucfirst($n->audience_type),
                    ucfirst($n->status),
                    $n->published_at ? $n->published_at->format('Y-m-d H:i') : 'Unpublished',
                    $n->created_at ? $n->created_at->format('Y-m-d H:i') : '',
                ]));
            }
        });

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function create(): View
    {
        return view('admin.notices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notice_type' => ['required', 'string', 'in:agm,financial,announcement,general'],
            'audience_type' => ['required', 'string', 'in:shareholder,pharmacy,doctor,customer,all'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
        ]);

        $notice = Notice::create($validated);

        if (($validated['status'] ?? 'draft') === 'published') {
            $this->dispatchNoticeNotifications($notice);
        }

        return to_route('admin.notices.index')->with('success', __('Notice created successfully.'));
    }

    public function show(Notice $notice): View
    {
        return view('admin.notices.show', compact('notice'));
    }

    public function edit(Notice $notice): View
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notice_type' => ['required', 'string', 'in:agm,financial,announcement,general'],
            'audience_type' => ['required', 'string', 'in:shareholder,pharmacy,doctor,customer,all'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
        ]);

        $notice->update($validated);

        if (($validated['status'] ?? 'draft') === 'published') {
            $this->dispatchNoticeNotifications($notice);
        }

        return to_route('admin.notices.index')->with('success', __('Notice updated successfully.'));
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return to_route('admin.notices.index')->with('success', __('Notice deleted successfully.'));
    }

    protected function dispatchNoticeNotifications(Notice $notice): void
    {
        $usersQuery = User::query();
        if ($notice->audience_type !== 'all') {
            $usersQuery->whereHas('roles', fn ($q) => $q->whereRaw('LOWER(name) = ?', [strtolower($notice->audience_type)]));
        }
        $users = $usersQuery->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $notice->title,
                'message' => substr(strip_tags($notice->body), 0, 255),
                'type' => 'notice',
                'is_read' => false,
            ]);
        }
    }

    public function agmNotices(): View
    {
        $notices = Notice::where('notice_type', 'agm')->latest()->get();

        return view('admin.shareholder-zone.agm-notices', compact('notices'));
    }

    public function financialReports(): View
    {
        $notices = Notice::where('notice_type', 'financial')->latest()->get();

        return view('admin.shareholder-zone.financial-reports', compact('notices'));
    }
}
