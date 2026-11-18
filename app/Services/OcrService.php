<?php

namespace App\Services;

use App\Ai\Agents\ReceiptParser;
use App\Models\OcrJob;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    /**
     * Process a file through OCR and create an OCR job.
     */
    public function processFile(UploadedFile $file, string $moduleName, ?string $engineName = null): OcrJob
    {
        $engineName ??= config('ocr.default_engine', 'tesseract');

        $ocrJob = OcrJob::create([
            'module_name' => $moduleName,
            'source_file_id' => $file->id,
            'status' => 'processing',
            'engine_name' => $engineName,
            'started_at' => now(),
        ]);

        try {
            $filePath = Storage::path($file->file_path);
            $rawText = $this->extractText($filePath, $engineName);
            $parsedData = $this->parseText($rawText, $moduleName);

            $ocrJob->update([
                'status' => 'completed',
                'raw_text' => $rawText,
                'parsed_json' => $parsedData,
                'confidence_score' => $parsedData['confidence_score'] ?? 0.0,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $ocrJob->update([
                'status' => 'failed',
                'raw_text' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }

        return $ocrJob->fresh();
    }

    /**
     * Re-process a failed OCR job.
     */
    public function reprocess(OcrJob $ocrJob): OcrJob
    {
        if ($ocrJob->status !== 'failed') {
            throw new \RuntimeException('Only failed OCR jobs can be reprocessed.');
        }

        return $this->processFile(
            $ocrJob->sourceFile,
            $ocrJob->module_name,
            $ocrJob->engine_name
        );
    }

    /**
     * Extract text from a file using the specified engine.
     */
    protected function extractText(string $filePath, string $engineName): string
    {
        // TODO: Integrate with Tesseract, Google Vision, or AWS Textract
        throw new \RuntimeException('OCR engine integration not implemented. Configure '.$engineName.' in config/ocr.php');
    }

    /**
     * Parse raw OCR text into structured data based on the module.
     */
    protected function parseText(string $rawText, string $moduleName): array
    {
        $parsed = match ($moduleName) {
            'purchase_bill' => $this->parsePurchaseBill($rawText),
            'prescription' => $this->parsePrescription($rawText),
            default => ['raw' => $rawText, 'confidence_score' => 0.0],
        };

        return $parsed;
    }

    /**
     * Parse purchase bill text into structured data.
     */
    protected function parsePurchaseBill(string $text): array
    {
        return app(ReceiptParser::class)->parse($text);
    }

    /**
     * Parse prescription text into structured data.
     */
    protected function parsePrescription(string $text): array
    {
        // TODO: Implement specific parsing logic for prescriptions
        return [
            'lines' => explode("\n", trim($text)),
            'confidence_score' => 0.0,
        ];
    }
}
