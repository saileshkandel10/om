<?php

namespace App\Services;

use Carbon\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class ProductDemandExportService
{
    public function __construct(
        protected ProductDemandAnalyticsService $analyticsService
    ) {}

    /**
     * Generate dynamic export filename.
     */
    public function generateFilename(array $filters, string $extension = 'xlsx'): string
    {
        $type = $filters['export_type'] ?? 'product_demand';
        $date = Carbon::now()->format('Y-m-d');

        return "{$type}_report_{$date}.{$extension}";
    }

    /**
     * Export demand report data to Excel or CSV.
     */
    public function export(string $filePath, array $filters = []): void
    {
        $format = ($filters['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $type = $filters['export_type'] ?? 'product_demand';

        if ($format === 'csv') {
            $writer = new CsvWriter;
        } else {
            $writer = new XlsxWriter;
        }

        $writer->openToFile($filePath);

        $data = $this->analyticsService->getDemandReportData($filters);

        switch ($type) {
            case 'district_demand':
                $this->exportDistrictDemand($writer, $data);
                break;
            case 'customer_behaviour':
                $this->exportCustomerBehaviour($writer, $data);
                break;
            case 'product_district':
                $this->exportProductDistrict($writer, $data);
                break;
            case 'complete':
                $this->exportCompleteReport($writer, $data);
                break;
            case 'product_demand':
            default:
                $this->exportProductDemand($writer, $data);
                break;
        }

        $writer->close();
    }

    protected function exportProductDemand($writer, array $data): void
    {
        $writer->addRow(Row::fromValues([
            'Rank',
            'Product Name',
            'Category',
            'Units Sold',
            'Previous Period Units',
            'Orders Count',
            'Unique Customers',
            'Revenue (NPR)',
            'Growth (%)',
            'Demand Score',
        ]));

        foreach ($data['trending_products'] as $prod) {
            $writer->addRow(Row::fromValues([
                (int) $prod['rank'],
                (string) $prod['name'],
                (string) $prod['category'],
                (float) $prod['units_sold'],
                (float) $prod['previous_units'],
                (int) $prod['orders_count'],
                (int) $prod['customers_count'],
                (float) $prod['revenue'],
                (float) $prod['growth_percent'],
                (float) $prod['demand_score'],
            ]));
        }
    }

    protected function exportDistrictDemand($writer, array $data): void
    {
        $writer->addRow(Row::fromValues([
            'Rank',
            'District Name',
            'Province',
            'Orders Count',
            'Unique Customers',
            'Units Sold',
            'Revenue (NPR)',
            '% of Total Orders',
            '% of Total Revenue',
        ]));

        foreach ($data['district_demand'] as $d) {
            $writer->addRow(Row::fromValues([
                (int) $d['rank'],
                (string) $d['name'],
                (string) $d['province_name'],
                (int) $d['orders_count'],
                (int) $d['customers_count'],
                (float) $d['units_sold'],
                (float) $d['revenue'],
                $d['order_percentage'].'%',
                $d['revenue_percentage'].'%',
            ]));
        }
    }

    protected function exportCustomerBehaviour($writer, array $data): void
    {
        $writer->addRow(Row::fromValues([
            'Month',
            'New Customers',
            'Returning Customers',
            'Total Orders',
            'Average Order Value (NPR)',
            'Repeat Purchase Rate (%)',
            'Total Revenue (NPR)',
        ]));

        foreach ($data['customer_behaviour']['monthly_behaviour'] as $m) {
            $writer->addRow(Row::fromValues([
                (string) $m['month'],
                (int) $m['new_customers'],
                (int) $m['returning_customers'],
                (int) $m['orders_count'],
                (float) $m['aov'],
                $m['repeat_rate'].'%',
                (float) $m['revenue'],
            ]));
        }
    }

    protected function exportProductDistrict($writer, array $data): void
    {
        $matrix = $data['product_district_matrix'];
        $products = $matrix['products'] ?? [];

        $headers = array_merge(['District'], $products, ['Total Units']);
        $writer->addRow(Row::fromValues($headers));

        foreach ($matrix['matrix'] as $dist => $row) {
            $values = [(string) $dist];
            foreach ($products as $p) {
                $values[] = (float) ($row[$p] ?? 0);
            }
            $values[] = (float) ($row['_total'] ?? 0);
            $writer->addRow(Row::fromValues($values));
        }
    }

    protected function exportCompleteReport($writer, array $data): void
    {
        $writer->addRow(Row::fromValues(['=== PRODUCT DEMAND & PURCHASING BEHAVIOUR REPORT ===']));
        $writer->addRow(Row::fromValues(['Total Orders', (int) $data['summary']['total_orders']]));
        $writer->addRow(Row::fromValues(['Total Units Sold', (float) $data['summary']['total_units_sold']]));
        $writer->addRow(Row::fromValues(['Total Revenue (NPR)', (float) $data['summary']['total_revenue']]));
        $writer->addRow(Row::fromValues(['Unique Customers', (int) $data['summary']['total_customers']]));
        $writer->addRow(Row::fromValues(['Repeat Customers', (int) $data['summary']['repeat_customers']]));
        $writer->addRow(Row::fromValues(['Average Order Value (NPR)', (float) $data['summary']['average_order_value']]));
        $writer->addRow(Row::fromValues(['Top Purchasing District', (string) $data['summary']['top_purchasing_district']]));
        $writer->addRow(Row::fromValues(['Most Demanded Product', (string) $data['summary']['most_demanded_product']]));
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValues(['=== TOP TRENDING PRODUCTS ===']));
        $this->exportProductDemand($writer, $data);
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValues(['=== DISTRICT DEMAND RANKING ===']));
        $this->exportDistrictDemand($writer, $data);
    }
}
