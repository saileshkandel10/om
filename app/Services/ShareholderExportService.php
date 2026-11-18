<?php

namespace App\Services;

use App\Models\ShareholderMaster;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class ShareholderExportService
{
    /**
     * Export shareholders to an Excel (.xlsx) or CSV (.csv) file.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(string $filePath, array $filters = []): void
    {
        $format = ($filters['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $scope = $filters['scope'] ?? 'all'; // 'all' or 'current'

        if ($format === 'csv') {
            $writer = new CsvWriter;
        } else {
            $writer = new XlsxWriter;
        }

        $writer->openToFile($filePath);

        // Header Row
        $writer->addRow(Row::fromValues([
            'BOID',
            'Shareholder Name',
            'Mobile',
            'Email',
            'Linked Accounts',
            'Status',
            'Source Note',
            'Added Date',
        ]));

        $query = ShareholderMaster::withCount('accounts')->latest();

        // Apply search filter if provided
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('shareholder_name', 'like', "%{$search}%")
                    ->orWhere('boid', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply status filter if provided
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($scope === 'current') {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $perPage = max(1, (int) ($filters['per_page'] ?? 1000));
            $records = $query->forPage($page, $perPage)->get();

            foreach ($records as $shareholder) {
                $writer->addRow(Row::fromValues([
                    (string) $shareholder->boid,
                    (string) $shareholder->shareholder_name,
                    (string) ($shareholder->mobile ?? ''),
                    (string) ($shareholder->email ?? ''),
                    (int) ($shareholder->accounts_count ?? 0),
                    (string) ($shareholder->status ?? 'active'),
                    (string) ($shareholder->source_note ?? ''),
                    $shareholder->created_at ? $shareholder->created_at->format('Y-m-d H:i') : '',
                ]));
            }
        } else {
            // Stream in chunks of 1000 to keep memory footprint low
            $query->chunk(1000, function ($shareholders) use ($writer) {
                foreach ($shareholders as $shareholder) {
                    $writer->addRow(Row::fromValues([
                        (string) $shareholder->boid,
                        (string) $shareholder->shareholder_name,
                        (string) ($shareholder->mobile ?? ''),
                        (string) ($shareholder->email ?? ''),
                        (int) ($shareholder->accounts_count ?? 0),
                        (string) ($shareholder->status ?? 'active'),
                        (string) ($shareholder->source_note ?? ''),
                        $shareholder->created_at ? $shareholder->created_at->format('Y-m-d H:i') : '',
                    ]));
                }
            });
        }

        $writer->close();
    }
}
