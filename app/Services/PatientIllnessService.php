<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\IllnessCategory;
use App\Models\OcrJob;
use App\Models\OtherProduct;
use App\Models\PatientIllness;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientIllnessService
{
    /**
     * Synchronize illness categories from a purchase bill and its OCR / item data.
     */
    public function syncFromPurchaseBill(PurchaseBill $bill): int
    {
        if (! $bill->user_id) {
            return 0;
        }

        $synced = 0;
        $detectedAt = $bill->invoice_date ? Carbon::parse($bill->invoice_date) : ($bill->created_at ?? now());

        // 1. Sync from existing purchase bill items with product_id
        $items = $bill->items()->with('product')->get();
        foreach ($items as $item) {
            if ($item->product && $item->product->illness_category_id) {
                PatientIllness::updateOrCreate(
                    [
                        'user_id' => $bill->user_id,
                        'illness_category_id' => $item->product->illness_category_id,
                        'source_type' => 'purchase_bill',
                        'source_id' => $bill->id,
                        'product_id' => $item->product_id,
                    ],
                    [
                        'detected_at' => $detectedAt,
                        'status' => 'active',
                        'diagnosed_by' => $bill->pharmacy?->store_name ?? 'Pharmacy Bill',
                        'notes' => 'Auto-detected from Purchase Bill #'.($bill->invoice_number ?? $bill->id).' (Product: '.$item->product->brand_name.')',
                    ]
                );
                $synced++;
            }
        }

        // 2. Extract OCR raw text and clean it for robust fuzzy matching
        $ocrText = $this->extractOcrTextForBill($bill);
        $cleanNormalizedText = ' '.trim(preg_replace('/[^a-z0-9]/', ' ', strtolower($ocrText))).' ';
        $cleanNormalizedText = preg_replace('/\s+/', ' ', $cleanNormalizedText);

        if (! empty($ocrText)) {
            $categorizedProducts = Product::whereNotNull('illness_category_id')
                ->where('is_active', true)
                ->get();

            foreach ($categorizedProducts as $product) {
                $brandMatched = $this->isMedicineMatch($product->brand_name, $ocrText, $cleanNormalizedText);
                $genericMatched = $this->isMedicineMatch($product->generic_name, $ocrText, $cleanNormalizedText);

                if ($brandMatched || $genericMatched) {
                    PatientIllness::updateOrCreate(
                        [
                            'user_id' => $bill->user_id,
                            'illness_category_id' => $product->illness_category_id,
                            'source_type' => 'purchase_bill',
                            'source_id' => $bill->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'detected_at' => $detectedAt,
                            'status' => 'active',
                            'diagnosed_by' => $bill->pharmacy?->store_name ?? $bill->pharmacy_name ?? 'OCR Detection',
                            'notes' => 'Auto-detected from bill text matching: '.$product->brand_name,
                        ]
                    );
                    $synced++;
                }
            }

            // 3. Scan OCR raw text for verified Other Products (third-party / market brands)
            $otherProducts = OtherProduct::where('status', 'active')
                ->whereNotNull('illness_category_id')
                ->where('is_verified', true)
                ->get();

            foreach ($otherProducts as $otherProduct) {
                $brandMatched = $this->isMedicineMatch($otherProduct->brand_name, $ocrText, $cleanNormalizedText);
                $genericMatched = $this->isMedicineMatch($otherProduct->generic_name, $ocrText, $cleanNormalizedText);

                if ($brandMatched || $genericMatched) {
                    PatientIllness::updateOrCreate(
                        [
                            'user_id' => $bill->user_id,
                            'illness_category_id' => $otherProduct->illness_category_id,
                            'source_type' => 'purchase_bill',
                            'source_id' => $bill->id,
                        ],
                        [
                            'detected_at' => $detectedAt,
                            'status' => 'active',
                            'diagnosed_by' => $bill->pharmacy?->store_name ?? 'Pharmacy Bill',
                            'notes' => 'Auto-detected from Purchase Bill #'.($bill->invoice_number ?? $bill->id).' (Market Product: '.$otherProduct->brand_name.')',
                        ]
                    );
                    $synced++;
                }
            }
        }

        return $synced;
    }

    /**
     * Test if a target medicine string (brand or generic) matches OCR text or item names.
     */
    protected function isMedicineMatch(?string $targetMedicine, string $rawText, string $cleanNormalizedText): bool
    {
        if (blank($targetMedicine)) {
            return false;
        }

        $target = trim($targetMedicine);
        if (empty($target)) {
            return false;
        }

        // 1. Direct case-insensitive substring match
        if (stripos($rawText, $target) !== false) {
            return true;
        }

        // 2. Normalized alphanumeric string match
        $cleanTarget = trim(preg_replace('/[^a-z0-9]/', ' ', strtolower($target)));
        $cleanTarget = preg_replace('/\s+/', ' ', $cleanTarget);
        if (! empty($cleanTarget) && strlen($cleanTarget) >= 3) {
            if (str_contains($cleanNormalizedText, ' '.$cleanTarget.' ')) {
                return true;
            }
        }

        // 3. Clean drug text (strip dosages like 40mg, 500mg, tab, cap)
        try {
            $drugBase = app(OtherProductMappingService::class)->cleanDrugText($target);
            if (! empty($drugBase) && strlen($drugBase) >= 3) {
                if (str_contains($cleanNormalizedText, ' '.$drugBase.' ')) {
                    return true;
                }
            }
        } catch (\Throwable $th) {
            // Ignore if mapping service is unavailable
        }

        return false;
    }

    /**
     * Helper to extract OCR text from a bill.
     */
    protected function extractOcrTextForBill(PurchaseBill $bill): string
    {
        $text = '';
        if ($bill->uploaded_file_id) {
            $ocrJob = OcrJob::where('source_file_id', $bill->uploaded_file_id)->latest()->first();
            if ($ocrJob) {
                $text .= ' '.($ocrJob->raw_text ?? '');
                if (is_array($ocrJob->parsed_json)) {
                    $text .= ' '.json_encode($ocrJob->parsed_json);
                }
            }
        }

        // Also add raw text from bill items
        foreach ($bill->items as $item) {
            $text .= ' '.($item->product_name_raw ?? '');
        }

        return $text;
    }

    /**
     * Synchronize illness categories from a prescription.
     */
    public function syncFromPrescription(Prescription $prescription): int
    {
        $user = null;
        if (! empty($prescription->patient_ref_code)) {
            $rawId = (int) str_ireplace('P-', '', $prescription->patient_ref_code);
            $user = User::where('id', $rawId)
                ->orWhere('mobile', $prescription->patient_ref_code)
                ->first();
        }

        if (! $user) {
            return 0;
        }

        $items = $prescription->items()->with('product')->get();
        $synced = 0;
        $detectedAt = $prescription->prescription_date ? Carbon::parse($prescription->prescription_date) : ($prescription->created_at ?? now());
        $doctorName = $prescription->doctor?->user?->name ? ('Dr. '.$prescription->doctor->user->name) : 'Doctor';

        foreach ($items as $item) {
            $categoryId = $item->illness_category_id ?? $item->product?->illness_category_id;
            if ($categoryId) {
                PatientIllness::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'illness_category_id' => $categoryId,
                        'source_type' => 'prescription',
                        'source_id' => $prescription->id,
                        'product_id' => $item->product_id,
                    ],
                    [
                        'detected_at' => $detectedAt,
                        'status' => 'active',
                        'diagnosed_by' => $doctorName,
                        'notes' => 'Prescribed on '.Carbon::parse($detectedAt)->format('M d, Y').' by '.$doctorName,
                    ]
                );
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Backfill patient illness records from all historical purchase bills and prescriptions.
     */
    public function backfillFromHistoricalData(): array
    {
        $bills = PurchaseBill::with(['items.product', 'pharmacy'])->get();
        $billsCount = 0;

        foreach ($bills as $bill) {
            $billsCount += $this->syncFromPurchaseBill($bill);
        }

        $prescriptions = Prescription::with(['items.product', 'doctor.user'])->get();
        $prescriptionsCount = 0;
        foreach ($prescriptions as $rx) {
            $prescriptionsCount += $this->syncFromPrescription($rx);
        }

        return [
            'synced_bills_records' => $billsCount,
            'synced_prescription_records' => $prescriptionsCount,
            'total_synced' => $billsCount + $prescriptionsCount,
        ];
    }

    /**
     * Handle product exclusion from an illness category.
     */
    public function syncProductExclusion(Product $product): void
    {
        PatientIllness::where('product_id', $product->id)->delete();
    }

    /**
     * Resolve date ranges based on preset or custom inputs.
     */
    public function resolveDateRange(array $filters): array
    {
        $preset = $filters['date_preset'] ?? 'all_time';
        $now = Carbon::now();
        $dateFrom = null;
        $dateTo = null;

        switch ($preset) {
            case 'today':
                $dateFrom = $now->copy()->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $dateFrom = $now->copy()->subDay()->startOfDay();
                $dateTo = $now->copy()->subDay()->endOfDay();
                break;
            case 'this_week':
                $dateFrom = $now->copy()->startOfWeek();
                $dateTo = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $dateFrom = $now->copy()->subMonth()->startOfMonth();
                $dateTo = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $dateFrom = $now->copy()->startOfQuarter();
                $dateTo = $now->copy()->endOfQuarter();
                break;
            case 'this_year':
                $dateFrom = $now->copy()->startOfYear();
                $dateTo = $now->copy()->endOfYear();
                break;
            case 'custom':
                if (! empty($filters['date_from'])) {
                    $dateFrom = Carbon::parse($filters['date_from'])->startOfDay();
                }
                if (! empty($filters['date_to'])) {
                    $dateTo = Carbon::parse($filters['date_to'])->endOfDay();
                }
                break;
            default:
                if (! empty($filters['date_from'])) {
                    $dateFrom = Carbon::parse($filters['date_from'])->startOfDay();
                }
                if (! empty($filters['date_to'])) {
                    $dateTo = Carbon::parse($filters['date_to'])->endOfDay();
                }
                break;
        }

        return [$dateFrom, $dateTo];
    }

    /**
     * Build base query for patient illnesses report based on filters.
     */
    public function buildFilteredQuery(array $filters): Builder
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($filters);
        $dateType = $filters['date_type'] ?? 'diagnosis_date';
        $matchType = strtolower($filters['match_type'] ?? 'any');

        // Extract selected categories (support both illness_category_id and illness_categories array)
        $selectedCategories = [];
        if (! empty($filters['illness_categories'])) {
            $selectedCategories = is_array($filters['illness_categories'])
                ? $filters['illness_categories']
                : explode(',', (string) $filters['illness_categories']);
        } elseif (! empty($filters['illness_category_id'])) {
            $selectedCategories = is_array($filters['illness_category_id'])
                ? $filters['illness_category_id']
                : explode(',', (string) $filters['illness_category_id']);
        }

        $selectedCategories = array_values(array_filter(array_map('intval', $selectedCategories)));

        $query = User::query()
            ->whereHas('patientIllnesses', function (Builder $q) {
                $q->where('status', '!=', 'inactive');
            })
            ->with(['profile', 'illnessCategories']);

        // 1. Illness Category Filtering with ANY vs ALL Logic
        if (! empty($selectedCategories)) {
            if ($matchType === 'all') {
                // ALL logic: patient must have EVERY selected illness category
                foreach ($selectedCategories as $catId) {
                    $query->whereHas('patientIllnesses', function (Builder $q) use ($catId, $dateFrom, $dateTo, $dateType) {
                        $q->where('illness_category_id', $catId)
                            ->where('status', '!=', 'inactive');
                        $this->applyDateFilterToIllness($q, $dateFrom, $dateTo, $dateType);
                    });
                }
            } else {
                // ANY logic (default): patient has ANY of the selected illness categories
                $query->whereHas('patientIllnesses', function (Builder $q) use ($selectedCategories, $dateFrom, $dateTo, $dateType) {
                    $q->whereIn('illness_category_id', $selectedCategories)
                        ->where('status', '!=', 'inactive');
                    $this->applyDateFilterToIllness($q, $dateFrom, $dateTo, $dateType);
                });
            }
        } elseif ($dateFrom || $dateTo) {
            // If no category specified, apply date filter to illnesses or registration/bills
            if ($dateType === 'registration_date') {
                if ($dateFrom) {
                    $query->where('users.created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->where('users.created_at', '<=', $dateTo);
                }
            } elseif ($dateType === 'visit_date') {
                $query->whereHas('purchaseBills', function (Builder $bq) use ($dateFrom, $dateTo) {
                    if ($dateFrom) {
                        $bq->where('invoice_date', '>=', $dateFrom->format('Y-m-d'));
                    }
                    if ($dateTo) {
                        $bq->where('invoice_date', '<=', $dateTo->format('Y-m-d'));
                    }
                });
            } else {
                $query->whereHas('patientIllnesses', function (Builder $q) use ($dateFrom, $dateTo, $dateType) {
                    $this->applyDateFilterToIllness($q, $dateFrom, $dateTo, $dateType);
                });
            }
        }

        // 2. Global search (ID, Code, Name, Phone, Email)
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $rawId = (int) str_ireplace(['P-', 'p-'], '', $search);

            $query->where(function (Builder $q) use ($search, $rawId) {
                if ($rawId > 0) {
                    $q->orWhere('users.id', $rawId);
                }
                $q->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.mobile', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // 3. Gender filter
        if (! empty($filters['gender']) && strtolower($filters['gender']) !== 'all') {
            $gender = strtolower($filters['gender']);
            $query->whereHas('profile', fn ($q) => $q->whereRaw('LOWER(gender) = ?', [$gender]));
        }

        // 4. Age range filter
        if (! empty($filters['age_range'])) {
            $this->applyAgeFilter($query, $filters['age_range']);
        } elseif (! empty($filters['age_from']) || ! empty($filters['age_to'])) {
            $this->applyCustomAgeFilter($query, $filters['age_from'] ?? null, $filters['age_to'] ?? null);
        }

        // 5. Doctor filter
        if (! empty($filters['doctor_id'])) {
            $docId = $filters['doctor_id'];
            $query->where(function (Builder $q) use ($docId) {
                $q->whereHas('patientIllnesses', fn ($piq) => $piq->where('diagnosed_by', 'like', "%{$docId}%"))
                    ->orWhereHas('purchaseBills', fn ($bq) => $bq->where('user_id', $docId));
            });
        } elseif (! empty($filters['doctor'])) {
            $doc = trim((string) $filters['doctor']);
            $query->whereHas('patientIllnesses', fn ($piq) => $piq->where('diagnosed_by', 'like', "%{$doc}%"));
        }

        // 6. Branch / Pharmacy filter
        if (! empty($filters['pharmacy_id']) || ! empty($filters['branch'])) {
            $pharmacyId = $filters['pharmacy_id'] ?? $filters['branch'];
            if (is_numeric($pharmacyId)) {
                $query->whereHas('purchaseBills', fn ($q) => $q->where('pharmacy_id', $pharmacyId));
            } else {
                $query->whereHas('purchaseBills.pharmacy', fn ($q) => $q->where('store_name', 'like', "%{$pharmacyId}%"));
            }
        }

        // 7. Product / Medicine filter
        if (! empty($filters['product_id'])) {
            $productId = $filters['product_id'];
            $query->whereHas('patientIllnesses', fn ($q) => $q->where('product_id', $productId));
        } elseif (! empty($filters['medicine'])) {
            $med = trim((string) $filters['medicine']);
            $query->whereHas('patientIllnesses.product', function (Builder $q) use ($med) {
                $q->where('brand_name', 'like', "%{$med}%")
                    ->orWhere('generic_name', 'like', "%{$med}%");
            });
        }

        // 8. Location filter (province, district, address, pharmacy location)
        if (! empty($filters['location'])) {
            $loc = trim((string) $filters['location']);
            $query->where(function (Builder $q) use ($loc) {
                $q->whereHas('profile', function (Builder $pq) use ($loc) {
                    $pq->where('province', 'like', "%{$loc}%")
                        ->orWhere('district', 'like', "%{$loc}%")
                        ->orWhere('address', 'like', "%{$loc}%");
                })->orWhereHas('purchaseBills.pharmacy', function (Builder $phq) use ($loc) {
                    $phq->where('district', 'like', "%{$loc}%")
                        ->orWhere('address', 'like', "%{$loc}%")
                        ->orWhere('store_name', 'like', "%{$loc}%");
                });
            });
        }

        // 9. Patient Status filter
        if (! empty($filters['status']) || ! empty($filters['patient_status'])) {
            $status = $filters['status'] ?? $filters['patient_status'];
            $query->where('users.status', $status);
        }

        // 10. Bill / Validation Status filter
        if (! empty($filters['bill_status']) || ! empty($filters['validation_status'])) {
            $vStatus = $filters['bill_status'] ?? $filters['validation_status'];
            $query->whereHas('purchaseBills', fn ($q) => $q->where('validation_status', $vStatus));
        }

        return $query;
    }

    /**
     * Apply date constraints to the illness query.
     */
    protected function applyDateFilterToIllness(Builder $q, ?Carbon $dateFrom, ?Carbon $dateTo, string $dateType): void
    {
        $column = match ($dateType) {
            'registration_date' => 'created_at',
            default => 'detected_at',
        };

        if ($dateFrom) {
            $q->where($column, '>=', $dateFrom);
        }
        if ($dateTo) {
            $q->where($column, '<=', $dateTo);
        }
    }

    /**
     * Apply age range constraints on user DOB.
     */
    protected function applyAgeFilter(Builder $query, string $ageRange): void
    {
        $now = Carbon::now();
        $query->whereHas('profile', function (Builder $q) use ($now, $ageRange) {
            match ($ageRange) {
                '<18', '0-18' => $q->where('dob', '>', $now->copy()->subYears(18)),
                '18-35', '19-30' => $q->whereBetween('dob', [$now->copy()->subYears(35), $now->copy()->subYears(18)]),
                '31-45', '36-50' => $q->whereBetween('dob', [$now->copy()->subYears(50), $now->copy()->subYears(31)]),
                '46-60', '51-65' => $q->whereBetween('dob', [$now->copy()->subYears(65), $now->copy()->subYears(46)]),
                '>65', '61+' => $q->where('dob', '<', $now->copy()->subYears(60)),
                default => null,
            };
        });
    }

    /**
     * Apply custom min/max age filter.
     */
    protected function applyCustomAgeFilter(Builder $query, ?int $ageFrom, ?int $ageTo): void
    {
        $now = Carbon::now();
        $query->whereHas('profile', function (Builder $q) use ($now, $ageFrom, $ageTo) {
            if ($ageFrom !== null && $ageTo !== null) {
                $q->whereBetween('dob', [
                    $now->copy()->subYears($ageTo)->startOfDay(),
                    $now->copy()->subYears($ageFrom)->endOfDay(),
                ]);
            } elseif ($ageFrom !== null) {
                $q->where('dob', '<=', $now->copy()->subYears($ageFrom)->endOfDay());
            } elseif ($ageTo !== null) {
                $q->where('dob', '>=', $now->copy()->subYears($ageTo)->startOfDay());
            }
        });
    }

    /**
     * Calculate comprehensive analytics data for the reporting page.
     */
    public function getReportData(array $filters): array
    {
        $this->ensureDataSynchronized();

        $baseQuery = $this->buildFilteredQuery($filters);
        $perPage = max(10, min(2000, (int) ($filters['per_page'] ?? 50)));

        // 1. Total Unique Patients in filtered cohort
        $totalUniquePatients = (clone $baseQuery)->distinct()->count('users.id');

        // Extract patient IDs in cohort
        $cohortPatientIds = (clone $baseQuery)->distinct()->pluck('users.id')->toArray();

        // 2. Total Illness Records (patient-illness relationships for cohort)
        $totalIllnessRecords = empty($cohortPatientIds)
            ? 0
            : PatientIllness::whereIn('user_id', $cohortPatientIds)
                ->where('status', '!=', 'inactive')
                ->count();

        // 3. Category Breakdown & Summary Table
        $categories = IllnessCategory::orderBy('name')->get();
        $categorySummary = [];
        $categoryCountsMap = [];
        $illnessDistributionChart = [];

        foreach ($categories as $cat) {
            $catPatientsQuery = empty($cohortPatientIds)
                ? collect()
                : PatientIllness::where('illness_category_id', $cat->id)
                    ->whereIn('user_id', $cohortPatientIds)
                    ->where('status', '!=', 'inactive')
                    ->with('user.profile')
                    ->get();

            $uniqueInCat = $catPatientsQuery->pluck('user_id')->unique();
            $uniqueCount = $uniqueInCat->count();
            $categoryCountsMap[$cat->name] = $uniqueCount;

            $maleCount = 0;
            $femaleCount = 0;
            $multiIllnessCount = 0;

            if ($uniqueCount > 0) {
                $patientsWithMulti = PatientIllness::whereIn('user_id', $uniqueInCat)
                    ->where('status', '!=', 'inactive')
                    ->select('user_id')
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(DISTINCT illness_category_id) > 1')
                    ->pluck('user_id')
                    ->count();

                $multiIllnessCount = $patientsWithMulti;

                foreach ($catPatientsQuery->unique('user_id') as $record) {
                    $gender = strtolower($record->user?->profile?->gender ?? '');
                    if ($gender === 'male') {
                        $maleCount++;
                    } elseif ($gender === 'female') {
                        $femaleCount++;
                    }
                }
            }

            $percentage = $totalUniquePatients > 0
                ? round(($uniqueCount / $totalUniquePatients) * 100, 1)
                : 0;

            $categorySummary[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description,
                'status' => $cat->status ?? 'active',
                'unique_patients' => $uniqueCount,
                'percentage' => $percentage,
                'male' => $maleCount,
                'female' => $femaleCount,
                'multiple_illness' => $multiIllnessCount,
            ];

            if ($uniqueCount > 0) {
                $illnessDistributionChart[$cat->name] = $uniqueCount;
            }
        }

        // 4. Multi-Illness Distribution (1, 2, 3, 4+ illnesses)
        $patientIllnessCounts = empty($cohortPatientIds)
            ? collect()
            : PatientIllness::whereIn('user_id', $cohortPatientIds)
                ->where('status', '!=', 'inactive')
                ->select('user_id', DB::raw('COUNT(DISTINCT illness_category_id) as total_illnesses'))
                ->groupBy('user_id')
                ->get();

        $distribution = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4+' => 0,
        ];
        $multipleIllnessPatientsCount = 0;

        foreach ($patientIllnessCounts as $pic) {
            $c = (int) $pic->total_illnesses;
            if ($c === 1) {
                $distribution['1']++;
            } elseif ($c === 2) {
                $distribution['2']++;
                $multipleIllnessPatientsCount++;
            } elseif ($c === 3) {
                $distribution['3']++;
                $multipleIllnessPatientsCount++;
            } elseif ($c >= 4) {
                $distribution['4+']++;
                $multipleIllnessPatientsCount++;
            }
        }

        // 5. Top Illness Combinations
        $topCombinations = $this->calculateTopCombinations($cohortPatientIds);

        // 6. Age & Gender Analytics for Charts
        $genderDistribution = $this->calculateGenderDistribution($cohortPatientIds);
        $ageDistribution = $this->calculateAgeDistribution($cohortPatientIds);
        $monthlyTrends = $this->calculateMonthlyTrends($cohortPatientIds, $categories);

        // 7. Paginated Patient Detail Report
        /** @var LengthAwarePaginator $paginatedPatients */
        $paginatedPatients = (clone $baseQuery)
            ->with([
                'profile',
                'illnessCategories',
                'purchaseBills.pharmacy',
                'purchaseBills.items.product',
                'doctor.prescriptions',
            ])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // Selected category name for "Other Illnesses" calculation
        $selectedCatId = ! empty($filters['illness_category_id']) ? (int) $filters['illness_category_id'] : null;
        $patientDetails = $this->formatPatientRows($paginatedPatients, $selectedCatId);

        // Filter metadata
        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->orderBy('name')->get();
        $pharmacies = Pharmacy::where('status', 'active')->orWhere('status', 'approved')->orderBy('store_name')->get();
        $products = Product::where('is_active', true)->orderBy('brand_name')->get();

        return [
            'total_unique_patients' => $totalUniquePatients,
            'total_illness_records' => $totalIllnessRecords,
            'multiple_illness_patients_count' => $multipleIllnessPatientsCount,
            'category_counts' => $categoryCountsMap,
            'category_summary' => $categorySummary,
            'distribution' => $distribution,
            'top_combinations' => $topCombinations,
            'gender_distribution' => $genderDistribution,
            'age_distribution' => $ageDistribution,
            'illness_distribution' => $illnessDistributionChart,
            'monthly_trends' => $monthlyTrends,
            'patients' => $paginatedPatients,
            'patient_rows' => $patientDetails,
            'categories' => $categories,
            'doctors' => $doctors,
            'pharmacies' => $pharmacies,
            'products' => $products,
            'filters' => $filters,
        ];
    }

    /**
     * Compute gender distribution for the cohort.
     */
    protected function calculateGenderDistribution(array $patientIds): array
    {
        if (empty($patientIds)) {
            return ['male' => 0, 'female' => 0, 'other' => 0];
        }

        $records = DB::table('user_profiles')
            ->whereIn('user_id', $patientIds)
            ->select('gender', DB::raw('COUNT(*) as count'))
            ->groupBy('gender')
            ->get();

        $male = 0;
        $female = 0;
        $other = 0;

        foreach ($records as $rec) {
            $g = strtolower($rec->gender ?? '');
            if ($g === 'male') {
                $male += $rec->count;
            } elseif ($g === 'female') {
                $female += $rec->count;
            } else {
                $other += $rec->count;
            }
        }

        return [
            'male' => $male,
            'female' => $female,
            'other' => $other,
        ];
    }

    /**
     * Compute age distribution into medical brackets (0-18, 19-30, 31-45, 46-60, 61+).
     */
    protected function calculateAgeDistribution(array $patientIds): array
    {
        $brackets = [
            '0–18' => 0,
            '19–30' => 0,
            '31–45' => 0,
            '46–60' => 0,
            '61+' => 0,
        ];

        if (empty($patientIds)) {
            return $brackets;
        }

        $dobs = DB::table('user_profiles')
            ->whereIn('user_id', $patientIds)
            ->whereNotNull('dob')
            ->pluck('dob');

        $now = Carbon::now();

        foreach ($dobs as $dob) {
            $age = Carbon::parse($dob)->age;
            if ($age <= 18) {
                $brackets['0–18']++;
            } elseif ($age <= 30) {
                $brackets['19–30']++;
            } elseif ($age <= 45) {
                $brackets['31–45']++;
            } elseif ($age <= 60) {
                $brackets['46–60']++;
            } else {
                $brackets['61+']++;
            }
        }

        return $brackets;
    }

    /**
     * Compute monthly trend of unique patients per category over the last 9-12 months.
     */
    protected function calculateMonthlyTrends(array $patientIds, iterable $categories): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $months[$monthDate->format('Y-m')] = $monthDate->format('M Y');
        }

        $topCategories = collect($categories)->take(5);
        $series = [];

        foreach ($topCategories as $cat) {
            $dataPoints = [];
            foreach (array_keys($months) as $ym) {
                $start = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $ym)->endOfMonth();

                $count = empty($patientIds)
                    ? 0
                    : PatientIllness::where('illness_category_id', $cat->id)
                        ->whereIn('user_id', $patientIds)
                        ->whereBetween('detected_at', [$start, $end])
                        ->distinct()
                        ->count('user_id');

                $dataPoints[] = $count;
            }

            $series[] = [
                'name' => $cat->name,
                'data' => $dataPoints,
            ];
        }

        return [
            'labels' => array_values($months),
            'series' => $series,
        ];
    }

    /**
     * Compute top co-occurring illness combinations per unique patient.
     */
    protected function calculateTopCombinations(array $patientIds): array
    {
        if (empty($patientIds)) {
            return [];
        }

        $records = PatientIllness::whereIn('user_id', $patientIds)
            ->where('status', '!=', 'inactive')
            ->with('illnessCategory')
            ->get()
            ->groupBy('user_id');

        $combinationCounts = [];

        foreach ($records as $userId => $userIllnesses) {
            $catNames = $userIllnesses->pluck('illnessCategory.name')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            if (count($catNames) >= 2) {
                $comboKey = implode(' + ', $catNames);
                $combinationCounts[$comboKey] = ($combinationCounts[$comboKey] ?? 0) + 1;
            }
        }

        arsort($combinationCounts);

        $result = [];
        $rank = 1;
        $totalCombos = array_sum($combinationCounts);

        foreach (array_slice($combinationCounts, 0, 10, true) as $combo => $count) {
            $pct = $totalCombos > 0 ? round(($count / $totalCombos) * 100, 1) : 0;
            $result[] = [
                'rank' => $rank++,
                'combination' => $combo,
                'patients_count' => $count,
                'percentage' => $pct,
            ];
        }

        return $result;
    }

    /**
     * Format patient rows for detailed report presentation.
     */
    protected function formatPatientRows(LengthAwarePaginator $paginator, ?int $selectedCatId = null): array
    {
        $rows = [];

        foreach ($paginator->items() as $user) {
            /** @var User $user */
            $profile = $user->profile;
            $allCategories = $user->illnessCategories->unique('id');
            $bills = $user->purchaseBills;

            $totalAmount = (float) $bills->sum('total_amount');
            $billsCount = $bills->count();

            $firstVisit = $bills->min('invoice_date') ?? $bills->min('created_at');
            $lastVisit = $bills->max('invoice_date') ?? $bills->max('created_at');

            // Collect medicines & pharmacies
            $medicines = collect();
            $pharmaciesList = collect();

            foreach ($bills as $bill) {
                if ($bill->pharmacy) {
                    $pharmaciesList->push($bill->pharmacy->store_name);
                }
                foreach ($bill->items as $item) {
                    if ($item->product) {
                        $medicines->push($item->product->brand_name);
                    } elseif (! empty($item->product_name_raw)) {
                        $medicines->push($item->product_name_raw);
                    }
                }
            }

            // Other illnesses (when filtering by a specific category)
            $otherIllnesses = collect();
            if ($selectedCatId) {
                $otherIllnesses = $allCategories->filter(fn ($c) => $c->id !== $selectedCatId)->pluck('name');
            } else {
                $otherIllnesses = $allCategories->pluck('name');
            }

            // Doctor details
            $doctorName = '—';
            $department = 'General Medicine';
            $latestIllness = $user->patientIllnesses->first();
            if (! empty($latestIllness?->diagnosed_by)) {
                $doctorName = $latestIllness->diagnosed_by;
            } elseif ($user->doctor) {
                $doctorName = 'Dr. '.$user->name;
                $department = $user->doctor->specialty ?? 'General Medicine';
            }

            $age = '—';
            if ($profile?->dob) {
                $age = Carbon::parse($profile->dob)->age.' yrs';
            }

            $locationParts = array_filter([
                $profile?->address,
                $profile?->district,
                $profile?->province,
            ]);

            $paymentStatus = 'Paid';
            if ($bills->where('validation_status', 'pending')->count() > 0) {
                $paymentStatus = 'Pending Verification';
            } elseif ($bills->where('validation_status', 'rejected')->count() > 0) {
                $paymentStatus = 'Rejected / Flagged';
            }

            $rows[] = [
                'id' => $user->id,
                'patient_id' => 'P-'.(1000 + $user->id),
                'user' => $user,
                'name' => $user->name,
                'age' => $age,
                'dob' => $profile?->dob ? Carbon::parse($profile->dob)->format('M d, Y') : '—',
                'gender' => ucfirst($profile?->gender ?? '—'),
                'phone' => $user->mobile ?? '—',
                'email' => $user->email ?? '—',
                'location' => ! empty($locationParts) ? implode(', ', $locationParts) : '—',
                'illness_categories' => $allCategories,
                'other_illnesses' => $otherIllnesses->implode(', ') ?: 'None',
                'illness_count' => $allCategories->count(),
                'doctor' => $doctorName,
                'department' => $department,
                'pharmacy' => $pharmaciesList->unique()->take(2)->implode(', ') ?: '—',
                'visits_count' => $billsCount,
                'first_visit_date' => $firstVisit ? Carbon::parse($firstVisit)->format('M d, Y') : '—',
                'last_visit_date' => $lastVisit ? Carbon::parse($lastVisit)->format('M d, Y') : '—',
                'medicines' => $medicines->unique()->take(5)->implode(', ') ?: '—',
                'total_amount' => $totalAmount,
                'payment_status' => $paymentStatus,
                'status' => $user->status ?? 'active',
            ];
        }

        return $rows;
    }

    /**
     * Ensure any untracked historical bills are synced into patient_illnesses.
     */
    protected function ensureDataSynchronized(): void
    {
        if (PatientIllness::count() === 0 && (PurchaseBill::count() > 0 || Prescription::count() > 0)) {
            $this->backfillFromHistoricalData();
        }
    }
}
