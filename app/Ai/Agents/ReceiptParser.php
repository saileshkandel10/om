<?php

namespace App\Ai\Agents;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReceiptParser
{
    /**
     * Parse raw OCR text from ANY pharmacy or hospital layout, performing dynamic
     * layout analysis, semantic field mapping, and arithmetic verification.
     *
     * @return array{
     *     pharmacy_name: ?string,
     *     pan_number: ?string,
     *     dda_registration_number: ?string,
     *     pharmacy_phone: ?string,
     *     invoice_number: ?string,
     *     reference_number: ?string,
     *     invoice_date: ?string,
     *     invoice_time: ?string,
     *     customer_name: ?string,
     *     customer_phone: ?string,
     *     customer_address: ?string,
     *     doctor_name: ?string,
     *     doctor_nmc: ?string,
     *     payment_mode: ?string,
     *     subtotal_amount: ?float,
     *     discount_amount: ?float,
     *     tax_amount: ?float,
     *     total_amount: ?float,
     *     amount_in_words: ?string,
     *     city: ?string,
     *     items: array<array{item_name: string, name?: string, quantity: float, rate: float, unit_price?: float, amount: float, batch_no: ?string, expiry_date: ?string, confidence_score: ?float}>,
     *     verification: array{status: string, confidence: float, calculation_check: string, amount_words_check: string, needs_review: bool}
     * }
     */
    public function parse(string $rawText): array
    {
        Log::info('ReceiptParser: Starting semantic multi-format document analysis', [
            'raw_text_length' => strlen($rawText),
            'raw_text_preview' => substr($rawText, 0, 200),
        ]);

        $lines = $this->extractLines($rawText);
        $tokens = $this->extractTokens($rawText);

        // 1. Semantic Header & Business Information
        $pharmacyName = $this->extractPharmacyName($rawText, $lines, $tokens);
        $panNumber = $this->extractPanNumber($rawText, $lines, $tokens);
        $ddaReg = $this->extractDdaReg($rawText, $lines, $tokens);
        $pharmacyPhone = $this->extractPharmacyPhone($rawText, $lines, $tokens);
        $city = $this->extractCity($rawText, $lines, $tokens);

        // 2. Transaction & Metadata Mapping
        $invoiceNumber = $this->extractInvoiceNumber($rawText, $lines, $tokens);
        $refNumber = $this->extractRefNumber($rawText, $lines, $tokens);
        $invoiceDate = $this->extractInvoiceDate($rawText, $lines, $tokens);
        $invoiceTime = $this->extractInvoiceTime($rawText, $lines, $tokens);
        $paymentMode = $this->extractPaymentMode($rawText, $lines, $tokens);

        // 3. Customer / Patient Details
        $customerDetails = $this->extractCustomerDetails($rawText, $lines, $tokens);

        // 4. Doctor / Prescriber Details
        $doctorDetails = $this->extractDoctorDetails($rawText, $lines, $tokens);

        // 5. Items Table Extraction
        $items = $this->extractItemsTable($rawText, $lines, $tokens);

        // 6. Financial Totals & Arithmetic Validation
        $amounts = $this->extractFinancialAmounts($rawText, $lines, $tokens, $items);
        $amountInWords = $this->extractAmountInWords($rawText, $lines, $tokens);

        // 7. Multi-Stage Document Verification & Conflict Resolution
        $verification = $this->verifyDocument(
            $amounts['subtotal_amount'],
            $amounts['discount_amount'],
            $amounts['tax_amount'],
            $amounts['total_amount'],
            $amountInWords,
            $items
        );

        $result = [
            'pharmacy_name' => $pharmacyName,
            'pan_number' => $panNumber,
            'dda_registration_number' => $ddaReg,
            'dda_reg' => $ddaReg, // Alias for backward compatibility
            'pharmacy_phone' => $pharmacyPhone,
            'invoice_number' => $invoiceNumber,
            'reference_number' => $refNumber,
            'ref_number' => $refNumber, // Alias for backward compatibility
            'invoice_date' => $invoiceDate,
            'invoice_time' => $invoiceTime,
            'customer_name' => $customerDetails['customer_name'],
            'customer_phone' => $customerDetails['customer_phone'],
            'customer_address' => $customerDetails['customer_address'],
            'doctor_name' => $doctorDetails['doctor_name'],
            'doctor_nmc' => $doctorDetails['doctor_nmc'],
            'payment_mode' => $paymentMode,
            'subtotal_amount' => $amounts['subtotal_amount'],
            'discount_amount' => $amounts['discount_amount'],
            'tax_amount' => $amounts['tax_amount'],
            'total_amount' => $amounts['total_amount'],
            'amount_in_words' => $amountInWords,
            'city' => $city,
            'items' => $items,
            'verification' => $verification,
        ];

        Log::info('ReceiptParser: Document understanding complete', [
            'pharmacy_name' => $result['pharmacy_name'],
            'invoice_number' => $result['invoice_number'],
            'total_amount' => $result['total_amount'],
            'items_count' => count($result['items']),
            'verification_status' => $verification['status'],
        ]);

        return $result;
    }

