<?php

namespace App\Services;

use App\Models\IllnessCategory;
use App\Models\OtherProduct;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

class OtherProductImportService
{
    public function __construct(
        protected OtherProductMappingService $mappingService
    ) {}

    /**
     * Preview the first N rows of an Excel/CSV file without saving to DB.
     */
    public function preview(string $filePath, int $maxRows = 10): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $reader = ($extension === 'csv' || $extension === 'txt') ? new CsvReader : new XlsxReader;

        try {
            $reader->open($filePath);
        } catch (Throwable $e) {
            if ($extension !== 'csv') {
                try {
                    $reader = new CsvReader;
                    $reader->open($filePath);
                } catch (Throwable $fallbackError) {
                    return ['error' => 'Unable to read file: '.$e->getMessage(), 'rows' => [], 'total_valid' => 0, 'total_invalid' => 0];
                }
            } else {
                return ['error' => 'Unable to read file: '.$e->getMessage(), 'rows' => [], 'total_valid' => 0, 'total_invalid' => 0];
            }
        }

        $headerMap = null;
        $previewRows = [];
        $validCount = 0;
        $invalidCount = 0;
        $rowNumber = 0;

        $categoriesCache = ProductCategory::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])->toArray();
        $illnessCache = IllnessCategory::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])->toArray();

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    $cells = $row->toArray();

                    if ($this->isRowEmpty($cells)) {
                        continue;
                    }

                    if ($headerMap === null) {
                        $headerMap = $this->parseHeaderMap($cells);
                        continue;
                    }

                    $parsed = $this->parseRow($cells, $headerMap, $categoriesCache, $illnessCache);
                    $isValid = ! empty($parsed['brand_name']);

                    if ($isValid) {
                        $validCount++;
                    } else {
                        $invalidCount++;
                    }

                    if (count($previewRows) < $maxRows) {
                        $previewRows[] = array_merge($parsed, [
                            'row_number' => $rowNumber,
                            'is_valid' => $isValid,
                            'validation_notes' => $isValid ? 'Ready for import' : 'Missing mandatory Brand Name',
                        ]);
                    }
                }
                break; // First sheet only
            }
        } catch (Throwable $e) {
            Log::error('OtherProduct import preview failed: '.$e->getMessage());
        } finally {
            $reader->close();
        }

        return [
            'rows' => $previewRows,
            'total_previewed' => count($previewRows),
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'total_estimated' => $validCount + $invalidCount,
        ];
    }

    /**
     * High-volume streaming import for up to 50,000+ records.
     */
    public function import(string $filePath, array $options = []): array
    {
        $onDuplicate = $options['on_duplicate'] ?? 'skip'; // skip, update
        $defaultStatus = $options['default_status'] ?? 'active'; // active, unmapped
        $userId = $options['user_id'] ?? null;

        $stats = [
            'total_rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'duplicates_skipped' => 0,
            'invalid_skipped' => 0,
            'errors' => [],
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $reader = ($extension === 'csv' || $extension === 'txt') ? new CsvReader : new XlsxReader;

        try {
            $reader->open($filePath);
        } catch (Throwable $e) {
            Log::error('OtherProduct import reader open failed: '.$e->getMessage());
            if ($extension !== 'csv') {
                try {
                    $reader = new CsvReader;
                    $reader->open($filePath);
                } catch (Throwable $fallbackError) {
                    $stats['errors'][] = 'Unable to read file: '.$e->getMessage();

                    return $stats;
                }
            } else {
                $stats['errors'][] = 'Unable to read file: '.$e->getMessage();

                return $stats;
            }
        }

        $headerMap = null;
        $batch = [];
        $batchSize = 500;
        $rowNumber = 0;
        $now = Carbon::now();

        // Preload caches
        $categoriesCache = ProductCategory::all()->keyBy(fn ($c) => strtolower(trim($c->name)));
        $illnessCache = IllnessCategory::all()->keyBy(fn ($ic) => strtolower(trim($ic->name)));
        $existingOtherProducts = OtherProduct::pluck('id', 'brand_name')->mapWithKeys(fn ($id, $brand) => [strtolower(trim($brand)) => $id])->toArray();

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    $cells = $row->toArray();

                    if ($this->isRowEmpty($cells)) {
                        continue;
                    }

                    if ($headerMap === null) {
                        $headerMap = $this->parseHeaderMap($cells);
                        continue;
                    }

                    $stats['total_rows']++;

                    $parsed = $this->parseRow($cells, $headerMap, $categoriesCache, $illnessCache);

                    if (empty($parsed['brand_name'])) {
                        $stats['invalid_skipped']++;
                        if (count($stats['errors']) < 25) {
                            $stats['errors'][] = "Row #{$rowNumber}: Skipped due to missing Brand Name.";
                        }
                        continue;
                    }

                    $brandKey = strtolower(trim($parsed['brand_name']));
                    $isDuplicate = isset($existingOtherProducts[$brandKey]);

                    if ($isDuplicate && $onDuplicate === 'skip') {
                        $stats['duplicates_skipped']++;
                        continue;
                    }

                    // Auto suggest illness category if not provided
                    $suggestedId = null;
                    $confidence = null;
                    if (empty($parsed['illness_category_id'])) {
                        $suggestions = $this->mappingService->suggestIllnessCategories($parsed['brand_name'], $parsed['generic_name'], 1);
                        if (! empty($suggestions)) {
                            $suggestedId = $suggestions[0]['illness_category_id'];
                            $confidence = $suggestions[0]['confidence_score'];
                        }
                    }

                    $recordStatus = ! empty($parsed['illness_category_id']) ? $defaultStatus : 'unmapped';
                    $isVerified = ($recordStatus === 'active' && ! empty($parsed['illness_category_id']));

                    $record = [
                        'brand_name' => $parsed['brand_name'],
                        'generic_name' => $parsed['generic_name'],
                        'category_id' => $parsed['category_id'],
                        'illness_category_id' => $parsed['illness_category_id'],
                        'suggested_illness_category_id' => $suggestedId,
                        'confidence_score' => $confidence,
                        'description' => $parsed['description'],
                        'status' => $recordStatus,
                        'is_verified' => $isVerified,
                        'source' => 'import',
                        'user_id' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($isDuplicate && $onDuplicate === 'update') {
                        $existingId = $existingOtherProducts[$brandKey];
                        OtherProduct::where('id', $existingId)->update([
                            'generic_name' => $record['generic_name'] ?: DB::raw('generic_name'),
                            'category_id' => $record['category_id'] ?: DB::raw('category_id'),
                            'illness_category_id' => $record['illness_category_id'] ?: DB::raw('illness_category_id'),
                            'description' => $record['description'] ?: DB::raw('description'),
                            'status' => $record['status'],
                            'is_verified' => $record['is_verified'],
                            'updated_at' => $now,
                        ]);
                        $stats['updated']++;
                    } else {
                        $batch[] = $record;
                        $existingOtherProducts[$brandKey] = true;

                        if (count($batch) >= $batchSize) {
                            OtherProduct::insert($batch);
                            $stats['imported'] += count($batch);
                            $batch = [];
                        }
                    }
                }
                break; // Process primary sheet
            }

            // Flush remaining items
            if (! empty($batch)) {
                OtherProduct::insert($batch);
                $stats['imported'] += count($batch);
                $batch = [];
            }
        } catch (Throwable $e) {
            Log::error('OtherProduct import process failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $stats['errors'][] = 'Fatal error during import: '.$e->getMessage();
        } finally {
            $reader->close();
        }

        return $stats;
    }

    /**
     * Parse header row mapping.
     */
    protected function parseHeaderMap(array $cells): array
    {
        $map = [
            'brand_name' => 0,
            'generic_name' => 1,
            'category' => 2,
            'illness_category' => 3,
            'description' => 4,
        ];

        foreach ($cells as $index => $cell) {
            $text = strtolower(trim((string) $cell));
            if (str_contains($text, 'brand')) {
                $map['brand_name'] = $index;
            } elseif (str_contains($text, 'generic')) {
                $map['generic_name'] = $index;
            } elseif (str_contains($text, 'illness')) {
                $map['illness_category'] = $index;
            } elseif (str_contains($text, 'category') || str_contains($text, 'prod_cat')) {
                $map['category'] = $index;
            } elseif (str_contains($text, 'desc')) {
                $map['description'] = $index;
            }
        }

        return $map;
    }

    /**
     * Parse a single row into structured attributes.
     */
    protected function parseRow(array $cells, array $headerMap, $categoriesCache, $illnessCache): array
    {
        $brandName = trim((string) ($cells[$headerMap['brand_name']] ?? ''));
        $genericName = trim((string) ($cells[$headerMap['generic_name']] ?? ''));
        $categoryRaw = trim((string) ($cells[$headerMap['category']] ?? ''));
        $illnessRaw = trim((string) ($cells[$headerMap['illness_category']] ?? ''));
        $description = trim((string) ($cells[$headerMap['description']] ?? ''));

        // Match category
        $categoryId = null;
        if (! empty($categoryRaw)) {
            $key = strtolower(trim($categoryRaw));
            if (isset($categoriesCache[$key])) {
                $categoryId = is_object($categoriesCache[$key]) ? $categoriesCache[$key]->id : $categoriesCache[$key];
            } else {
                // Case-insensitive lookup before creating
                $matchedCat = ProductCategory::whereRaw('LOWER(name) = ?', [$key])->first();
                if ($matchedCat) {
                    $categoryId = $matchedCat->id;
                } else {
                    $newCat = ProductCategory::create(['name' => trim($categoryRaw)]);
                    $categoryId = $newCat->id;
                }
                if (is_array($categoriesCache)) {
                    $categoriesCache[$key] = $categoryId;
                }
            }
        }

        // Match illness category
        $illnessCategoryId = null;
        if (! empty($illnessRaw)) {
            $key = strtolower($illnessRaw);
            if (isset($illnessCache[$key])) {
                $illnessCategoryId = is_object($illnessCache[$key]) ? $illnessCache[$key]->id : $illnessCache[$key];
            } else {
                // Try fuzzy matching illness category
                $matchedCat = IllnessCategory::where('name', 'like', "%{$illnessRaw}%")->first();
                if ($matchedCat) {
                    $illnessCategoryId = $matchedCat->id;
                }
            }
        }

        return [
            'brand_name' => $brandName,
            'generic_name' => $genericName ?: null,
            'category_name' => $categoryRaw ?: null,
            'category_id' => $categoryId,
            'illness_category_name' => $illnessRaw ?: null,
            'illness_category_id' => $illnessCategoryId,
            'description' => $description ?: null,
        ];
    }

    /**
     * Check if a row is completely empty.
     */
    protected function isRowEmpty(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
