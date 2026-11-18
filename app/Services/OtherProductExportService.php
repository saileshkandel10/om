<?php

namespace App\Services;

use App\Models\OtherProduct;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class OtherProductExportService
{
    /**
     * Export Other Products based on filters.
     */
    public function export(string $filePath, array $filters = []): void
    {
        $format = ($filters['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;

        $writer->openToFile($filePath);

        // Header
        $writer->addRow(Row::fromValues([
            'Brand Name',
            'Generic Name',
            'Category',
            'Illness Category',
            'Description',
            'Status',
            'Source',
            'Confidence Score (%)',
            'Created Date',
        ]));

        $query = OtherProduct::with(['category', 'illnessCategory'])->latest('id');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['illness_category_id'])) {
            $query->where('illness_category_id', $filters['illness_category_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'unmapped') {
                $query->unmapped();
            } else {
                $query->where('status', $filters['status']);
            }
        }

        $query->chunk(500, function ($products) use ($writer) {
            foreach ($products as $p) {
                $writer->addRow(Row::fromValues([
                    $p->brand_name,
                    $p->generic_name ?? '',
                    $p->category?->name ?? '',
                    $p->illnessCategory?->name ?? 'Unmapped',
                    $p->description ?? '',
                    ucfirst($p->status),
                    strtoupper($p->source),
                    $p->confidence_score ? "{$p->confidence_score}%" : '',
                    $p->created_at ? $p->created_at->format('Y-m-d H:i') : '',
                ]));
            }
        });

        $writer->close();
    }

    /**
     * Generate sample import template with standard columns and example data.
     */
    public function generateSampleTemplate(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        // Header Row
        $writer->addRow(Row::fromValues([
            'Brand Name',
            'Generic Name',
            'Category',
            'Illness Category',
            'Description',
        ]));

        // Sample Data Rows (as specified in FRD)
        $writer->addRow(Row::fromValues([
            'Pantocid 40',
            'Pantoprazole',
            'Pharmaceuticals',
            'Gastric',
            'Market product for acidity and peptic ulcers',
        ]));

        $writer->addRow(Row::fromValues([
            'Glycomet 500',
            'Metformin Hydrochloride',
            'Pharmaceuticals',
            'Diabetes',
            'Oral antidiabetic medication',
        ]));

        $writer->addRow(Row::fromValues([
            'Atorva 20',
            'Atorvastatin',
            'Cardiovascular',
            'Cardiovascular',
            'Statin medication for cholesterol management',
        ]));

        $writer->addRow(Row::fromValues([
            'Calpol 650',
            'Paracetamol',
            'Analgesics',
            'Pain Management',
            'Fever and pain relief medication',
        ]));

        $writer->addRow(Row::fromValues([
            'Montek LC',
            'Montelukast + Levocetirizine',
            'Respiratory',
            'Respiratory',
            'Anti-allergic and respiratory maintenance',
        ]));

        $writer->close();
    }
}