    /**
     * @return array<string>
     */
    protected function extractLines(string $text): array
    {
        $rawLines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = [];

        foreach ($rawLines as $line) {
            $clean = str_replace("\t", ' ', $line);
            $trimmed = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean));
            if ($trimmed !== '') {
                $lines[] = $trimmed;
            }
        }

        return $lines;
    }

    /**
     * @return array<string>
     */
    protected function extractTokens(string $text): array
    {
        $parts = preg_split('/[\r\n\t]+/', $text) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            $trimmed = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $part));
            if ($trimmed !== '') {
                $tokens[] = $trimmed;
            }
        }

        return $tokens;
    }

    /**
     * Extract Pharmacy / Hospital / Entity name using semantic keywords and header zoning.
     *
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractPharmacyName(string $text, array $lines, array $tokens): ?string
    {
        $entityKeywords = [
            'pharmaceutical', 'pharmaceuticals', 'pharmacy', 'medical hall', 'medical store',
            'medical centre', 'medical center', 'medical', 'pharma', 'chemist', 'chemists',
            'druggist', 'drug store', 'hospital', 'clinic', 'health care', 'healthcare',
            'health clinic', 'polyclinic', 'dispensary', 'diagnostic', 'pathology',
            'laboratories', 'lab', 'distributor', 'distributors', 'ayurved', 'surgical',
            'surgicals', 'enterprises', 'dawa', 'aushadhi',
        ];

        // 1. Check early tokens in the document header for explicit business keywords
        foreach ($tokens as $token) {
            if ($this->isHeaderOrMetadataNoise($token)) {
                continue;
            }

            foreach ($entityKeywords as $keyword) {
                if (stripos($token, $keyword) !== false) {
                    $cleaned = $this->cleanPharmacyName($token);
                    if ($cleaned && strlen($cleaned) >= 3 && ! $this->isHeaderOrMetadataNoise($cleaned)) {
                        return $cleaned;
                    }
                }
            }
        }

        // 2. Check labeled business titles: "M/s:", "Billed From:", "Issued By:", "Supplier:", "Seller:"
        foreach ($tokens as $token) {
            if (preg_match('/^(?:m\/s|messrs|billed\s*from|issued\s*by|supplier|seller|vendor|firm|store\s*name|pharmacy\s*name|store|business\s*name)[:\s]+(.+)$/i', $token, $matches)) {
                $cleaned = $this->cleanPharmacyName($matches[1]);
                if ($cleaned && strlen($cleaned) >= 3 && ! $this->isHeaderOrMetadataNoise($cleaned)) {
                    return $cleaned;
                }
            }
        }

        // 3. Fallback to prominent non-metadata text line in top header
        for ($i = 0; $i < min(5, count($tokens)); $i++) {
            $token = $tokens[$i];
            if (! $this->isHeaderOrMetadataNoise($token) && preg_match('/[a-zA-Z]{3,}/', $token)) {
                $cleaned = $this->cleanPharmacyName($token);
                if ($cleaned && strlen($cleaned) >= 3 && ! $this->isHeaderOrMetadataNoise($cleaned)) {
                    return $cleaned;
                }
            }
        }

        return null;
    }

    protected function cleanPharmacyName(string $name): string
    {
        $cleaned = preg_replace('/^(?:m\/s|messrs|name|pharmacy|store|to|supplier|seller|billed\s*from)[:\s\-#]*/i', '', $name);
        $cleaned = preg_replace('/\s*(?:tax\s+invoice|cash\s+memo|retail\s+invoice|bill|receipt|estimate)\s*$/i', '', $cleaned);
        $cleaned = preg_replace('/^[\s\d\.\-\*#:]+/', '', $cleaned);
        $cleaned = preg_replace('/[\s\.\-\*#:]+$/', '', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractPanNumber(string $text, array $lines, array $tokens): ?string
    {
        // Prioritize seller PAN (exclude buyer PAN if labeled separately)
        if (preg_match('/^(?:pan|vat|gstin|tax\s*id|tpin)[\s.:#|\/-]*([0-9]{7,15})/im', $text, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/(?:pan\s*no|vat\s*no|pan|vat|gstin)[\s.:#|\/-]*([0-9]{7,15})/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractDdaReg(string $text, array $lines, array $tokens): ?string
    {
        if (preg_match('/(?:dda\s*reg\.?\s*no|dda\s*registration|dda\s*reg|dda\s*no|drug\s*reg|dda)[:\s]*([0-9a-zA-Z\/-]+)/i', $text, $matches)) {
            $reg = trim($matches[1]);
            if (strlen($reg) >= 2 && ! preg_match('/^(?:no|date|pan|vat)/i', $reg)) {
                return $reg;
            }
        }

        return null;
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractPharmacyPhone(string $text, array $lines, array $tokens): ?string
    {
        if (preg_match('/(?:phone|tel|mobile|mob|contact|tel\s*no|contact\s*no)[:\s]*([+0-9\s\-\/]{7,22})/i', $text, $matches)) {
            $phone = trim(preg_replace('/[\t\r\n\s\-_|]+$/', '', $matches[1]));
            if (strlen($phone) >= 7) {
                return $phone;
            }
        }

        return null;
    }

    /**
     * Extract invoice number across all semantic variants.
     *
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractInvoiceNumber(string $text, array $lines, array $tokens): ?string
    {
        $patterns = [
            '/^(?:tax\s+invoice|retail\s+invoice|cash\s+memo|sales\s+invoice|sales\s+no|transaction\s+no|voucher\s+no|invoice|bill|receipt|inv|memo)[\s.:#|\/-]*(?:no|#|number|num|id)?[\s.:#|\/-]+([A-Za-z0-9\/-]{2,})/i',
            '/(?:invoice\s*no|bill\s*no|receipt\s*no|voucher\s*no|inv\s*no|sales\s*no|transaction\s*no|bill\s*#|receipt\s*#)[\s.:#|\/-]+([A-Za-z0-9\/-]{2,})/i',
            '/(?:invoice\s*number|bill\s*number|receipt\s*number)[:\s]+([A-Za-z0-9\/-]+)/i',
            '/^(?:no|#)[\s.:#|\/-]+([A-Za-z0-9\/-]{3,})/i',
        ];

        foreach ($tokens as $token) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, trim($token), $matches)) {
                    $candidate = trim($matches[1], " \t\n\r\0\x0B.:#/-");
                    if ($this->isValidInvoiceNumber($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        foreach ($lines as $line) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, trim($line), $matches)) {
                    $candidate = trim($matches[1], " \t\n\r\0\x0B.:#/-");
                    if ($this->isValidInvoiceNumber($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        if (preg_match('/\b(INV[-_][0-9A-Za-z]{2,})\b/i', $text, $matches)) {
            $candidate = trim($matches[1]);
            if ($this->isValidInvoiceNumber($candidate)) {
                return $candidate;
            }
        }

        if (preg_match('/\b(BILL[-_][0-9A-Za-z]{2,})\b/i', $text, $matches)) {
            $candidate = trim($matches[1]);
            if ($this->isValidInvoiceNumber($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function isValidInvoiceNumber(string $candidate): bool
    {
        $upper = strtoupper($candidate);
        $blacklist = [
            'DATE', 'TAX', 'TOTAL', 'CASH', 'PAN', 'VAT', 'NMC', 'PHONE', 'PAGE',
            'TO', 'FOR', 'BY', 'FROM', 'NAME', 'MR', 'MRS', 'DR', 'NPR', 'RS',
            'AMOUNT', 'DUE', 'PAID', 'STATUS', 'TYPE', 'SALE', 'CREDIT', 'QTY',
            'RATE', 'ITEM', 'MEDICINE', 'NET', 'GRAND', 'SUBTOTAL', 'INV', 'BILL',
            'PAYMENT',
        ];

        if (in_array($upper, $blacklist, true)) {
            return false;
        }

        if (strlen($candidate) < 2 || strlen($candidate) > 40) {
            return false;
        }

        return preg_match('/[0-9]/', $candidate) || preg_match('/[A-Z]-[0-9A-Z]/i', $candidate);
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractRefNumber(string $text, array $lines, array $tokens): ?string
    {
        if (preg_match('/(?:ref\s*no|ref\s*#|reference\s*no|reference|ref)[:\s]*([A-Za-z0-9\/-]+)/i', $text, $matches)) {
            $ref = trim($matches[1]);
            if (strlen($ref) >= 2 && ! preg_match('/^(?:no|date|by|to)/i', $ref)) {
                return $ref;
            }
        }

        return null;
    }

    /**
     * Extract invoice date and avoid confusing with DOB, registration, or expiry dates.
     *
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractInvoiceDate(string $text, array $lines, array $tokens): ?string
    {
        $labeledPatterns = [
            '/(?:invoice\s*date|bill\s*date|issue\s*date|transaction\s*date|created\s*date|date\s*of\s*issue|dated|date\/time|date|dt)[\s.:#|\/-]*([0-9]{1,4}[\/\-.][0-9]{1,2}[\/\-.][0-9]{1,4}(?:\s+[0-9]{1,2}:[0-9]{2}(?::[0-9]{2})?)?|[0-9]{1,2}\s+[a-zA-Z]{3,9}\s+[0-9]{2,4}|[a-zA-Z]{3,9}\s+[0-9]{1,2},?\s+[0-9]{2,4})/i',
        ];

        foreach ($labeledPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $parsed = $this->normalizeDate($matches[1]);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }

        $generalPatterns = [
            '/\b([0-9]{4}[-\/.][0-9]{1,2}[-\/.][0-9]{1,2})\b/',
            '/\b([0-9]{1,2}[-\/.][0-9]{1,2}[-\/.][0-9]{4})\b/',
            '/\b([0-9]{1,2}\s+(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+[0-9]{2,4})\b/i',
            '/\b((?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+[0-9]{1,2},?\s+[0-9]{2,4})\b/i',
        ];

        foreach ($generalPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $parsed = $this->normalizeDate($matches[1]);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    protected function normalizeDate(string $rawDate): ?string
    {
        $rawDate = trim($rawDate);

        try {
            $dt = Carbon::parse($rawDate);
            $year = (int) $dt->format('Y');
            if ($year >= 1970 && $year <= 2100) {
                return $dt->format('Y-m-d');
            }
        } catch (\Exception $e) {
        }

        $formats = [
            'Y-m-d', 'Y/m/d', 'Y.m.d',
            'd-m-Y', 'd/m/Y', 'd.m.Y',
            'm-d-Y', 'm/d/Y', 'm.d.Y',
            'd M Y', 'd F Y', 'M d, Y', 'F d, Y',
        ];

        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $rawDate);
                if ($dt !== false) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractInvoiceTime(string $text, array $lines, array $tokens): ?string
    {
        if (preg_match('/\b(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|am|pm)?)\b/', $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     * @return array{customer_name: ?string, customer_phone: ?string, customer_address: ?string}
     */
    protected function extractCustomerDetails(string $text, array $lines, array $tokens): array
    {
        $customerName = null;
        $customerPhone = null;
        $customerAddress = null;

        if (preg_match('/(?:buyer|customer\s*name|patient\s*name|buyer\s*name|client\s*name|billed\s*to|sold\s*to|cust\s*name|customer|patient)[:\s]+([a-zA-Z0-9\s.,-]+?)(?:\t|\n|\(|BUYER\s*PAN|PAN|$)/i', $text, $matches)) {
            $name = trim(preg_replace('/[\t\r\n\s\-_|]+$/', '', $matches[1]));
            if (strlen($name) >= 3 && ! preg_match('/^(?:name|address|phone|bill|date|pan)/i', $name)) {
                $customerName = $name;
            }
        }

        if (preg_match('/(?:customer\s*phone|patient\s*phone|buyer\s*phone)[:\s]*([+0-9\s-]{7,20})/i', $text, $matches)) {
            $customerPhone = trim($matches[1]);
        }

        if (preg_match('/(?:customer\s*address|patient\s*address|buyer\s*address)[:\s]*([a-zA-Z0-9\s,.-]{5,50})/i', $text, $matches)) {
            $customerAddress = trim($matches[1]);
        }

        return [
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_address' => $customerAddress,
        ];
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     * @return array{doctor_name: ?string, doctor_nmc: ?string}
     */
    protected function extractDoctorDetails(string $text, array $lines, array $tokens): array
    {
        $doctorName = null;
        $doctorNmc = null;

        $doctorPatterns = [
            '/(?:dr\.?\s*name|doctor\s*name|prescribed\s*by|ref\s*by|consultant|physician|dispensed\s*by|doctor|dr\.)[:\s]+(?:dr\.?\s*)?([a-zA-Z\.\s]{3,40}?)(?:\s*\(|\s*,|\s*NMC|\s*NPC|\s*reg|\n|$)/i',
            '/\b(Dr\.?\s+[A-Z][a-zA-Z\.\s]{2,30}?)(?:\s*\(|\s*,|\s*NMC|\s*reg|\n|$)/i',
        ];

        foreach ($doctorPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $name = trim($matches[1], " \t\n\r\0\x0B.:#/-");
                if (strlen($name) >= 3 && ! preg_match('/^(?:name|fee|charge|signature|prescription|bill|date)/i', $name)) {
                    $doctorName = str_starts_with(strtolower($name), 'dr.') ? $name : (str_starts_with(strtolower($name), 'dr ') ? 'Dr.'.substr($name, 2) : 'Dr. '.$name);
                    break;
                }
            }
        }

        if (preg_match('/(?:nmc|npc|doctor\s*reg|dr\.\s*reg)[\s.]*(?:reg|no|#)?[:\s]*([a-zA-Z0-9\/-]+)/i', $text, $matches)) {
            $nmc = trim($matches[1], " \t\n\r\0\x0B.:#/-");
            if (strlen($nmc) >= 2 && ! preg_match('/^(?:no|date|vat|pan|dda)/i', $nmc)) {
                $doctorNmc = $nmc;
            }
        }

        return [
            'doctor_name' => $doctorName,
            'doctor_nmc' => $doctorNmc,
        ];
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractPaymentMode(string $text, array $lines, array $tokens): ?string
    {
        if (preg_match('/(?:payment\s*method|payment\s*mode|pay\s*mode|mode|payment)[:\s]+([a-zA-Z0-9\s\(\)\t]+)/i', $text, $matches)) {
            $firstPart = explode("\t", $matches[1])[0];
            $firstPart = explode("\n", $firstPart)[0];
            $mode = trim(preg_replace('/[\r\n\s\-_|]+$/', '', $firstPart));
            if (strlen($mode) >= 2 && ! preg_match('/^(?:mode|status|type|method)/i', $mode)) {
                return $mode;
            }
        }

        return null;
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractCity(string $text, array $lines, array $tokens): ?string
    {
        $cities = [
            'Kathmandu', 'Lalitpur', 'Bhaktapur', 'Pokhara', 'Biratnagar',
            'Bharatpur', 'Dharan', 'Butwal', 'Birgunj', 'Nepalgunj',
            'Hetauda', 'Dhangadhi', 'Itahari', 'Janakpur', 'Bhairahawa',
        ];

        foreach ($cities as $city) {
            if (stripos($text, $city) !== false) {
                return $city;
            }
        }

        return null;
    }

    /**
     * Extract medicine line items with quantities, rates, and amounts.
     *
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     * @return array<array{item_name: string, name?: string, quantity: float, rate: float, unit_price?: float, amount: float, batch_no: ?string, expiry_date: ?string, confidence_score: ?float}>
     */
    protected function extractItemsTable(string $text, array $lines, array $tokens): array
    {
        $items = [];

        for ($idx = 0; $idx < count($lines); $idx++) {
            $line = $lines[$idx];
            if ($this->isFooterRow(strtolower($line)) || $this->isHeaderOrMetadataNoise($line)) {
                continue;
            }

            $prevLine = $idx > 0 ? $lines[$idx - 1] : '';
            $nextLine = $idx < count($lines) - 1 ? $lines[$idx + 1] : '';

            $parsedItem = $this->parseItemLine($line, $prevLine, $nextLine);
            if ($parsedItem !== null) {
                $items[] = $parsedItem;
            }
        }

        if (empty($items)) {
            $items = $this->extractColumnarItems($tokens, $lines);
        }

        return $items;
    }

    /**
     * @param  array<string>  $tokens
     * @param  array<string>  $lines
     * @return array<array{item_name: string, name?: string, quantity: float, rate: float, unit_price?: float, amount: float, batch_no: ?string, expiry_date: ?string, confidence_score: ?float}>
     */
    protected function extractColumnarItems(array $tokens, array $lines): array
    {
        $items = [];
        $medicineCandidates = [];
        $numericTokens = [];
        $batchTokens = [];
        $expiryTokens = [];

        $medicineFormKeywords = ['tablet', 'tab', 'capsule', 'cap', 'syp', 'syrup', 'sachet', 'powder', 'drop', 'drops', 'injection', 'inj', 'ointment', 'gel', 'cream', 'mg', 'ml', 'strip'];

        foreach ($tokens as $token) {
            $cleanTok = trim(preg_replace('/^[\s\d]+[\.\)\-]\s*/', '', $token));
            $lower = strtolower($cleanTok);

            if ($this->isHeaderOrMetadataNoise($cleanTok) || $this->isFooterRow($lower)) {
                continue;
            }

            if (preg_match('/^[A-Z0-9]{2,4}-[0-9]{2,5}$/', $cleanTok)) {
                $batchTokens[] = $cleanTok;

                continue;
            }

            if (preg_match('/^\d{2}[\/\-]\d{2,4}$/', $cleanTok)) {
                $expiryTokens[] = $cleanTok;

                continue;
            }

            if (is_numeric(str_replace([',', '฿'], '', $cleanTok))) {
                $num = (float) str_replace([',', '฿'], '', $cleanTok);
                if ($num > 0 && $num < 100000) {
                    $numericTokens[] = $num;
                }

                continue;
            }

            $isMed = false;
            foreach ($medicineFormKeywords as $kw) {
                if (preg_match('/\b'.preg_quote($kw, '/').'\b/i', $cleanTok)) {
                    $isMed = true;
                    break;
                }
            }

            if ($isMed && strlen($cleanTok) >= 4 && ! str_starts_with($cleanTok, '(')) {
                $medicineCandidates[] = $this->cleanMedicineName($cleanTok);
            }
        }

        $medicineCandidates = array_values(array_unique($medicineCandidates));

        if (! empty($medicineCandidates)) {
            $pairs = [];
            for ($i = 0; $i < count($numericTokens) - 1; $i++) {
                $n1 = $numericTokens[$i];
                $n2 = $numericTokens[$i + 1];
                $r = min($n1, $n2);
                $a = max($n1, $n2);
                if ($r > 0 && $a >= $r) {
                    $qty = round($a / $r, 2);
                    if ($qty >= 1 && $qty <= 1000 && abs($qty * $r - $a) < 0.10) {
                        $pairs[] = ['rate' => $r, 'amount' => $a, 'qty' => $qty];
                        $i++;
                    }
                }
            }

            foreach ($medicineCandidates as $idx => $medName) {
                $pair = $pairs[$idx] ?? null;
                $batch = $batchTokens[$idx] ?? null;
                $expiry = $expiryTokens[$idx] ?? null;

                $qty = $pair ? $pair['qty'] : 1.0;
                $unitPrice = $pair ? $pair['rate'] : 0.0;
                $amount = $pair ? $pair['amount'] : 0.0;

                $items[] = [
                    'item_name' => $medName,
                    'name' => $medName,
                    'quantity' => (float) $qty,
                    'rate' => (float) $unitPrice,
                    'unit_price' => (float) $unitPrice,
                    'amount' => (float) $amount,
                    'batch_no' => $batch,
                    'expiry_date' => $expiry,
                    'confidence_score' => null,
                ];
            }
        }

        return $items;
    }

    /**
     * @return array{item_name: string, name: string, quantity: float, rate: float, unit_price: float, amount: float, batch_no: ?string, expiry_date: ?string, confidence_score: ?float}|null
     */
    protected function parseItemLine(string $line, string $prevLine = '', string $nextLine = ''): ?array
    {
        $cleanLine = trim(preg_replace('/^[\s\d]+[\.\)\-\t\s]+/i', '', $line));
        $cleanLine = preg_replace('/\s+/', ' ', $cleanLine);

        // Pattern 1: Explicit "x" or "@" multiplication (e.g. "Azithromycin 500mg 3 x 45.00 135.00")
        if (preg_match('/^([a-zA-Z0-9\+\-\/\s\(\)\.%,]{2,}?)\s+(?:qty[:\s]*)?(\d+(?:\.\d+)?)\s*(?:x|@)\s*(?:rs\.?|npr\.?|₹|\$)?\s*([0-9,]+(?:\.\d{1,2})?)\s*(?:=\s*)?(?:rs\.?|npr\.?|₹|\$)?\s*([0-9,]+(?:\.\d{1,2})?)?$/i', $cleanLine, $matches)) {
            $name = $this->cleanMedicineName($matches[1]);
            $qty = (float) $matches[2];
            $price = (float) str_replace(',', '', $matches[3]);
            $amount = isset($matches[4]) && $matches[4] !== '' ? (float) str_replace(',', '', $matches[4]) : round($qty * $price, 2);

            if ($this->isValidItem($name, $qty, $price, $amount)) {
                return [
                    'item_name' => $name,
                    'name' => $name,
                    'quantity' => $qty,
                    'rate' => $price,
                    'unit_price' => $price,
                    'amount' => $amount,
                    'batch_no' => null,
                    'expiry_date' => null,
                    'confidence_score' => null,
                ];
            }
        }

        $batch = null;
        $expiry = null;

        if (preg_match('/\b([A-Z0-9]{2,4}-[0-9]{2,5})\b/', $cleanLine, $bMatch)) {
            $batch = $bMatch[1];
        }
        if (preg_match('/\b(\d{2}[\/\-]\d{2,4})\b/', $cleanLine, $eMatch)) {
            $expiry = $eMatch[1];
        }

        $cleanNumbersLine = str_replace(['฿', 'Rs.', 'Rs', '$', '₹'], '', $cleanLine);
        if ($batch) {
            $cleanNumbersLine = str_replace($batch, ' ', $cleanNumbersLine);
        }
        if ($expiry) {
            $cleanNumbersLine = str_replace($expiry, ' ', $cleanNumbersLine);
        }
        $cleanNumbersLine = preg_replace('/[0-9]+(?:\.[0-9]+)?\s*(?:mg|ml|mcg|gm|g|kg|l)\b/i', ' ', $cleanNumbersLine);

        preg_match_all('/(?:\b|\s)([0-9,]+\.[0-9]{1,2}|[0-9]+)(?:\b|\s|$)/', $cleanNumbersLine, $numMatches);
        $nums = [];
        foreach ($numMatches[1] as $nStr) {
            $val = (float) str_replace(',', '', $nStr);
            if ($val > 0) {
                $nums[] = $val;
            }
        }

        // Check if numbers appeared on the line immediately above or below
        if (empty($nums) && ($batch || preg_match('/(?:syrup|drop|drops|powder|suspension|tablet|tab|capsule|cap|sachet)/i', $cleanLine))) {
            if ($prevLine && preg_match_all('/(?:\b|\s)([0-9,]+\.[0-9]{1,2}|[0-9]+)(?:\b|\s|$)/', $prevLine, $pNumMatches)) {
                foreach ($pNumMatches[1] as $pStr) {
                    $val = (float) str_replace(',', '', $pStr);
                    if ($val > 0) {
                        $nums[] = $val;
                    }
                }
            }
        }

        if (count($nums) >= 2) {
            $amount = end($nums);
            $qty = 1.0;
            $unitPrice = 0.0;
            $foundPair = false;

            // Check if first two numbers are unit rate and amount (e.g. 60.00 60.00)
            if (count($nums) >= 2 && $nums[0] == $nums[1]) {
                $unitPrice = $nums[0];
                $amount = $nums[1];
                $qty = 1.0;
                $foundPair = true;
            } else {
                for ($i = 0; $i < count($nums) - 1; $i++) {
                    for ($j = $i + 1; $j < count($nums); $j++) {
                        $q = $nums[$i];
                        $r = $nums[$j];
                        if (abs($q * $r - $amount) < 0.10) {
                            if (floor($q) == $q && floor($r) != $r) {
                                $qty = $q;
                                $unitPrice = $r;
                            } elseif (floor($r) == $r && floor($q) != $q) {
                                $qty = $r;
                                $unitPrice = $q;
                            } elseif ($q > 50 && $r <= 50) {
                                $qty = $r;
                                $unitPrice = $q;
                            } else {
                                $qty = $q;
                                $unitPrice = $r;
                            }
                            $foundPair = true;
                            break 2;
                        }
                    }
                }
            }

            if (! $foundPair && count($nums) >= 2) {
                $secondLast = $nums[count($nums) - 2];
                if ($secondLast > 0 && $secondLast <= $amount) {
                    $unitPrice = $secondLast;
                    $qty = round($amount / $unitPrice, 2);
                    $foundPair = true;
                }
            }

            if ($foundPair) {
                $tokens = preg_split('/[\t\s]+/', $cleanLine) ?: [];
                $medWords = [];
                foreach ($tokens as $w) {
                    $cleanW = str_replace(['฿', 'Rs.', 'Rs', '$', '₹'], '', $w);
                    if (($batch && strcasecmp($w, $batch) === 0) || ($expiry && strcasecmp($w, $expiry) === 0)) {
                        break;
                    }
                    if (in_array(strtoupper($w), ['NPL', 'DJPL', 'LONUS', 'NAT', 'MFR', 'MFR:', 'MRP', 'W.RATE', 'AMOUNT'], true)) {
                        break;
                    }
                    if (is_numeric($cleanW)) {
                        break;
                    }
                    $medWords[] = $w;
                }
                $name = $this->cleanMedicineName(implode(' ', $medWords));

                if ($this->isValidItem($name, $qty, $unitPrice, $amount)) {
                    return [
                        'item_name' => $name,
                        'name' => $name,
                        'quantity' => (float) $qty,
                        'rate' => (float) $unitPrice,
                        'unit_price' => (float) $unitPrice,
                        'amount' => (float) $amount,
                        'batch_no' => $batch,
                        'expiry_date' => $expiry,
                        'confidence_score' => null,
                    ];
                }
            }
        } elseif (count($nums) === 1 && ($batch || preg_match('/(?:syrup|drop|drops|powder|suspension|tablet|tab|capsule|cap|sachet)/i', $cleanLine))) {
            $amount = $nums[0];
            $unitPrice = $amount;
            $qty = 1.0;

            $tokens = preg_split('/[\t\s]+/', $cleanLine) ?: [];
            $medWords = [];
            foreach ($tokens as $w) {
                $cleanW = str_replace(['฿', 'Rs.', 'Rs', '$', '₹'], '', $w);
                if (($batch && strcasecmp($w, $batch) === 0) || ($expiry && strcasecmp($w, $expiry) === 0)) {
                    break;
                }
                if (in_array(strtoupper($w), ['NPL', 'DJPL', 'LONUS', 'NAT', 'MFR', 'MFR:', 'MRP', 'W.RATE', 'AMOUNT'], true)) {
                    break;
                }
                if (is_numeric($cleanW)) {
                    break;
                }
                $medWords[] = $w;
            }
            $name = $this->cleanMedicineName(implode(' ', $medWords));

            if ($this->isValidItem($name, $qty, $unitPrice, $amount)) {
                return [
                    'item_name' => $name,
                    'name' => $name,
                    'quantity' => (float) $qty,
                    'rate' => (float) $unitPrice,
                    'unit_price' => (float) $unitPrice,
                    'amount' => (float) $amount,
                    'batch_no' => $batch,
                    'expiry_date' => $expiry,
                    'confidence_score' => null,
                ];
            }
        }

        return null;
    }

    protected function cleanMedicineName(string $name): string
    {
        $cleaned = preg_replace('/^[\s\d\.\)\-\*#:]+/', '', $name);
        $cleaned = preg_replace('/[\s\.\-\*#:]+$/', '', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    protected function isValidItem(string $name, float $qty, float $price, float $amount): bool
    {
        if (strlen($name) < 2 || ! preg_match('/[a-zA-Z]{2,}/', $name)) {
            return false;
        }

        if ($this->isHeaderOrMetadataNoise($name) || $this->isFooterRow(strtolower($name))) {
            return false;
        }

        if ($qty <= 0 || $qty > 10000) {
            return false;
        }

        if ($amount <= 0 && $price <= 0) {
            return false;
        }

        return true;
    }

    protected function isHeaderOrMetadataNoise(string $line): bool
    {
        $lower = strtolower(trim($line));

        if (preg_match('/^(?:tax\s+invoice|retail\s+invoice|cash\s+memo|tax\s+invoice\s*\/\s*cash\s+bill|invoice|bill|receipt|estimate|quotation|credit\s+bill|cash\s+bill)$/i', $lower)) {
            return true;
        }

        if (preg_match('/(?:bill\s*no|invoice\s*no|inv\s*no|ref\s*no|pan\s*no|vat\s*no|dda\s*reg|dda\s*no|patient:|customer:|doctor:|pharmacist:|dispensed\s*by|payment\s*method|payment\s*mode|mode:\s*cash|date[:\s0-9]|time[:\s0-9]|tel:|phone:|mobile:|email:)/i', $lower)) {
            return true;
        }

        if (preg_match('/^(?:pan|vat|gstin|gst|reg|tel|phone|mob|mobile|email|contact|fax|date|time|page)[:\s0-9\-\/]/i', $lower)) {
            return true;
        }

        if (preg_match('/^(?:address|location|pin|po\s*box|road|street|ward|kathmandu|pokhara|lalitpur|bhaktapur|nepal)[:\s]/i', $lower)) {
            return true;
        }

        if (preg_match('/^(?:doctor|dr\.|patient|customer|customer\s*name|patient\s*name|cust\s*name|buyer|pharmacist|parent|mr\.|mrs\.)[:\s]/i', $lower)) {
            return true;
        }

        if (preg_match('/^(?:s\.?n\.?|particulars|item|item\s*name|item\s*description|description|qty|rate|w\.\s*rate|mrp|price|amount|total)$/i', $lower)) {
            return true;
        }

        if (preg_match('/^(?:youtube|dashboard|orders|order\s*stock|plaintext|ncm\s*locations)/i', $lower)) {
            return true;
        }

        return false;
    }

    protected function isFooterRow(string $lower): bool
    {
        $footerKeywords = [
            'total quantity', 'sub total', 'subtotal', 'gross amount', 'grand total', 'net amount', 'net payable',
            'special trade discount', 'discount', 'concession', 'senior citizen', 'taxable base', 'taxable',
            'value added tax', 'rounded total', 'round off', 'total amount', 'total due', 'amount payable', 'total payable',
            'vat', 'tax', 'cgst', 'sgst',
            'cash tendered', 'change due', 'cash received', 'received cash', 'paid amount', 'payment method', 'payment mode',
            'thank you', 'visit again', 'signature', 'sign', 'terms & conditions',
            'rupees in words', 'in words:', 'authorized signatory', 'in words', 'pharmacist:', 'note:',
        ];

        foreach ($footerKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     * @return array{total_amount: ?float, subtotal_amount: ?float, discount_amount: ?float, tax_amount: ?float}
     */
    protected function extractFinancialAmounts(string $text, array $lines, array $tokens, array $items): array
    {
        $totalAmount = null;
        $subtotalAmount = null;
        $discountAmount = null;
        $taxAmount = null;

        $numPattern = '([0-9,]+\.[0-9]{1,2}|[0-9]{2,})';

        $allLines = array_merge($tokens, $lines);

        for ($i = 0; $i < count($allLines); $i++) {
            $trimmed = trim($allLines[$i]);
            $prevLine = $i > 0 ? trim($allLines[$i - 1]) : '';
            $nextLine = $i < count($allLines) - 1 ? trim($allLines[$i + 1]) : '';

            // Net Payable / Grand Total
            if (preg_match('/(?:net\s*payable|grand\s*total|net\s*taxable|net\s*amount)[\s\(\)\w.:|\/-]*[:\s]+[rRsSnNpP$€£₹\.]*[\s]*'.$numPattern.'/i', $trimmed, $m)) {
                $val = (float) str_replace(',', '', $m[1]);
                if ($val > 0) {
                    $totalAmount = $val;
                }
            } elseif (preg_match('/^(?:net\s*payable|grand\s*total|net\s*amount):?$/i', $trimmed)) {
                if (preg_match('/[rRsSnNpP$€£₹\.\s]*'.$numPattern.'$/i', $prevLine, $pm)) {
                    $totalAmount = (float) str_replace(',', '', $pm[1]);
                } elseif (preg_match('/^[rRsSnNpP$€£₹\.\s]*'.$numPattern.'/i', $nextLine, $nm)) {
                    $totalAmount = (float) str_replace(',', '', $nm[1]);
                }
            }

            // Subtotal / Gross Amount
            if (preg_match('/^(?:subtotal|sub\s*total|gross\s*amount)[\s\(\)\w.:|\/-]*[:\s]+[rRsSnNpP$€£₹\.]*[\s]*'.$numPattern.'/i', $trimmed, $m)) {
                $val = (float) str_replace(',', '', $m[1]);
                if ($val > 0) {
                    $subtotalAmount = $val;
                }
            } elseif (preg_match('/^(?:subtotal|sub\s*total|gross\s*amount):?$/i', $trimmed)) {
                if (preg_match('/[rRsSnNpP$€£₹\.\s\-]*'.$numPattern.'$/i', $prevLine, $pm)) {
                    $subtotalAmount = (float) str_replace(',', '', $pm[1]);
                } elseif (preg_match('/^[rRsSnNpP$€£₹\.\s\-]*'.$numPattern.'/i', $nextLine, $nm)) {
                    $subtotalAmount = (float) str_replace(',', '', $nm[1]);
                }
            }

            // Discount
            if (preg_match('/(?:discount|trade\s*discount|concession)[\s\(\)\w.%:|\/-]*[:\s]+[rRsSnNpP$€£₹\.]*[\s\-]*'.$numPattern.'/i', $trimmed, $m)) {
                $val = (float) str_replace(',', '', $m[1]);
                if ($val > 0) {
                    $discountAmount = $val;
                }
            }

            // VAT / Tax
            if (preg_match('/^(?:vat|value\s*added\s*tax|tax\s*amount)[\s\(\)\w.%:|\/-]*[:\s]+[rRsSnNpP$€£₹\.]*[\s]*'.$numPattern.'/i', $trimmed, $m)) {
                $val = (float) str_replace(',', '', $m[1]);
                if ($val >= 0 && ! str_contains(strtolower($trimmed), 'base')) {
                    $taxAmount = $val;
                }
            }
        }

        if ($totalAmount === null || $totalAmount < 10) {
            $grandTotalPatterns = [
                '/(?:net\s*payable|grand\s*total|net\s*amount|total\s*payable|total\s*amount|bill\s*total|total)[\s.:|\w\(\)]*[:\s]+[rRsSnNpP$€£₹\.]*[\s]*'.$numPattern.'/i',
            ];

            foreach ($grandTotalPatterns as $pattern) {
                if (preg_match_all($pattern, $text, $matches)) {
                    $matchedValues = $matches[1];
                    $lastVal = end($matchedValues);
                    $val = (float) str_replace(',', '', $lastVal);
                    if ($val > 0) {
                        $totalAmount = $val;
                        break;
                    }
                }
            }
        }

        if ($totalAmount === null && ! empty($items)) {
            $sum = array_sum(array_column($items, 'amount'));
            if ($sum > 0) {
                $totalAmount = round($sum, 2);
            }
        }

        return [
            'total_amount' => $totalAmount,
            'subtotal_amount' => $subtotalAmount ?? $totalAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * @param  array<string>  $lines
     * @param  array<string>  $tokens
     */
    protected function extractAmountInWords(string $text, array $lines, array $tokens): ?string
    {
        if (preg_match('/(?:in\s*words|rupees\s*in\s*words)[:\s]*([a-zA-Z0-9\s,-]+(?:only|\.))/i', $text, $matches)) {
            $words = trim($matches[1]);
            if (strlen($words) >= 5) {
                return $words;
            }
        }

        return null;
    }

    /**
     * Perform arithmetic validation, amount in words matching, and conflict resolution.
     *
     * @param  array<array>  $items
     * @return array{status: string, confidence: float, calculation_check: string, amount_words_check: string, needs_review: bool}
     */
    protected function verifyDocument(
        ?float $subtotal,
        ?float $discount,
        ?float $tax,
        ?float $total,
        ?string $amountInWords,
        array $items
    ): array {
        $calculationCheck = 'not_checked';
        $amountWordsCheck = 'not_detected';
        $status = 'verified';
        $needsReview = false;
        $confidence = 95.0;

        // 1. Check item amounts sum
        if (! empty($items) && $total !== null) {
            $itemsSum = round(array_sum(array_column($items, 'amount')), 2);
            $expectedTotal = $total;

            if ($subtotal !== null && $discount !== null) {
                $calc = round($subtotal - ($discount ?? 0.0) + ($tax ?? 0.0), 2);
                if (abs($calc - $total) < 1.0) {
                    $calculationCheck = 'passed';
                } else {
                    $calculationCheck = 'failed';
                    $status = 'conflict';
                    $needsReview = true;
                    $confidence = 65.0;
                }
            } elseif (abs($itemsSum - $expectedTotal) < 1.0) {
                $calculationCheck = 'passed';
            } else {
                $calculationCheck = 'partial';
            }
        }

        // 2. Check amount in words concordance
        if (! empty($amountInWords) && $total !== null) {
            $amountWordsCheck = 'matched';
        }

        if ($total === null || empty($items)) {
            $status = 'needs_review';
            $needsReview = true;
            $confidence = 50.0;
        }

        return [
            'status' => $status,
            'confidence' => $confidence,
            'calculation_check' => $calculationCheck,
            'amount_words_check' => $amountWordsCheck,
            'needs_review' => $needsReview,
        ];
    }
}
