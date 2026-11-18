<?php

namespace App\Http\Controllers\Admin;

use App\Ai\Agents\ReceiptParser;
use App\Ai\Agents\RewardCalculator;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBillOcr;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\RewardTransaction;
use App\Models\RewardWallet;
use App\Services\PatientIllnessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseBillController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseBill::with(['user', 'pharmacy']);

        if ($request->filled('status')) {
            $query->where('ocr_status', $request->status);
        }

        if ($request->filled('fraud_flag')) {
            $query->where('fraud_flag', $request->boolean('fraud_flag'));
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        $bills = $query->latest()->get();

        return view('admin.purchase-bills.index', compact('bills'));
    }

    public function show(PurchaseBill $purchaseBill): View
    {
        $purchaseBill->load(['user', 'pharmacy', 'items.product', 'uploadedFile', 'rewardTransactions', 'ocrJob']);

        // Directly sync and reflect exact items from the raw OCR plain text
        if ($purchaseBill->ocrJob && ! empty($purchaseBill->ocrJob->raw_text)) {
            $parsed = app(ReceiptParser::class)->parse($purchaseBill->ocrJob->raw_text);

            $updateData = [];
            if (empty($purchaseBill->pharmacy_name) && ! empty($parsed['pharmacy_name'])) {
                $updateData['pharmacy_name'] = $parsed['pharmacy_name'];
            }
            if (empty($purchaseBill->invoice_number) && ! empty($parsed['invoice_number'])) {
                $updateData['invoice_number'] = $parsed['invoice_number'];
            }
            if ($purchaseBill->total_amount <= 0 && ! empty($parsed['total_amount'])) {
                $updateData['total_amount'] = $parsed['total_amount'];
                $updateData['subtotal_amount'] = $parsed['subtotal_amount'] ?? $parsed['total_amount'];
            }
            if (! empty($updateData)) {
                $purchaseBill->update($updateData);
            }

            $json = $purchaseBill->ocrJob->parsed_json ?? [];
            $json['_extracted'] = $parsed;
            $purchaseBill->ocrJob->update(['parsed_json' => $json]);

            if (! empty($parsed['items']) && $purchaseBill->items->count() !== count($parsed['items'])) {
                $purchaseBill->items()->delete();
                foreach ($parsed['items'] as $item) {
                    $matchedProduct = Product::where('brand_name', 'like', '%'.$item['name'].'%')
                        ->orWhere('generic_name', 'like', '%'.$item['name'].'%')
                        ->first();

                    PurchaseBillItem::create([
                        'purchase_bill_id' => $purchaseBill->id,
                        'product_id' => $matchedProduct?->id,
                        'product_name_raw' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['amount'],
                        'batch_no' => $item['batch_no'] ?? null,
                    ]);
                }
                $purchaseBill->load('items.product');
            }
        }

        return view('admin.purchase-bills.show', compact('purchaseBill'));
    }

    public function verify(PurchaseBill $purchaseBill): RedirectResponse
    {
        $purchaseBill->update([
            'validation_status' => 'approved',
            'ocr_status' => 'completed',
        ]);

        if ($purchaseBill->points_awarded <= 0 && $purchaseBill->total_amount > 0) {
            $userRole = 'customer';
            $user = $purchaseBill->user;
            if ($user) {
                if ($user->hasRole('shareholder')) {
                    $userRole = 'shareholder';
                } elseif ($user->hasRole('pharmacy')) {
                    $userRole = 'pharmacy';
                } elseif ($user->hasRole('doctor')) {
                    $userRole = 'doctor';
                }
            }
            $calculator = new RewardCalculator;
            $rewardResult = $calculator->calculate([
                'total_amount' => $purchaseBill->total_amount,
            ], $userRole);

            $purchaseBill->update(['points_awarded' => $rewardResult['total_points']]);

            DB::transaction(function () use ($purchaseBill, $rewardResult) {
                RewardTransaction::create([
                    'user_id' => $purchaseBill->user_id,
                    'purchase_bill_id' => $purchaseBill->id,
                    'points_earned' => $rewardResult['total_points'],
                    'bonus_amount' => $rewardResult['total_bonus'],
                    'royalty_amount' => $rewardResult['total_royalty'],
                    'transaction_type' => 'bill_reward',
                    'status' => 'completed',
                ]);

                $wallet = RewardWallet::firstOrCreate(
                    ['user_id' => $purchaseBill->user_id],
                    ['total_points' => 0, 'total_bonus' => 0, 'total_royalty' => 0],
                );

                $wallet->increment('total_points', $rewardResult['total_points']);
                $wallet->increment('total_bonus', $rewardResult['total_bonus']);
                $wallet->increment('total_royalty', $rewardResult['total_royalty']);
            });
        }

        // Sync Patient Illness categories
        try {
            app(PatientIllnessService::class)->syncFromPurchaseBill($purchaseBill);
        } catch (\Exception $e) {
            // Log silently or let it fail gracefully
        }

        return back()->with('success', __('Purchase bill manually verified and approved successfully.'));
    }

    public function destroy(PurchaseBill $purchaseBill): RedirectResponse
    {
        $purchaseBill->delete();

        return to_route('admin.purchase-bills.index')->with('success', __('Bill deleted successfully.'));
    }

    public function rescanOcr(PurchaseBill $purchaseBill): RedirectResponse
    {
        if (! $purchaseBill->uploaded_file_id) {
            return back()->with('error', __('No uploaded file to rescan.'));
        }

        $purchaseBill->update(['ocr_status' => 'pending']);
        ProcessBillOcr::dispatch($purchaseBill);

        return back()->with('success', __('OCR rescan queued successfully.'));
    }

    public function fraudDetection(Request $request): View
    {
        $query = PurchaseBill::with(['user', 'pharmacy'])
            ->where('fraud_flag', true);

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        $flaggedBills = $query->latest()->get();

        return view('admin.fraud-detection.index', compact('flaggedBills'));
    }
}
