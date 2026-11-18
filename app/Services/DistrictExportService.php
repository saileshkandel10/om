<?php

namespace App\Services;

use App\Models\District;
use App\Models\Province;
use App\Models\PurchaseBill;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class DistrictExportService
{
    /**
     * Export all 77 Nepal districts with statistics.
     */
    public function exportDistricts(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        $writer->addRow(Row::fromValues([
            'District ID',
            'District Name',
            'District Code',
            'Province',
            'Status',
            'Mapped Patients Count',
            'Mapped Bills Count',
            'Mapped Pharmacies Count',
            'Created Date',
        ]));

        $districts = District::with(['province'])
            ->withCount(['userProfiles', 'purchaseBills', 'pharmacies'])
            ->orderBy('province_id')
            ->orderBy('name')
            ->get();

        foreach ($districts as $d) {
            $writer->addRow(Row::fromValues([
                $d->id,
                $d->name,
                $d->code ?? '',
                $d->province?->name ?? 'Province '.$d->province_id,
                ucfirst($d->status ?? 'active'),
                $d->user_profiles_count,
                $d->purchase_bills_count,
                $d->pharmacies_count,
                $d->created_at ? $d->created_at->format('Y-m-d') : '',
            ]));
        }

        $writer->close();
    }

    /**
     * Export unmapped bills queue for location verification.
     */
    public function exportUnmappedBills(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        $writer->addRow(Row::fromValues([
            'Bill ID',
            'Invoice #',
            'Invoice Date',
            'Customer Name',
            'Customer Mobile',
            'Pharmacy / Store Name',
            'Raw Location Text',
            'Bill Total (NPR)',
            'Validation Status',
            'Uploaded At',
        ]));

        $query = PurchaseBill::with(['user', 'pharmacy', 'uploadedFile'])
            ->whereNull('district_id')
            ->where(function ($q) {
                $q->whereNull('location_mapping_status')
                    ->orWhere('location_mapping_status', 'unmapped');
            })
            ->latest('id');

        $query->chunk(500, function ($bills) use ($writer) {
            foreach ($bills as $bill) {
                $rawLocation = $bill->pharmacy?->address ?? ($bill->pharmacy?->store_name ?? '');
                $writer->addRow(Row::fromValues([
                    $bill->id,
                    $bill->invoice_number ?? 'BILL-'.$bill->id,
                    $bill->invoice_date ? $bill->invoice_date->format('Y-m-d') : '',
                    $bill->user?->name ?? 'Guest / Retail',
                    $bill->user?->mobile ?? '',
                    $bill->pharmacy?->store_name ?? 'Direct Upload',
                    $rawLocation,
                    number_format((float) ($bill->total_amount ?? 0), 2),
                    ucfirst($bill->validation_status ?? 'Pending'),
                    $bill->created_at ? $bill->created_at->format('Y-m-d H:i') : '',
                ]));
            }
        });

        $writer->close();
    }

    /**
     * Export province coverage breakdown.
     */
    public function exportCoverageSummary(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        $writer->addRow(Row::fromValues([
            'Province ID',
            'Province Name',
            'Total Districts',
            'Total Mapped Patients',
            'Total Mapped Bills',
            'Total Mapped Pharmacies',
        ]));

        $provinces = Province::with(['districts' => function ($q) {
            $q->withCount(['userProfiles', 'purchaseBills', 'pharmacies']);
        }])->get();

        foreach ($provinces as $prov) {
            $districtsCount = $prov->districts->count();
            $patientsSum = $prov->districts->sum('user_profiles_count');
            $billsSum = $prov->districts->sum('purchase_bills_count');
            $pharmaciesSum = $prov->districts->sum('pharmacies_count');

            $writer->addRow(Row::fromValues([
                $prov->id,
                $prov->name,
                $districtsCount,
                $patientsSum,
                $billsSum,
                $pharmaciesSum,
            ]));
        }

        $writer->close();
    }
}
