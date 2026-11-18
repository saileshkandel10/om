<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use App\Services\DoctorExportService;
use App\Services\DoctorImportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Doctor::with('user')->withCount('prescriptions');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', "%{$request->specialty}%");
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('hospital_clinic', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 50);
        $allowedPerPage = [25, 50, 100, 250, 500, 1000];
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $doctors = $query->latest('id')->paginate($perPage)->withQueryString();
        $specialties = Doctor::whereNotNull('specialty')->where('specialty', '!=', '')->distinct()->pluck('specialty');

        return view('admin.doctors.index', compact('doctors', 'perPage', 'specialties'));
    }

    public function create(): View
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_no' => ['required', 'string', 'max:255', 'unique:doctors,registration_no'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'hospital_clinic' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'status' => ['required', 'string', 'in:active,inactive,suspended,pending'],
        ]);

        $email = $validated['email'] ?? null;
        if (empty($email)) {
            $safeReg = preg_replace('/[^a-zA-Z0-9]/', '', $validated['registration_no']);
            $email = 'dr.'.strtolower($safeReg).'@omconnect.com';
            if (User::where('email', $email)->exists()) {
                $email = 'dr.'.strtolower($safeReg).'.'.rand(100, 999).'@omconnect.com';
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make('Doctor@123'),
            'status' => 'active',
        ]);

        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $user->roles()->syncWithoutDetaching([$doctorRole->id]);

        Doctor::create([
            'user_id' => $user->id,
            'registration_no' => $validated['registration_no'],
            'specialty' => $validated['specialty'] ?? null,
            'hospital_clinic' => $validated['hospital_clinic'] ?? null,
            'status' => $validated['status'],
            'approved_at' => ($validated['status'] === 'active' || $validated['status'] === 'approved') ? Carbon::now() : null,
        ]);

        return to_route('admin.doctors.index')->with('success', __('Doctor created successfully.'));
    }

    public function show(Doctor $doctor): View
    {
        $doctor->load(['user', 'prescriptions' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor): View
    {
        $doctor->load('user');

        return view('admin.doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'registration_no' => ['required', 'string', 'max:255', 'unique:doctors,registration_no,'.$doctor->id],
            'specialty' => ['nullable', 'string', 'max:255'],
            'hospital_clinic' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive,suspended,pending'],
        ]);

        $doctor->update([
            'registration_no' => $validated['registration_no'],
            'specialty' => $validated['specialty'],
            'hospital_clinic' => $validated['hospital_clinic'],
            'status' => $validated['status'],
            'approved_at' => ($validated['status'] === 'active' || $validated['status'] === 'approved') ? ($doctor->approved_at ?? Carbon::now()) : null,
        ]);

        return to_route('admin.doctors.index')->with('success', __('Doctor updated successfully.'));
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $doctor->delete();

        return to_route('admin.doctors.index')->with('success', __('Doctor deleted successfully.'));
    }

    public function import(Request $request, DoctorImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:51200'],
            'on_duplicate' => ['nullable', 'string', 'in:skip,update'],
            'default_status' => ['nullable', 'string', 'in:active,inactive,pending,suspended'],
        ]);

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return back()->with('error', __('The uploaded file is invalid or could not be processed.'));
        }

        $stats = $importService->import($file->getRealPath(), [
            'on_duplicate' => $request->input('on_duplicate', 'skip'),
            'default_status' => $request->input('default_status', 'active'),
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

        return to_route('admin.doctors.index')
            ->with('success', $summaryMessage)
            ->with('import_stats', $stats);
    }

    public function sample(Request $request, DoctorImportService $importService): BinaryFileResponse
    {
        $format = $request->query('format') === 'csv' ? 'csv' : 'xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'sample_doctors_').'.'.$format;

        $importService->generateSampleFile($tempPath, $format);

        return response()->download($tempPath, 'doctors_template.'.$format, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function export(Request $request, DoctorExportService $exportService): BinaryFileResponse
    {
        $request->validate([
            'scope' => ['nullable', 'string', 'in:current,all'],
            'format' => ['nullable', 'string', 'in:xlsx,csv'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'specialty' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $scope = $request->input('scope', 'all');
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $date = Carbon::now()->format('Y-m-d');
        $scopeTag = $scope === 'current' ? 'page_'.($request->input('page', 1)) : 'all';
        $filename = "doctors_{$scopeTag}_{$date}.{$format}";

        $tempPath = tempnam(sys_get_temp_dir(), 'export_doctors_').'.'.$format;

        $exportService->export($tempPath, [
            'scope' => $scope,
            'format' => $format,
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'specialty' => $request->input('specialty'),
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
}
