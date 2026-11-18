<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\ReceiptParser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Bill\StorePurchaseBillRequest;
use App\Http\Resources\Api\V1\PurchaseBillResource;
use App\Jobs\ProcessBillOcr;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseBillController extends Controller
{
    /**
     * List the authenticated user's purchase bills.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PurchaseBill::where('user_id', $request->user()->id)
            ->with(['pharmacy', 'items.product', 'uploadedFile']);

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->string('search')}%");
        }

        if ($request->filled('ocr_status')) {
            $query->where('ocr_status', $request->string('ocr_status'));
        }

        if ($request->filled('validation_status')) {
            $query->where('validation_status', $request->string('validation_status'));
        }

        return PurchaseBillResource::collection(
            $query->latest()->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * Upload a receipt and create a new purchase bill.
     */
    public function store(StorePurchaseBillRequest $request): JsonResponse
    {
        $user = $request->user();

        $file = $request->file('receipt');
        $path = $file->store('receipts/'.$user->id, 'public');

        $uploadedFile = $user->uploadedFiles()->create([
            'module_name' => 'purchase_bill',
            'file_type' => 'receipt',
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $bill = $user->purchaseBills()->create([
            'pharmacy_id' => $request->validated('pharmacy_id'),
            'uploaded_file_id' => $uploadedFile->id,
            'invoice_number' => $request->validated('invoice_number'),
            'invoice_date' => $request->validated('invoice_date') ?? now(),
            'total_amount' => $request->validated('total_amount') ?? 0,
            'subtotal_amount' => $request->validated('total_amount') ?? 0,
            'doctor_name' => $request->validated('doctor_name'),
            'doctor_nmc' => $request->validated('doctor_nmc'),
            'ocr_status' => 'pending',
            'validation_status' => 'pending',
        ]);

        ProcessBillOcr::dispatch($bill);

        $bill->load(['pharmacy', 'uploadedFile']);

        return response()->json([
            'message' => 'Bill submitted successfully. It will be processed shortly.',
            'bill' => new PurchaseBillResource($bill),
        ], 201);
    }

    /**
     * Show a specific purchase bill (owned by the user).
     */
    public function show(Request $request, PurchaseBill $bill): PurchaseBillResource
    {
        if ($bill->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this bill.');
        }

        $bill->load(['pharmacy', 'items.product', 'rewardTransactions', 'uploadedFile', 'ocrJob']);

        if ($bill->ocrJob && ! empty($bill->ocrJob->raw_text) && $bill->items->isEmpty()) {
            $parsed = app(ReceiptParser::class)->parse($bill->ocrJob->raw_text);
            if (! empty($parsed['items'])) {
                foreach ($parsed['items'] as $item) {
                    $matchedProduct = Product::where('brand_name', 'like', '%'.$item['name'].'%')
                        ->orWhere('generic_name', 'like', '%'.$item['name'].'%')
                        ->first();

                    PurchaseBillItem::create([
                        'purchase_bill_id' => $bill->id,
                        'product_id' => $matchedProduct?->id,
                        'product_name_raw' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['amount'],
                        'batch_no' => $item['batch_no'] ?? null,
                    ]);
                }
                $bill->load('items.product');
            }
        }

        return new PurchaseBillResource($bill);
    }
}
