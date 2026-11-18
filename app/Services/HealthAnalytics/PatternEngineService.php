<?php

namespace App\Services\HealthAnalytics;

use App\Models\PatientHealthAnalytic;
use App\Models\Prescription;
use App\Models\PurchaseBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PatternEngineService
{
    public function __construct(
        protected MedicineNormalizationService $normalizer
    ) {}

    /**
     * Analyze and generate Health Analytics patterns for a patient based on bills and prescriptions.
     *
     * @return array<PatientHealthAnalytic>
     */
    public function analyzePatient(User $patient): array
    {
        Log::info('PatternEngine: Starting analysis for patient', ['patient_id' => $patient->id]);

        // 1. Gather all bill items & prescriptions for patient
        $medicinesDetected = [];

        // From purchase bills
        $bills = PurchaseBill::where('user_id', $patient->id)
            ->with(['items.product', 'uploadedFile'])
            ->get();

        foreach ($bills as $bill) {
            $billDate = $bill->invoice_date ? Carbon::parse($bill->invoice_date)->toDateString() : $bill->created_at->toDateString();
            $ref = 'Bill #'.($bill->invoice_number ?? $bill->id);

            // Parse items or raw text
            if ($bill->items->isNotEmpty()) {
                foreach ($bill->items as $item) {
                    $raw = $item->medicine_name_raw ?: ($item->product?->brand_name ?? 'Unknown Medicine');
                    $medicinesDetected[] = [
                        'raw_name' => $raw,
                        'source_type' => 'purchase_bill',
                        'source_id' => $bill->id,
                        'source_reference' => $ref,
                        'date' => $billDate,
                    ];
                }
            } else {
                // If OCR job text is present
                $ocrText = $bill->ocrJob?->raw_text ?? '';
                if ($ocrText) {
                    $lines = explode("\n", $ocrText);
                    foreach ($lines as $line) {
                        if (preg_match('/([a-zA-Z\s\-]+(?:\d+mg)?)\s+(?:qty[:\s]*)?(\d+)?/i', $line, $m)) {
                            $medicinesDetected[] = [
                                'raw_name' => trim($m[1]),
                                'source_type' => 'purchase_bill',
                                'source_id' => $bill->id,
                                'source_reference' => $ref,
                                'date' => $billDate,
                            ];
                        }
                    }
                }
            }
        }

        // From prescriptions (if patient has prescriptions)
        $prescriptions = Prescription::where('patient_ref_code', (string) $patient->id)
            ->orWhere('patient_ref_code', 'like', "%{$patient->name}%")
            ->with('items')
            ->get();

        foreach ($prescriptions as $p) {
            $pDate = $p->prescription_date ? Carbon::parse($p->prescription_date)->toDateString() : $p->created_at->toDateString();
            $ref = 'Prescription #P-'.$p->id;

            foreach ($p->items as $pItem) {
                $raw = $pItem->medicine_name_raw ?: 'Prescribed Medicine';
                $medicinesDetected[] = [
                    'raw_name' => $raw,
                    'source_type' => 'prescription',
                    'source_id' => $p->id,
                    'source_reference' => $ref,
                    'date' => $pDate,
                ];
            }
        }

        // 2. Normalize medicines and map to Health Categories
        $categoryMatches = [];

        foreach ($medicinesDetected as $detection) {
            $norm = $this->normalizer->normalize($detection['raw_name']);

            if ($norm['matched'] && $norm['medicine']) {
                $medicine = $norm['medicine'];
                $indications = $medicine->indications()->with('healthCategory')->get();

                foreach ($indications as $ind) {
                    $catId = $ind->health_category_id;
                    $catName = $ind->healthCategory?->name ?? 'General Care';

                    if (! isset($categoryMatches[$catId])) {
                        $categoryMatches[$catId] = [
                            'category_id' => $catId,
                            'category_name' => $catName,
                            'medicines' => [],
                            'records' => [],
                            'dates' => [],
                            'evidences' => [],
                        ];
                    }

                    $categoryMatches[$catId]['medicines'][$medicine->brand_name] = true;
                    $categoryMatches[$catId]['records'][$detection['source_reference']] = true;
                    $categoryMatches[$catId]['dates'][] = $detection['date'];
                    $categoryMatches[$catId]['evidences'][] = [
                        'source_type' => $detection['source_type'],
                        'source_id' => $detection['source_id'],
                        'medicine_name' => $medicine->brand_name,
                        'generic_name' => $medicine->generic_name,
                        'detected_date' => $detection['date'],
                        'source_reference' => $detection['source_reference'],
                        'notes' => "Indicated for {$catName} ({$ind->evidence_level} evidence)",
                    ];
                }
            }
        }

        // 3. Score Association Levels & Save Insights
        $analyticsResults = [];

        foreach ($categoryMatches as $catId => $data) {
            $uniqueMedsCount = count($data['medicines']);
            $uniqueRecordsCount = count($data['records']);
            $dates = array_filter($data['dates']);
            sort($dates);
            $firstDate = $dates[0] ?? now()->toDateString();
            $lastDate = end($dates) ?: $firstDate;

            // Scoring Mechanism (Rule-based)
            $associationLevel = 'Possible Association';
            if ($uniqueMedsCount >= 2 && $uniqueRecordsCount >= 3) {
                $associationLevel = 'Strong Association';
            } elseif ($uniqueMedsCount >= 2 || $uniqueRecordsCount >= 2) {
                $associationLevel = 'Moderate Association';
            } elseif ($uniqueRecordsCount === 1 && $uniqueMedsCount === 1) {
                $associationLevel = 'Weak Association';
            }

            $catName = $data['category_name'];
            $medList = implode(', ', array_keys($data['medicines']));

            $explanation = "Uploaded medication records contain medicines commonly used in {$catName} ({$medList}). "
                ."These medicines appeared across {$uniqueRecordsCount} record(s), resulting in an observed {$associationLevel} with this category. "
                .'This is a medication-based analytical insight and does not confirm a medical diagnosis.';

            $analytic = PatientHealthAnalytic::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'health_category_id' => $catId,
                ],
                [
                    'association_level' => $associationLevel,
                    'supporting_medicines_count' => $uniqueMedsCount,
                    'supporting_records_count' => $uniqueRecordsCount,
                    'first_detected_date' => $firstDate,
                    'latest_detected_date' => $lastDate,
                    'explanation' => $explanation,
                    'status' => 'pending_review',
                ]
            );

            // Re-populate evidence
            $analytic->evidences()->delete();
            foreach ($data['evidences'] as $ev) {
                $analytic->evidences()->create($ev);
            }

            $analyticsResults[] = $analytic;
        }

        return $analyticsResults;
    }

    /**
     * Process an individual purchase bill into health analytics.
     */
    public function processBill(PurchaseBill $bill): void
    {
        $user = $bill->user;
        if ($user) {
            $this->analyzePatient($user);
        }
    }
}
