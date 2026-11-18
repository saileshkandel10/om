<?php

use App\Ai\Agents\ReceiptParser;
use App\Ai\Agents\RewardCalculator;
use App\Jobs\ProcessBillOcr;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\UploadedFile;
use App\Models\User;
use App\Services\GeminiOcrService;
use App\Services\OcrSpaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('process bill ocr parses receipt and populates bill table and items via gemini', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $pharmacy = Pharmacy::factory()->create(['store_name' => 'Om City Pharmacy']);
    $product = Product::factory()->create(['brand_name' => 'Paracetamol 500mg Tab']);

    $uploadedFile = UploadedFile::create([
        'user_id' => $user->id,
        'module_name' => 'purchase_bill',
        'file_type' => 'receipt',
        'file_path' => 'receipts/test.jpg',
        'file_size' => 1024,
        'mime_type' => 'image/jpeg',
    ]);

    Storage::disk('public')->put('receipts/test.jpg', 'dummy image content');

    $bill = PurchaseBill::create([
        'user_id' => $user->id,
        'uploaded_file_id' => $uploadedFile->id,
        'ocr_status' => 'pending',
        'validation_status' => 'pending',
    ]);

    $rawOcrText = <<<'TEXT'
    Om City Pharmacy
    Kathmandu, Nepal
    Tax Invoice No: INV-9901
    Date: 2026-05-20

    S.N. Item Name Qty Price Amount
    1. Paracetamol 500mg Tab 10 5.00 50.00
    2. Vitamin D3 60k Cap 4 40.00 160.00

    Grand Total: Rs 210.00
    TEXT;

    $geminiMock = Mockery::mock(GeminiOcrService::class);
    $geminiMock->shouldReceive('processReceipt')
        ->once()
        ->with('receipts/test.jpg')
        ->andReturn([
            'success' => true,
            'text' => $rawOcrText,
            'full_response' => ['candidates' => []],
            'extracted' => (new ReceiptParser)->parse($rawOcrText),
            'error' => null,
        ]);

    $ocrSpaceMock = Mockery::mock(OcrSpaceService::class);

    $job = new ProcessBillOcr($bill);
    $job->handle($geminiMock, $ocrSpaceMock, new ReceiptParser, new RewardCalculator);

    $bill->refresh();

    expect($bill->ocr_status)->toBe('completed')
        ->and($bill->validation_status)->toBe('approved')
        ->and($bill->pharmacy_name)->toBe('Om City Pharmacy')
        ->and($bill->pharmacy_id)->toBe($pharmacy->id)
        ->and($bill->invoice_number)->toBe('INV-9901')
        ->and((float) $bill->total_amount)->toBe(210.00)
        ->and($bill->invoice_date->format('Y-m-d'))->toBe('2026-05-20')
        ->and($bill->items)->toHaveCount(2);

    $item1 = $bill->items->firstWhere('product_name_raw', 'Paracetamol 500mg Tab');
    expect($item1)->not->toBeNull()
        ->and((float) $item1->quantity)->toBe(10.0)
        ->and((float) $item1->unit_price)->toBe(5.00)
        ->and((float) $item1->amount)->toBe(50.00)
        ->and($item1->product_id)->toBe($product->id);

    $item2 = $bill->items->firstWhere('product_name_raw', 'Vitamin D3 60k Cap');
    expect($item2)->not->toBeNull()
        ->and((float) $item2->quantity)->toBe(4.0)
        ->and((float) $item2->unit_price)->toBe(40.00)
        ->and((float) $item2->amount)->toBe(160.00);
});

test('process bill ocr handles ocr failures safely', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $uploadedFile = UploadedFile::create([
        'user_id' => $user->id,
        'module_name' => 'purchase_bill',
        'file_type' => 'receipt',
        'file_path' => 'receipts/corrupt.jpg',
        'file_size' => 1024,
        'mime_type' => 'image/jpeg',
    ]);

    Storage::disk('public')->put('receipts/corrupt.jpg', 'corrupt content');

    $bill = PurchaseBill::create([
        'user_id' => $user->id,
        'uploaded_file_id' => $uploadedFile->id,
        'ocr_status' => 'pending',
        'validation_status' => 'pending',
    ]);

    $geminiMock = Mockery::mock(GeminiOcrService::class);
    $geminiMock->shouldReceive('processReceipt')
        ->once()
        ->with('receipts/corrupt.jpg')
        ->andReturn([
            'success' => false,
            'text' => null,
            'full_response' => null,
            'extracted' => [],
            'error' => 'Image corrupt',
        ]);

    $ocrSpaceMock = Mockery::mock(OcrSpaceService::class);
    $ocrSpaceMock->shouldReceive('processReceipt')
        ->once()
        ->with('receipts/corrupt.jpg')
        ->andReturn([
            'success' => false,
            'text' => null,
            'full_response' => null,
            'extracted' => [],
            'error' => 'OCR.space could not recognize any characters',
        ]);

    $job = new ProcessBillOcr($bill);
    $job->handle($geminiMock, $ocrSpaceMock, new ReceiptParser, new RewardCalculator);

    $bill->refresh();

    expect($bill->ocr_status)->toBe('failed')
        ->and($bill->items)->toBeEmpty();
});
