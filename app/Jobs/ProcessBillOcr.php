<?php

namespace App\Jobs;

use App\Ai\Agents\ReceiptParser;
use App\Ai\Agents\RewardCalculator;
use App\Models\OcrJob;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use App\Services\GeminiOcrService;
use App\Services\HealthAnalytics\PatternEngineService;
use App\Services\OcrSpaceService;
use App\Services\PatientIllnessService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBillOcr implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseBill $purchaseBill
    ) {}

    public function handle(
        GeminiOcrService $gemini,
        OcrSpaceService $ocrSpace,
        ReceiptParser $parser,
        RewardCalculator $calculator,
    ): void {
        $uploadedFile = $this->purchaseBill->uploadedFile;

        if (! $uploadedFile || ! $uploadedFile->file_path) {
            Log::warning('Bill OCR skipped: no uploaded file', [
                'bill_id' => $this->purchaseBill->id,
            ]);

            return;
        }

        // Update bill status to processing
        $this->purchaseBill->update(['ocr_status' => 'processing']);

        // Create OCR job record
        $ocrJob = OcrJob::create([
            'module_name' => 'purchase_bill',
            'source_file_id' => $uploadedFile->id,
            'status' => 'processing',
            'engine_name' => 'gemini_vision',
            'started_at' => now(),
        ]);

        try {
            // Step 1: Send to Gemini Vision OCR (primary) or OCR.space (fallback)
            $result = $gemini->processReceipt($uploadedFile->file_path);
            $engineName = 'gemini_vision';

            if (! ($result['success'] ?? false)) {
                Log::info('Gemini OCR fallback to OCR.space', ['error' => $result['error'] ?? '']);
                $result = $ocrSpace->processReceipt($uploadedFile->file_path);
                $engineName = 'ocr_space';
            }

            if (! ($result['success'] ?? false)) {
                $errorMessage = $result['error'] ?? 'OCR extraction failed or no legible text found.';
                Log::warning('Bill OCR: extraction failed', [
                    'bill_id' => $this->purchaseBill->id,
                    'error' => $errorMessage,
                ]);

                $ocrJob->update([
                    'status' => 'failed',
                    'engine_name' => $engineName,
                    'raw_text' => $errorMessage,
                    'parsed_json' => $result['full_response'] ?? null,
                    'completed_at' => now(),
                ]);

                $this->purchaseBill->update([
                    'ocr_status' => 'failed',
                ]);

                return;
            }

            $rawText = $result['text'] ?? '';
            $parsedData = ! empty($result['extracted']) && ! empty($result['extracted']['pharmacy_name'])
                ? $result['extracted']
                : $parser->parse($rawText);

            $parsedJson = $result['full_response'] ?? [];
            $parsedJson['_extracted'] = $parsedData;

            // Step 2: Strictly prioritize user-entered values over OCR estimation (no invention of random numbers)
            $invoiceNumber = $this->purchaseBill->invoice_number ?: ($parsedData['invoice_number'] ?? null);
            $totalAmount = (float) ($this->purchaseBill->total_amount > 0 ? $this->purchaseBill->total_amount : ($parsedData['total_amount'] ?? 0.00));
            $doctorName = $this->purchaseBill->doctor_name ?: ($parsedData['doctor_name'] ?? null);
            $doctorNmc = $this->purchaseBill->doctor_nmc ?: ($parsedData['doctor_nmc'] ?? null);
            $pharmacyName = $this->purchaseBill->pharmacy_name ?: ($parsedData['pharmacy_name'] ?? null);

            $pharmacyId = $this->purchaseBill->pharmacy_id;
            if (! $pharmacyId && $pharmacyName) {
                $matchedPharmacy = Pharmacy::where('store_name', 'like', '%'.$pharmacyName.'%')->first();
                if ($matchedPharmacy) {
                    $pharmacyId = $matchedPharmacy->id;
                }
            }

            // Step 3: Check for duplicate bill / Fraud Detection
            $fraudFlag = false;
            $fraudReason = null;

            if ($invoiceNumber && $totalAmount > 0) {
                $existingBill = PurchaseBill::where('invoice_number', $invoiceNumber)
                    ->where('id', '!=', $this->purchaseBill->id)
                    ->where('total_amount', $totalAmount)
                    ->first();
                if ($existingBill) {
                    $fraudFlag = true;
                    $fraudReason = 'Duplicate invoice number and amount detected (Matches Bill #'.$existingBill->id.')';
                }
            }

            if ($totalAmount > 100000.00) {
                $fraudFlag = true;
                $fraudReason = 'Abnormal bill total amount (Rs '.$totalAmount.')';
            }

            if ($fraudFlag) {
                Log::warning('ProcessBillOcr: Fraud rule triggered', [
                    'bill_id' => $this->purchaseBill->id,
                    'reason' => $fraudReason,
                ]);

                $ocrJob->update([
                    'status' => 'completed',
                    'raw_text' => $rawText,
                    'parsed_json' => $parsedJson,
                    'completed_at' => now(),
                ]);

                $this->purchaseBill->update([
                    'ocr_status' => 'completed',
                    'validation_status' => 'rejected',
                    'fraud_flag' => true,
                    'duplicate_flag' => str_contains($fraudReason, 'Duplicate'),
                    'pharmacy_id' => $pharmacyId,
                    'pharmacy_name' => $pharmacyName,
                    'invoice_number' => $invoiceNumber,
                    'total_amount' => $totalAmount,
                    'subtotal_amount' => $totalAmount,
                    'doctor_name' => $doctorName,
                    'doctor_nmc' => $doctorNmc,
                ]);

                return;
            }

            // Step 4: Determine user role for reward calculation
            $userRole = 'customer';
            $user = $this->purchaseBill->user;
            if ($user) {
                if ($user->hasRole('shareholder')) {
                    $userRole = 'shareholder';
                } elseif ($user->hasRole('pharmacy')) {
                    $userRole = 'pharmacy';
                } elseif ($user->hasRole('doctor')) {
                    $userRole = 'doctor';
                }
            }

            // Step 5: Calculate rewards based on exact total amount
            $parsedData['total_amount'] = $totalAmount;
            $rewardResult = $calculator->calculate($parsedData, $userRole);
            $parsedJson['_rewards'] = $rewardResult;

            // Update OCR job with results
            $ocrJob->update([
                'status' => 'completed',
                'raw_text' => $rawText,
                'parsed_json' => $parsedJson,
                'completed_at' => now(),
            ]);

            // Step 6: Admin Rule: Bills >= Rs 5,000 require manual admin verification; below Rs 5,000 auto-verified by OCR
            $validationStatus = ($totalAmount >= 5000.00) ? 'pending' : 'approved';

            $updateData = [
                'ocr_status' => 'completed',
                'validation_status' => $validationStatus,
                'pharmacy_id' => $pharmacyId,
                'pharmacy_name' => $pharmacyName,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'subtotal_amount' => $parsedData['subtotal_amount'] ?? $totalAmount,
                'tax_amount' => $parsedData['tax_amount'] ?? null,
                'doctor_name' => $doctorName,
                'doctor_nmc' => $doctorNmc,
            ];

            if (! empty($parsedData['invoice_date'])) {
                try {
                    $updateData['invoice_date'] = Carbon::parse($parsedData['invoice_date']);
                } catch (\Exception $e) {
                }
            }

            $this->purchaseBill->update($updateData);

            // Step 7: Populate Purchase Bill Items table from extracted items
            $this->purchaseBill->items()->delete();
            if (! empty($parsedData['items'])) {
                foreach ($parsedData['items'] as $item) {
                    $matchedProduct = Product::where('brand_name', 'like', '%'.$item['name'].'%')
                        ->orWhere('generic_name', 'like', '%'.$item['name'].'%')
                        ->first();

                    PurchaseBillItem::create([
                        'purchase_bill_id' => $this->purchaseBill->id,
                        'product_id' => $matchedProduct?->id,
                        'product_name_raw' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['amount'],
                        'batch_no' => $item['batch_no'] ?? null,
                        'confidence_score' => $item['confidence_score'] ?? null,
                    ]);
                }
            }

            // Step 8: Create reward transactions and update wallet if auto-approved
            if ($validationStatus === 'approved' && $rewardResult['total_points'] > 0) {
                DB::transaction(function () use ($rewardResult) {
                    RewardTransaction::create([
                        'user_id' => $this->purchaseBill->user_id,
                        'purchase_bill_id' => $this->purchaseBill->id,
                        'points_earned' => $rewardResult['total_points'],
                        'bonus_amount' => $rewardResult['total_bonus'],
                        'royalty_amount' => $rewardResult['total_royalty'],
                        'transaction_type' => 'bill_reward',
                        'status' => 'completed',
                    ]);

                    $wallet = RewardWallet::firstOrCreate(
                        ['user_id' => $this->purchaseBill->user_id],
                        ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0],
                    );

                    $wallet->increment('total_points', $rewardResult['total_points']);
                    $wallet->increment('total_bonus', $rewardResult['total_bonus']);
                    $wallet->increment('total_royalty', $rewardResult['total_royalty']);
                });
            }

            // Trigger Health Analytics Pattern Engine
            try {
                app(PatternEngineService::class)->processBill($this->purchaseBill);
            } catch (\Exception $e) {
                Log::warning('Health Analytics processBill skipped: '.$e->getMessage());
            }

            // Sync Patient Illness categories from bill items, OCR text, and market products
            try {
                app(PatientIllnessService::class)->syncFromPurchaseBill($this->purchaseBill);
            } catch (\Exception $e) {
                Log::warning('Patient Illness syncFromPurchaseBill skipped: '.$e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Bill OCR processing failed', [
                'bill_id' => $this->purchaseBill->id,
                'error' => $e->getMessage(),
            ]);

            $this->purchaseBill->update(['ocr_status' => 'failed']);
            $ocrJob->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }
    }
}
