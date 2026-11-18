<?php

namespace App\Services;

use App\Models\PurchaseBill;

class FraudDetectionService
{
    /**
     * Run fraud detection checks on a purchase bill.
     */
    public function analyze(PurchaseBill $bill): array
    {
        $flags = [];

        // Check 1: Duplicate invoice number
        if ($this->checkDuplicateInvoice($bill)) {
            $flags[] = 'duplicate_invoice';
        }

        // Check 2: Suspicious amount patterns
        if ($this->checkSuspiciousAmount($bill)) {
            $flags[] = 'suspicious_amount';
        }

        // Check 3: Multiple bills same day
        if ($this->checkHighFrequency($bill)) {
            $flags[] = 'high_frequency';
        }

        // Check 4: Amount exceeds normal range for pharmacy
        if ($this->checkAmountOutOfRange($bill)) {
            $flags[] = 'amount_out_of_range';
        }

        $isFraud = count($flags) > 0;

        if ($isFraud) {
            $bill->update([
                'fraud_flag' => true,
            ]);
        }

        return [
            'is_fraud' => $isFraud,
            'flags' => $flags,
            'risk_score' => $this->calculateRiskScore($flags),
        ];
    }

    /**
     * Check for duplicate invoice number.
     */
    protected function checkDuplicateInvoice(PurchaseBill $bill): bool
    {
        if (empty($bill->invoice_number)) {
            return false;
        }

        return PurchaseBill::where('invoice_number', $bill->invoice_number)
            ->where('id', '!=', $bill->id)
            ->where('user_id', $bill->user_id)
            ->exists();
    }

    /**
     * Check for suspicious amount patterns (round numbers, exact matches).
     */
    protected function checkSuspiciousAmount(PurchaseBill $bill): bool
    {
        // Flag if total is an exact round number (e.g., 1000.00)
        if ($bill->total_amount > 0 && fmod($bill->total_amount, 100) === 0.0) {
            return true;
        }

        return false;
    }

    /**
     * Check for high submission frequency from the same user.
     */
    protected function checkHighFrequency(PurchaseBill $bill): bool
    {
        $count = PurchaseBill::where('user_id', $bill->user_id)
            ->whereDate('created_at', $bill->created_at?->toDateString() ?? today())
            ->count();

        return $count > 3;
    }

    /**
     * Check if bill amount is unusually high for the pharmacy.
     */
    protected function checkAmountOutOfRange(PurchaseBill $bill): bool
    {
        if (! $bill->pharmacy_id) {
            return false;
        }

        $avgAmount = PurchaseBill::where('pharmacy_id', $bill->pharmacy_id)
            ->where('id', '!=', $bill->id)
            ->average('total_amount');

        if (! $avgAmount || $avgAmount <= 0) {
            return false;
        }

        // Flag if amount is 5x the pharmacy average
        return $bill->total_amount > ($avgAmount * 5);
    }

    /**
     * Calculate risk score based on number and type of flags.
     */
    protected function calculateRiskScore(array $flags): float
    {
        $scores = [
            'duplicate_invoice' => 50,
            'suspicious_amount' => 20,
            'high_frequency' => 30,
            'amount_out_of_range' => 25,
        ];

        $totalScore = 0;
        foreach ($flags as $flag) {
            $totalScore += $scores[$flag] ?? 10;
        }

        return min($totalScore, 100);
    }
}
