<?php

namespace App\Services;

use App\Ai\Agents\ReceiptParser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrSpaceService
{
    protected Client $client;

    protected ?string $apiKey;

    protected string $endpoint;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 60,
            'connect_timeout' => 10,
            'verify' => false,
        ]);

        $this->apiKey = config('services.ocr_space.api_key', 'K83667067388957');
        $this->endpoint = config('services.ocr_space.endpoint', 'https://api.ocr.space/parse/image');
    }

    /**
     * Send a receipt image to OCR.space (Engine 2 High Accuracy) and return the full result.
     * Absolutely no fake data generation is permitted.
     *
     * @return array{success: bool, text: ?string, full_response: ?array, extracted: array, error: ?string}
     */
    public function processReceipt(string $storagePath): array
    {
        $fullPath = Storage::disk('public')->path($storagePath);

        if (! file_exists($fullPath)) {
            Log::warning('OCR: File not found', ['path' => $fullPath]);

            return $this->errorResponse('File not found at path: '.$fullPath);
        }

        $useApi = ! empty($this->apiKey) && str_starts_with($this->apiKey, 'K');

        if (! $useApi) {
            return $this->errorResponse('OCR_SPACE_API_KEY is missing or invalid.');
        }

        try {
            Log::info('OCR: Sending image to OCR.space API...', [
                'endpoint' => $this->endpoint,
                'file' => basename($fullPath),
            ]);

            $response = $this->client->post($this->endpoint, [
                'multipart' => [
                    ['name' => 'apikey', 'contents' => $this->apiKey],
                    [
                        'name' => 'file',
                        'contents' => fopen($fullPath, 'r'),
                        'filename' => basename($fullPath),
                    ],
                    ['name' => 'language', 'contents' => 'eng'],
                    ['name' => 'isTable', 'contents' => 'true'],
                    ['name' => 'OCREngine', 'contents' => '2'],
                    ['name' => 'scale', 'contents' => 'true'],
                    ['name' => 'detectOrientation', 'contents' => 'true'],
                    ['name' => 'isOverlayRequired', 'contents' => 'false'],
                ],
            ]);

            $fullResponse = json_decode($response->getBody()->getContents(), true);

            if (($fullResponse['OCRExitCode'] ?? 0) === 1) {
                // Success parsing
                $parsedText = $fullResponse['ParsedResults'][0]['ParsedText'] ?? null;
                if ($parsedText && trim($parsedText) !== '') {
                    $text = trim($parsedText);
                    $extracted = app(ReceiptParser::class)->parse($text);

                    Log::info('OCR: Successfully parsed text from OCR.space');

                    return [
                        'success' => true,
                        'text' => $text,
                        'full_response' => $fullResponse,
                        'extracted' => $extracted,
                        'error' => null,
                    ];
                }

                // If OCR succeeded but no text was found (image too blurry, dark, or blank)
                return $this->errorResponse('OCR process succeeded, but no legible text was found in the image.');
            }

            // OCR returned an error exit code (e.g. 2 or 3, or file size limits, etc.)
            $errorMsg = $fullResponse['ErrorMessage'][0] ?? 'Unknown OCR API Error';
            Log::error('OCR: OCR.space API returned error code', ['response' => $fullResponse]);

            return $this->errorResponse('OCR.space rejected the image: '.$errorMsg, $fullResponse);

        } catch (\Exception $e) {
            Log::error('OCR: OCR.space external API connection failed!', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Connection to OCR.space failed: '.$e->getMessage());
        }
    }

    protected function errorResponse(string $errorMsg, ?array $fullResponse = null): array
    {
        return [
            'success' => false,
            'text' => null,
            'full_response' => $fullResponse,
            'extracted' => [
                'pharmacy_name' => null,
                'invoice_number' => null,
                'invoice_date' => null,
                'total_amount' => null,
                'subtotal_amount' => null,
                'tax_amount' => null,
                'doctor_name' => null,
                'doctor_nmc' => null,
                'city' => null,
                'items' => [],
            ],
            'error' => $errorMsg,
        ];
    }
}
