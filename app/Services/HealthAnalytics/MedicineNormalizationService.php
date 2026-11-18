<?php

namespace App\Services\HealthAnalytics;

use App\Models\Medicine;
use App\Models\Product;

class MedicineNormalizationService
{
    /**
     * Normalize a raw medicine name to canonical Medicine and Product.
     *
     * @return array{matched: bool, medicine: ?Medicine, product: ?Product, generic_name: ?string, therapeutic_class: ?string, normalized_name: string}
     */
    public function normalize(string $rawName): array
    {
        $cleaned = $this->cleanMedicineName($rawName);

        // 1. Direct match on canonical Medicine table (brand_name or generic_name)
        $medicine = Medicine::whereRaw('LOWER(brand_name) = ?', [strtolower($cleaned)])
            ->orWhereRaw('LOWER(generic_name) = ?', [strtolower($cleaned)])
            ->first();

        // 2. Substring search if not found
        if (! $medicine) {
            $words = explode(' ', $cleaned);
            $firstWord = $words[0] ?? $cleaned;

            if (strlen($firstWord) >= 3) {
                $medicine = Medicine::where('brand_name', 'like', "%{$firstWord}%")
                    ->orWhere('generic_name', 'like', "%{$firstWord}%")
                    ->first();
            }
        }

        // 3. Search Product catalog if not matched in medicines table
        $product = null;
        if ($medicine && $medicine->product_id) {
            $product = $medicine->product;
        } else {
            $product = Product::where('brand_name', 'like', "%{$cleaned}%")
                ->orWhere('generic_name', 'like', "%{$cleaned}%")
                ->first();

            if (! $product && isset($firstWord) && strlen($firstWord) >= 3) {
                $product = Product::where('brand_name', 'like', "%{$firstWord}%")
                    ->orWhere('generic_name', 'like', "%{$firstWord}%")
                    ->first();
            }
        }

        // If found in product but not in medicine, create or return canonical medicine
        if (! $medicine && $product) {
            $medicine = Medicine::firstOrCreate(
                ['brand_name' => $product->brand_name],
                [
                    'generic_name' => $product->generic_name ?? $product->brand_name,
                    'strength' => $product->strength,
                    'dosage_form' => $product->dosage_form,
                    'product_id' => $product->id,
                    'status' => 'active',
                ]
            );
        }

        return [
            'matched' => (bool) $medicine,
            'medicine' => $medicine,
            'product' => $product,
            'generic_name' => $medicine?->generic_name ?? $product?->generic_name,
            'therapeutic_class' => $medicine?->therapeutic_class,
            'normalized_name' => $medicine ? $medicine->brand_name : $cleaned,
        ];
    }

    /**
     * Clean dosages, strengths, and formatting from raw names.
     */
    protected function cleanMedicineName(string $name): string
    {
        $name = preg_replace('/(\d+(\.\d+)?\s*(mg|mcg|gm|g|ml|iu|%))/i', '', $name);
        $name = preg_replace('/(tablet|tab|capsule|cap|syrup|syp|inj|injection|suspension|drops|cream|ointment)\b/i', '', $name);
        $name = preg_replace('/[^a-zA-Z0-9\s\-]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }
}
