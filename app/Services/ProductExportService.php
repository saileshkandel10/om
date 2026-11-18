<?php

namespace App\Services;

use App\Models\Product;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class ProductExportService
{
    /**
     * Export Own Products based on filters.
     */
    public function export(string $filePath, array $filters = []): void
    {
        $format = ($filters['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;

        $writer->openToFile($filePath);

        // Header Row
        $writer->addRow(Row::fromValues([
            'SKU',
            'Brand Name',
            'Generic Name',
            'Strength',
            'Dosage Form',
            'Pack Size',
            'Category',
            'Illness Category',
            'MRP (Rs)',
            'Launch Status',
            'Status',
            'Description',
            'Created Date',
        ]));

        $query = Product::with(['category', 'illnessCategory'])->latest('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('product_category_id', $filters['category_id']);
        }

        if (! empty($filters['illness_category_id'])) {
            $query->where('illness_category_id', $filters['illness_category_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        $query->chunk(500, function ($products) use ($writer) {
            foreach ($products as $p) {
                $writer->addRow(Row::fromValues([
                    $p->sku ?? '',
                    $p->brand_name,
                    $p->generic_name ?? '',
                    $p->strength ?? '',
                    $p->dosage_form ?? '',
                    $p->pack_size ?? '',
                    $p->category?->name ?? 'Uncategorized',
                    $p->illnessCategory?->name ?? 'Unassigned',
                    $p->mrp !== null ? number_format((float) $p->mrp, 2, '.', '') : '',
                    ucfirst($p->launch_status ?? 'launched'),
                    $p->is_active ? 'Active' : 'Inactive',
                    $p->description ?? '',
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
            'SKU',
            'Brand Name',
            'Generic Name',
            'Category',
            'Illness Category',
            'Strength',
            'Dosage Form',
            'Pack Size',
            'MRP',
            'Launch Status',
            'Status',
            'Description',
        ]));

        // Sample Data Rows
        $writer->addRow(Row::fromValues([
            'OM-AMX-500',
            'Omox 500mg',
            'Amoxicillin Trihydrate',
            'Antibiotics',
            'Bacterial Infection',
            '500mg',
            'Capsule',
            '10x10 Strips',
            '150.00',
            'launched',
            'Active',
            'Broad spectrum antibiotic for respiratory and bacterial infections',
        ]));

        $writer->addRow(Row::fromValues([
            'OM-PAN-40',
            'Ompan 40mg',
            'Pantoprazole Sodium',
            'Gastrointestinal',
            'Acid Reflux & GERD',
            '40mg',
            'Tablet',
            '10x10 Alu-Alu',
            '120.00',
            'launched',
            'Active',
            'Proton pump inhibitor for acidity, GERD, and peptic ulcers',
        ]));

        $writer->addRow(Row::fromValues([
            'OM-ATV-10',
            'Omtor 10mg',
            'Atorvastatin Calcium',
            'Cardiovascular',
            'Hypertension & Cholesterol',
            '10mg',
            'Tablet',
            '3x10 Blister',
            '210.00',
            'launched',
            'Active',
            'Lipid-lowering medication for cardiovascular disease prevention',
        ]));

        $writer->addRow(Row::fromValues([
            'OM-PCM-650',
            'Ompara 650mg',
            'Paracetamol',
            'Analgesics & Antipyretics',
            'Fever & Pain Relief',
            '650mg',
            'Tablet',
            '20x10 Strips',
            '45.00',
            'launched',
            'Active',
            'Analgesic and antipyretic for moderate pain and fever management',
        ]));

        $writer->close();
    }
}
