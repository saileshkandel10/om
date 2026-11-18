<?php

namespace App\Services;

use App\Models\ShareholderMaster;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Throwable;

class ShareholderImportService
{
    /**
     * Import shareholders from an Excel (.xlsx, .xls) or CSV file.
     *
     * @param  string  $filePath  Absolute path to the uploaded file
     * @param  array<string, mixed>  $options
     * @return array{
     *     total_rows: int,
     *     imported: int,
     *     updated: int,
     *     duplicates_skipped: int,
     *     invalid_skipped: int,
     *     errors: array<string>
     * }
     */
    public function import(string $filePath, array $options = []): array
    {
        $onDuplicate = $options['on_duplicate'] ?? 'skip'; // 'skip' or 'update'
        $defaultStatus = $options['default_status'] ?? 'active';
        $customSourceNote = $options['source_note'] ?? null;

        $stats = [
            'total_rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'duplicates_skipped' => 0,
            'invalid_skipped' => 0,
            'errors' => [],
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Use streaming reader based on extension
        if ($extension === 'csv' || $extension === 'txt') {
            $reader = new CsvReader;
        } else {
            $reader = new XlsxReader;
        }

        try {
            $reader->open($filePath);
        } catch (Throwable $e) {
            Log::error('Shareholder import reader open failed: '.$e->getMessage());
            // Fallback for CSV if XLSX reader failed or vice versa
            if ($extension !== 'csv') {
                try {
                    $reader = new CsvReader;
                    $reader->open($filePath);
                } catch (Throwable $fallbackError) {
                    $stats['errors'][] = 'Unable to read the file format: '.$e->getMessage();

                    return $stats;
                }
            } else {
                $stats['errors'][] = 'Unable to read file: '.$e->getMessage();

                return $stats;
            }
        }

        $headerMap = null;
        $batch = [];
        $batchSize = 1000;
        $seenBoidsInFile = [];
        $rowNumber = 0;
        $now = Carbon::now();

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    $cells = $row->toArray();

                    // Check if entire row is empty
                    if ($this->isRowEmpty($cells)) {
                        continue;
                    }

                    // First non-empty row is header
                    if ($headerMap === null) {
                        $headerMap = $this->mapHeaders($cells);

                        continue;
                    }

                    $stats['total_rows']++;

                    $parsed = $this->parseRow($cells, $headerMap, $defaultStatus, $customSourceNote, $now);

                    if (! $parsed['valid']) {
                        $stats['invalid_skipped']++;
                        if (count($stats['errors']) < 10) {
                            $stats['errors'][] = "Row {$rowNumber}: {$parsed['reason']}";
                        }

                        continue;
                    }

                    $boid = $parsed['data']['boid'];

                    // Check for intra-file duplicate
                    if (isset($seenBoidsInFile[$boid])) {
                        $stats['duplicates_skipped']++;

                        continue;
                    }

                    $seenBoidsInFile[$boid] = true;
                    $batch[] = $parsed['data'];

                    if (count($batch) >= $batchSize) {
                        $this->processBatch($batch, $onDuplicate, $stats, $now);
                        $batch = [];
                    }
                }

                // Process first sheet only
                break;
            }

