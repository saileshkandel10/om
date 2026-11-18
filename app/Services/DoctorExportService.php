<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class DoctorExportService
{
    public function export(string $filePath, array $filters = []): void
    {
        $format = ($filters['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $scope = $filters['scope'] ?? 'all';

        if ($format === 'csv') {
            $writer = new CsvWriter;
        } else {
            $writer = new XlsxWriter;
        }

        $writer->openToFile($filePath);

        $writer->addRow(Row::fromValues([
            'Doctor ID',
            'Doctor Name',
            'Registration No',
            'Specialty',
            'Hospital / Clinic',
            'Mobile',
            'Email',
            'Prescriptions Count',
            'Status',
            'Approved Date',
            'Registered Date',
        ]));

        $query = Doctor::with(['user', 'prescriptions'])->withCount('prescriptions')->latest('id');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('hospital_clinic', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['specialty'])) {
            $query->where('specialty', 'like', "%{$filters['specialty']}%");
        }

        if ($scope === 'current') {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $perPage = max(1, (int) ($filters['per_page'] ?? 1000));
            $records = $query->forPage($page, $perPage)->get();

            foreach ($records as $doctor) {
                $writer->addRow(Row::fromValues([
                    'DOC-'.(1000 + $doctor->id),
                    (string) ($doctor->user?->name ?? '—'),
                    (string) $doctor->registration_no,
                    (string) ($doctor->specialty ?? '—'),
                    (string) ($doctor->hospital_clinic ?? '—'),
                    (string) ($doctor->user?->mobile ?? '—'),
                    (string) ($doctor->user?->email ?? '—'),
                    (int) ($doctor->prescriptions_count ?? 0),
                    (string) ucfirst($doctor->status ?? 'pending'),
                    $doctor->approved_at ? $doctor->approved_at->format('Y-m-d H:i') : '—',
                    $doctor->created_at ? $doctor->created_at->format('Y-m-d H:i') : '—',
                ]));
            }
        } else {
            $query->chunk(500, function ($doctors) use ($writer) {
                foreach ($doctors as $doctor) {
                    $writer->addRow(Row::fromValues([
                        'DOC-'.(1000 + $doctor->id),
                        (string) ($doctor->user?->name ?? '—'),
                        (string) $doctor->registration_no,
                        (string) ($doctor->specialty ?? '—'),
                        (string) ($doctor->hospital_clinic ?? '—'),
                        (string) ($doctor->user?->mobile ?? '—'),
                        (string) ($doctor->user?->email ?? '—'),
                        (int) ($doctor->prescriptions_count ?? 0),
                        (string) ucfirst($doctor->status ?? 'pending'),
                        $doctor->approved_at ? $doctor->approved_at->format('Y-m-d H:i') : '—',
                        $doctor->created_at ? $doctor->created_at->format('Y-m-d H:i') : '—',
                    ]));
                }
            });
        }

        $writer->close();
    }
}
