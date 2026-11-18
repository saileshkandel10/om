<?php

namespace App\Services;

use App\Models\IllnessCategory;
use App\Models\PatientIllness;
use App\Models\User;
use Carbon\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class PatientIllnessExportService
{
    public function __construct(
        protected PatientIllnessService $illnessService
    ) {}

    /**
     * Generate dynamic export filename based on filters.
     */
    public function generateFilename(array $filters, string $extension = 'xlsx'): string
    {
        $prefix = 'illness_category_report';

        if (! empty($filters['illness_category_id']) && is_numeric($filters['illness_category_id'])) {
            $cat = IllnessCategory::find($filters['illness_category_id']);
            if ($cat) {
                $prefix = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $cat->name)).'_patient_report';
            }
        }

        $dateStr = Carbon::now()->format('Y-m-d');

        return "{$prefix}_{$dateStr}.{$extension}";
    }

    /**
     * Export patient illness report to Excel or CSV.
     */
    public function export(string $filePath, array $filters = []): void
    {
        $format = ($filters['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $type = $filters['export_type'] ?? 'detailed'; // 'current', 'detailed', 'summary', 'patients', 'records', 'combinations'

        if ($format === 'csv') {
            $writer = new CsvWriter;
        } else {
            $writer = new XlsxWriter;
        }

        $writer->openToFile($filePath);

        switch ($type) {
            case 'summary':
                $this->exportCategorySummary($writer, $filters);
                break;
            case 'combinations':
                $this->exportCombinations($writer, $filters);
                break;
            case 'records':
            case 'illness_records':
                $this->exportIllnessRecords($writer, $filters);
                break;
            case 'patients':
            case 'patient_list':
                $this->exportPatientList($writer, $filters);
                break;
            case 'current':
                $this->exportPatientDetails($writer, $filters, 'current');
                break;
            case 'detailed':
            default:
                $this->exportPatientDetails($writer, $filters, 'all');
                break;
        }

        $writer->close();
    }

    /**
     * Write detailed patient rows with all clinical and billing attributes.
     */
    protected function exportPatientDetails($writer, array $filters, string $scope): void
    {
        $selectedCatId = ! empty($filters['illness_category_id']) && is_numeric($filters['illness_category_id'])
            ? (int) $filters['illness_category_id']
            : null;

        // Header
        $writer->addRow(Row::fromValues([
            'Patient ID',
            'Patient Name',
            'Age',
            'DOB',
            'Gender',
            'Phone',
            'Email',
            'Location / Address',
            'All Illness Categories',
            'Other Illnesses',
            'Number of Illness Categories',
            'Doctor',
            'Department',
            'Pharmacy / Branch',
            'Number of Visits / Bills',
            'First Visit Date',
            'Last Visit Date',
            'Medicines Prescribed / Purchased',
            'Total Amount (NPR)',
            'Payment / Bill Status',
            'Account Status',
        ]));

        $query = $this->illnessService->buildFilteredQuery($filters)
            ->with([
                'profile',
                'illnessCategories',
                'patientIllnesses',
                'purchaseBills.pharmacy',
                'purchaseBills.items.product',
                'doctor',
            ])
            ->latest('id');

        if ($scope === 'current') {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $perPage = max(1, (int) ($filters['per_page'] ?? 50));
            $patients = $query->forPage($page, $perPage)->get();
            $this->writeDetailedPatientBatch($writer, $patients, $selectedCatId);
        } else {
            $query->chunk(250, function ($patients) use ($writer, $selectedCatId) {
                $this->writeDetailedPatientBatch($writer, $patients, $selectedCatId);
            });
        }
    }

    /**
     * Format and write detailed batch of patient records.
     */
    protected function writeDetailedPatientBatch($writer, $patients, ?int $selectedCatId): void
    {
        foreach ($patients as $user) {
            /** @var User $user */
            $profile = $user->profile;
            $allCategories = $user->illnessCategories->unique('id');
            $bills = $user->purchaseBills;

            $totalAmount = (float) $bills->sum('total_amount');
            $billsCount = $bills->count();

            $firstVisit = $bills->min('invoice_date') ?? $bills->min('created_at');
            $lastVisit = $bills->max('invoice_date') ?? $bills->max('created_at');

            // Collect medicines
            $medicines = collect();
            $pharmaciesList = collect();

            foreach ($bills as $bill) {
                if ($bill->pharmacy) {
                    $pharmaciesList->push($bill->pharmacy->store_name);
                }
                foreach ($bill->items as $item) {
                    if ($item->product) {
                        $medicines->push($item->product->brand_name);
                    } elseif (! empty($item->product_name_raw)) {
                        $medicines->push($item->product_name_raw);
                    }
                }
            }

            $otherIllnesses = collect();
            if ($selectedCatId) {
                $otherIllnesses = $allCategories->filter(fn ($c) => $c->id !== $selectedCatId)->pluck('name');
            } else {
                $otherIllnesses = $allCategories->pluck('name');
            }

            $doctorName = '—';
            $department = 'General Medicine';
            $latestIllness = $user->patientIllnesses->first();
            if (! empty($latestIllness?->diagnosed_by)) {
                $doctorName = $latestIllness->diagnosed_by;
            } elseif ($user->doctor) {
                $doctorName = 'Dr. '.$user->name;
                $department = $user->doctor->specialty ?? 'General Medicine';
            }

            $age = '—';
            if ($profile?->dob) {
                $age = Carbon::parse($profile->dob)->age.' yrs';
            }

            $locationParts = array_filter([
                $profile?->address,
                $profile?->district,
                $profile?->province,
            ]);

            $paymentStatus = 'Paid';
            if ($bills->where('validation_status', 'pending')->count() > 0) {
                $paymentStatus = 'Pending Verification';
            } elseif ($bills->where('validation_status', 'rejected')->count() > 0) {
                $paymentStatus = 'Rejected / Flagged';
            }

            $writer->addRow(Row::fromValues([
                'P-'.(1000 + $user->id),
                (string) $user->name,
                (string) $age,
                $profile?->dob ? Carbon::parse($profile->dob)->format('Y-m-d') : '—',
                ucfirst($profile?->gender ?? '—'),
                (string) ($user->mobile ?? '—'),
                (string) ($user->email ?? '—'),
                ! empty($locationParts) ? implode(', ', $locationParts) : '—',
                $allCategories->pluck('name')->implode(', ') ?: '—',
                $otherIllnesses->implode(', ') ?: 'None',
                (int) $allCategories->count(),
                (string) $doctorName,
                (string) $department,
                $pharmaciesList->unique()->take(2)->implode(', ') ?: '—',
                (int) $billsCount,
                $firstVisit ? Carbon::parse($firstVisit)->format('Y-m-d') : '—',
                $lastVisit ? Carbon::parse($lastVisit)->format('Y-m-d') : '—',
                $medicines->unique()->take(5)->implode(', ') ?: '—',
                $totalAmount,
                (string) $paymentStatus,
                (string) ($user->status ?? 'active'),
            ]));
        }
    }

    /**
     * Export unique patient list.
     */
    protected function exportPatientList($writer, array $filters): void
    {
        $writer->addRow(Row::fromValues([
            'Patient ID',
            'Patient Name',
            'Gender',
            'Age',
            'Phone',
            'Address',
            'Assigned Illness Categories',
            'Illness Count',
            'Registered Date',
        ]));

        $this->illnessService->buildFilteredQuery($filters)
            ->with(['profile', 'illnessCategories'])
            ->latest('id')
            ->chunk(250, function ($patients) use ($writer) {
                foreach ($patients as $user) {
                    $profile = $user->profile;
                    $cats = $user->illnessCategories->unique('id');
                    $age = $profile?->dob ? Carbon::parse($profile->dob)->age.' yrs' : '—';
                    $location = implode(', ', array_filter([$profile?->address, $profile?->district, $profile?->province])) ?: '—';

                    $writer->addRow(Row::fromValues([
                        'P-'.(1000 + $user->id),
                        (string) $user->name,
                        ucfirst($profile?->gender ?? '—'),
                        (string) $age,
                        (string) ($user->mobile ?? '—'),
                        (string) $location,
                        $cats->pluck('name')->implode(', ') ?: '—',
                        (int) $cats->count(),
                        $user->created_at ? $user->created_at->format('Y-m-d') : '—',
                    ]));
                }
            });
    }

    /**
     * Export illness records (patient-illness relationships: 1 row per illness).
     */
    protected function exportIllnessRecords($writer, array $filters): void
    {
        $writer->addRow(Row::fromValues([
            'Record ID',
            'Patient ID',
            'Patient Name',
            'Illness Category',
            'Diagnosis Date',
            'Diagnosed By / Source',
            'Product / Medicine Name',
            'Status',
            'Notes',
        ]));

        $patientIds = $this->illnessService->buildFilteredQuery($filters)->distinct()->pluck('users.id')->toArray();

        if (empty($patientIds)) {
            return;
        }

        PatientIllness::whereIn('user_id', $patientIds)
            ->where('status', '!=', 'inactive')
            ->with(['user.profile', 'illnessCategory', 'product'])
            ->latest('detected_at')
            ->chunk(300, function ($records) use ($writer) {
                foreach ($records as $record) {
                    $writer->addRow(Row::fromValues([
                        (int) $record->id,
                        'P-'.(1000 + ($record->user_id ?? 0)),
                        (string) ($record->user?->name ?? '—'),
                        (string) ($record->illnessCategory?->name ?? '—'),
                        $record->detected_at ? $record->detected_at->format('Y-m-d') : '—',
                        (string) ($record->diagnosed_by ?? ucfirst($record->source_type ?? '—')),
                        (string) ($record->product?->brand_name ?? '—'),
                        ucfirst($record->status ?? 'active'),
                        (string) ($record->notes ?? '—'),
                    ]));
                }
            });
    }

    /**
     * Write category summary report.
     */
    protected function exportCategorySummary($writer, array $filters): void
    {
        $reportData = $this->illnessService->getReportData($filters);

        $writer->addRow(Row::fromValues([
            'Illness Category',
            'Status',
            'Unique Patients Count',
            '% of Total Patients',
            'Male Patients',
            'Female Patients',
            'Multiple Illness Patients',
        ]));

        foreach ($reportData['category_summary'] as $row) {
            $writer->addRow(Row::fromValues([
                $row['name'],
                ucfirst($row['status']),
                (int) $row['unique_patients'],
                $row['percentage'].'%',
                (int) $row['male'],
                (int) $row['female'],
                (int) $row['multiple_illness'],
            ]));
        }
    }

    /**
     * Write multi-illness combinations report.
     */
    protected function exportCombinations($writer, array $filters): void
    {
        $reportData = $this->illnessService->getReportData($filters);

        $writer->addRow(Row::fromValues([
            'Rank',
            'Illness Combination',
            'Unique Patients Count',
            '% of Multi-Illness Patients',
        ]));

        foreach ($reportData['top_combinations'] as $combo) {
            $writer->addRow(Row::fromValues([
                (int) $combo['rank'],
                (string) $combo['combination'],
                (int) $combo['patients_count'],
                $combo['percentage'].'%',
            ]));
        }
    }
}