            // Process any remaining rows in the final batch
            if (! empty($batch)) {
                $this->processBatch($batch, $onDuplicate, $stats, $now);
                $batch = [];
            }
        } catch (Throwable $e) {
            Log::error('Error processing shareholder import: '.$e->getMessage());
            $stats['errors'][] = 'An error occurred during import: '.$e->getMessage();
        } finally {
            $reader->close();
        }

        return $stats;
    }

    /**
     * Process a batch of parsed records against the database.
     *
     * @param  array<int, array<string, mixed>>  $batch
     * @param  array<string, mixed>  $stats
     */
    protected function processBatch(array $batch, string $onDuplicate, array &$stats, Carbon $now): void
    {
        if (empty($batch)) {
            return;
        }

        $boids = array_column($batch, 'boid');

        // Look up existing records in database by BOID
        $existingRecords = ShareholderMaster::whereIn('boid', $boids)
            ->get()
            ->keyBy('boid');

        $toInsert = [];

        DB::beginTransaction();
        try {
            foreach ($batch as $record) {
                $boid = $record['boid'];

                if ($existingRecords->has($boid)) {
                    if ($onDuplicate === 'update') {
                        /** @var ShareholderMaster $existing */
                        $existing = $existingRecords->get($boid);
                        $existing->update([
                            'shareholder_name' => $record['shareholder_name'],
                            'mobile' => $record['mobile'] ?? $existing->mobile,
                            'email' => $record['email'] ?? $existing->email,
                            'status' => $record['status'] ?? $existing->status,
                            'source_note' => $record['source_note'] ?? $existing->source_note,
                        ]);
                        $stats['updated']++;
                    } else {
                        // Skip duplicate
                        $stats['duplicates_skipped']++;
                    }
                } else {
                    $toInsert[] = $record;
                }
            }

            if (! empty($toInsert)) {
                foreach (array_chunk($toInsert, 500) as $chunk) {
                    ShareholderMaster::insert($chunk);
                }
                $stats['imported'] += count($toInsert);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Batch insert failed for shareholders: '.$e->getMessage());
            $stats['errors'][] = 'Batch database error: '.$e->getMessage();
        }
    }

    /**
     * Map header columns to known attribute names.
     *
     * @param  array<int, mixed>  $headerRow
     * @return array<string, int|null>
     */
    protected function mapHeaders(array $headerRow): array
    {
        $map = [
            'boid' => null,
            'shareholder_name' => null,
            'mobile' => null,
            'email' => null,
            'status' => null,
            'source_note' => null,
        ];

        $aliases = [
            'boid' => [
                'boid', 'boidno', 'boidnumber', 'beneficialownerid', 'demat',
                'dematno', 'dematnumber', 'accountno', 'accountnumber', 'clientid',
                'boidnum', 'shareholderid', 'boidcode',
            ],
            'shareholder_name' => [
                'shareholdername', 'name', 'fullname', 'shareholder', 'clientname',
                'investorname', 'holdername', 'nameoftheholder', 'shareholderfullname',
                'membername',
            ],
            'mobile' => [
                'mobile', 'mobileno', 'mobilenumber', 'phone', 'phoneno',
                'phonenumber', 'contact', 'contactno', 'contactnumber', 'cell',
                'cellno', 'telephone',
            ],
            'email' => [
                'email', 'emailaddress', 'mail', 'emailid',
            ],
            'status' => [
                'status', 'accountstatus', 'state',
            ],
            'source_note' => [
                'sourcenote', 'notes', 'note', 'remarks', 'remark', 'source', 'comments',
            ],
        ];

        foreach ($headerRow as $index => $header) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $header));
            if ($normalized === '') {
                continue;
            }

            foreach ($aliases as $field => $aliasList) {
                if ($map[$field] === null && in_array($normalized, $aliasList, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        // Fallback to positional mapping if BOID or Name could not be mapped
        if ($map['boid'] === null && isset($headerRow[0])) {
            $map['boid'] = 0;
        }
        if ($map['shareholder_name'] === null && isset($headerRow[1])) {
            $map['shareholder_name'] = 1;
        }
        if ($map['mobile'] === null && isset($headerRow[2])) {
            $map['mobile'] = 2;
        }
        if ($map['email'] === null && isset($headerRow[3])) {
            $map['email'] = 3;
        }
        if ($map['status'] === null && isset($headerRow[4])) {
            $map['status'] = 4;
        }
        if ($map['source_note'] === null && isset($headerRow[5])) {
            $map['source_note'] = 5;
        }

        return $map;
    }

    /**
     * Parse and sanitize a single row.
     *
     * @param  array<int, mixed>  $cells
     * @param  array<string, int|null>  $map
     * @return array{valid: bool, data: array<string, mixed>, reason?: string}
     */
    protected function parseRow(array $cells, array $map, string $defaultStatus, ?string $customSourceNote, Carbon $now): array
    {
        $rawBoid = $map['boid'] !== null && isset($cells[$map['boid']]) ? $cells[$map['boid']] : null;
        $rawName = $map['shareholder_name'] !== null && isset($cells[$map['shareholder_name']]) ? $cells[$map['shareholder_name']] : null;
        $rawMobile = $map['mobile'] !== null && isset($cells[$map['mobile']]) ? $cells[$map['mobile']] : null;
        $rawEmail = $map['email'] !== null && isset($cells[$map['email']]) ? $cells[$map['email']] : null;
        $rawStatus = $map['status'] !== null && isset($cells[$map['status']]) ? $cells[$map['status']] : null;
        $rawSourceNote = $map['source_note'] !== null && isset($cells[$map['source_note']]) ? $cells[$map['source_note']] : null;

        $boid = $this->formatScalarValue($rawBoid);
        $name = $this->formatScalarValue($rawName);
        $mobile = $this->formatScalarValue($rawMobile);
        $email = $this->formatScalarValue($rawEmail);
        $status = strtolower($this->formatScalarValue($rawStatus) ?: $defaultStatus);
        $sourceNote = $this->formatScalarValue($rawSourceNote);

        if ($boid === '') {
            return ['valid' => false, 'data' => [], 'reason' => 'Missing BOID'];
        }

        if ($name === '') {
            return ['valid' => false, 'data' => [], 'reason' => 'Missing shareholder name'];
        }

        if (! in_array($status, ['active', 'inactive', 'pending'], true)) {
            $status = $defaultStatus;
        }

        $note = $customSourceNote ?: ($sourceNote ?: 'Excel Import on '.$now->toDateString());

        return [
            'valid' => true,
            'data' => [
                'boid' => $boid,
                'shareholder_name' => $name,
                'mobile' => $mobile ?: null,
                'email' => $email ?: null,
                'status' => $status,
                'source_note' => $note,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }

    /**
     * Check if all cells in a row are empty.
     *
     * @param  array<int, mixed>  $cells
     */
    protected function isRowEmpty(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Format a scalar value from cell.
     */
    protected function formatScalarValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value)) {
            // Avoid scientific notation for large numbers like BOID 1301020000123456
            return number_format($value, 0, '', '');
        }

        return trim((string) $value);
    }

    /**
     * Generate a sample template file.
     */
    public function generateSampleFile(string $filePath, string $format = 'xlsx'): void
    {
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
            'Status',
            'Source Note',
        ]));

        // Sample Data Rows
        $writer->addRow(Row::fromValues([
            '1301020000000001',
            'Ram Bahadur Shrestha',
            '9841234567',
            'ram.shrestha@example.com',
            'active',
            'Initial Shareholder Registry',
        ]));

        $writer->addRow(Row::fromValues([
            '1301020000000002',
            'Sita Devi Sharma',
            '9851234568',
            'sita.sharma@example.com',
            'active',
            'Initial Shareholder Registry',
        ]));

        $writer->addRow(Row::fromValues([
            '1301020000000003',
            'Hari Prasad Adhikari',
            '9801234569',
            'hari.adhikari@example.com',
            'active',
            'Initial Shareholder Registry',
        ]));

        $writer->close();
    }
}
