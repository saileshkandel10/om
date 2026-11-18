<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

class ProductImportService
{
    public function import(string $filePath, array $options = []): array
    {
        $onDuplicate = $options['on_duplicate'] ?? 'update';
        $defaultStatus = $options['default_status'] ?? 'active';
        $autoCreateCategories = $options['auto_create_categories'] ?? false;

        $stats = [
            'total_rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'duplicates_skipped' => 0,
            'invalid_skipped' => 0,
            'errors' => [],
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            $reader = new CsvReader;
        } else {
            $reader = new XlsxReader;
        }

        try {
            $reader->open($filePath);
        } catch (Throwable $e) {
            Log::error('Product import reader open failed: '.$e->getMessage());

            // Try fallback
            try {
                $reader = new CsvReader;
                $reader->open($filePath);
            } catch (Throwable $fallbackError) {
                $stats['errors'][] = 'Unable to read file: '.$e->getMessage();

                return $stats;
            }
        }

        $headerMap = null;
        $batch = [];
        $batchSize = 500;
        $seenSkusInFile = [];
        $rowNumber = 0;
        $now = Carbon::now();

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    $cells = $row->toArray();

                    if ($this->isRowEmpty($cells)) {
                        continue;
                    }

                    if ($headerMap === null) {
                        $headerMap = $this->mapHeaders($cells);

                        continue;
                    }

                    $stats['total_rows']++;

                    $parsed = $this->parseRow($cells, $headerMap, $defaultStatus, $now, (bool) $autoCreateCategories);

                    if (! $parsed['valid']) {
                        $stats['invalid_skipped']++;
                        if (count($stats['errors']) < 10) {
                            $stats['errors'][] = "Row {$rowNumber}: {$parsed['reason']}";
                        }

                        continue;
                    }

                    $sku = $parsed['data']['sku'];

                    if (isset($seenSkusInFile[$sku])) {
                        $stats['duplicates_skipped']++;

                        continue;
                    }

                    $seenSkusInFile[$sku] = true;
                    $batch[] = $parsed['data'];

                    if (count($batch) >= $batchSize) {
                        $this->processBatch($batch, $onDuplicate, $stats, $now);
                        $batch = [];
                    }
                }
                break;
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $onDuplicate, $stats, $now);
            }
        } catch (Throwable $e) {
            Log::error('Error processing product import: '.$e->getMessage());
            $stats['errors'][] = 'An error occurred during import: '.$e->getMessage();
        } finally {
            $reader->close();
        }

        return $stats;
    }

    protected function processBatch(array $batch, string $onDuplicate, array &$stats, Carbon $now): void
    {
        $skus = array_column($batch, 'sku');
        $existingProducts = Product::whereIn('sku', $skus)->get()->keyBy('sku');

        DB::beginTransaction();
        try {
            foreach ($batch as $record) {
                $sku = $record['sku'];

                if ($existingProducts->has($sku)) {
                    if ($onDuplicate === 'update') {
                        $product = $existingProducts->get($sku);
                        $product->update($record);
                        $stats['updated']++;
                    } else {
                        $stats['duplicates_skipped']++;
                    }
                } else {
                    Product::create($record);
                    $stats['imported']++;
                }
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Batch insert failed for products: '.$e->getMessage());
            $stats['errors'][] = 'Batch database error: '.$e->getMessage();
        }
    }

    protected function mapHeaders(array $headerRow): array
    {
        $map = ['sku' => null, 'brand_name' => null, 'generic_name' => null, 'category_name' => null, 'mrp' => null, 'status' => null];
        $aliases = [
            'sku' => ['sku', 'productcode', 'code', 'id'],
            'brand_name' => ['brand', 'brandname', 'productname', 'name', 'item'],
            'generic_name' => ['generic', 'composition', 'genericname'],
            'category_name' => ['category', 'group'],
            'mrp' => ['mrp', 'price', 'rate', 'unitprice'],
            'status' => ['status'],
        ];

        foreach ($headerRow as $index => $header) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $header));
            foreach ($aliases as $field => $aliasList) {
                if ($map[$field] === null && in_array($normalized, $aliasList, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        // Defaults if not found
        if ($map['sku'] === null && isset($headerRow[0])) {
            $map['sku'] = 0;
        }
        if ($map['brand_name'] === null && isset($headerRow[1])) {
            $map['brand_name'] = 1;
        }

        return $map;
    }

    protected function parseRow(array $cells, array $map, string $defaultStatus, Carbon $now, bool $autoCreateCategories): array
    {
        $sku = $this->formatScalarValue($map['sku'] !== null ? ($cells[$map['sku']] ?? '') : '');
        $brandName = $this->formatScalarValue($map['brand_name'] !== null ? ($cells[$map['brand_name']] ?? '') : '');
        $categoryName = $this->formatScalarValue($map['category_name'] !== null ? ($cells[$map['category_name']] ?? '') : '');
        $mrp = (float) $this->formatScalarValue($map['mrp'] !== null ? ($cells[$map['mrp']] ?? 0) : 0);
        $status = strtolower($this->formatScalarValue($map['status'] ?: $defaultStatus));

        if ($sku === '' || $brandName === '') {
            return ['valid' => false, 'data' => [], 'reason' => 'Missing SKU or Brand Name'];
        }

        $categoryId = null;
        if ($categoryName !== '') {
            $cat = ProductCategory::firstOrCreate(['name' => $categoryName], ['slug' => Str::slug($categoryName)]);
            $categoryId = $cat->id;
        }

        return [
            'valid' => true,
            'data' => [
                'sku' => $sku,
                'brand_name' => $brandName,
                'generic_name' => $this->formatScalarValue($map['generic_name'] !== null ? ($cells[$map['generic_name']] ?? '') : ''),
                'product_category_id' => $categoryId,
                'mrp' => $mrp,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }

    protected function isRowEmpty(array $cells): bool
    {
        return empty(array_filter($cells, fn ($c) => trim((string) $c) !== ''));
    }

    protected function formatScalarValue(mixed $value): string
    {
        return trim((string) $value);
    }
}
