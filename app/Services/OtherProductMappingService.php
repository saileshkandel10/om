<?php

namespace App\Services;

use App\Models\IllnessCategory;
use App\Models\OtherProduct;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtherProductMappingService
{
    protected ?Collection $illnessCategoriesCache = null;

    protected ?Collection $knownProductsCache = null;

    protected ?Collection $knownOtherProductsCache = null;

    /**
     * Medical suffix & prefix classification rules.
     */
    protected array $drugClassRules = [
        // Gastric / Acid Peptic / GI
        ['patterns' => ['prazole', 'tidine', 'antacid', 'omeprazole', 'pantoprazole', 'rabeprazole', 'esomeprazole', 'famotidine', 'sucralfate', 'digene', 'gelusil', 'gaviscon', 'domperidone', 'ondansetron'], 'keywords' => ['gastric', 'gastro', 'acid', 'ulcer', 'digestive', 'stomach']],

        // Diabetes / Endocrine
        ['patterns' => ['formin', 'metformin', 'gliptin', 'gliflozin', 'glimepiride', 'gliclazide', 'insulin', 'sitagliptin', 'vildagliptin', 'empagliflozin', 'dapagliflozin'], 'keywords' => ['diabetes', 'diabetic', 'endocrine', 'blood sugar', 'metabolic']],

        // Cardiovascular / Hypertension / Cholesterol
        ['patterns' => ['statin', 'atorvastatin', 'rosuvastatin', 'artan', 'losartan', 'telmisartan', 'valsartan', 'olol', 'atenolol', 'metoprolol', 'amlodipine', 'nifedipine', 'clopidogrel', 'aspirin', 'enalapril', 'ramipril', 'diltiazem'], 'keywords' => ['cardio', 'heart', 'hypertension', 'blood pressure', 'cholesterol', 'vascular']],

        // Pain / Anti-inflammatory / Ortho
        ['patterns' => ['paracetamol', 'acetaminophen', 'ibuprofen', 'aceclofenac', 'diclofenac', 'naproxen', 'tramadol', 'ketorolac', 'mefenamic', 'celecoxib', 'etoricoxib', 'piroxicam'], 'keywords' => ['pain', 'analgesic', 'orthopedic', 'fever', 'inflammation', 'arthritis', 'joint']],

        // Antibiotics / Anti-infective
        ['patterns' => ['cillin', 'amoxicillin', 'ampicillin', 'clav', 'augmentin', 'cef', 'cefixime', 'ceftriaxone', 'cefuroxime', 'floxacin', 'ciprofloxacin', 'levofloxacin', 'azithromycin', 'clarithromycin', 'doxycycline', 'metronidazole', 'tinidazole'], 'keywords' => ['infectious', 'antibiotic', 'bacterial', 'infection', 'antimicrobial']],

        // Respiratory / Allergy
        ['patterns' => ['cetirizine', 'levocetirizine', 'fexofenadine', 'montelukast', 'salbutamol', 'budesonide', 'formoterol', 'cough', 'ambroxol', 'bromhexine', 'dextromethorphan'], 'keywords' => ['respiratory', 'allergy', 'asthma', 'pulmonary', 'cough', 'cold', 'ent', 'lung']],

        // Neurology / Psychiatric
        ['patterns' => ['clonazepam', 'alprazolam', 'diazepam', 'lorazepam', 'sertraline', 'fluoxetine', 'escitalopram', 'pregabalin', 'gabapentin', 'carbamazepine', 'sodium valproate', 'levetiracetam'], 'keywords' => ['neuro', 'psychiatr', 'central nervous', 'brain', 'epilepsy', 'depression', 'anxiety']],

        // Dermatology
        ['patterns' => ['clotrimazole', 'ketoconazole', 'miconazole', 'fluconazole', 'terbinafine', 'betamethasone', 'clobetasol', 'mupirocin', 'fusidic'], 'keywords' => ['derma', 'skin', 'fungal', 'topical', 'eczema']],

        // Vitamins / Supplements / Nephro / Hematology
        ['patterns' => ['vitamin', 'calcium', 'iron', 'folic', 'zinc', 'multivitamin', 'b-complex', 'cyanocobalamin', 'cholecalciferol'], 'keywords' => ['vitamin', 'supplement', 'nutrition', 'hematology', 'anemia', 'bone']],
    ];

    /**
     * Get all active illness categories.
     */
    public function getIllnessCategories(): Collection
    {
        if ($this->illnessCategoriesCache === null) {
            $this->illnessCategoriesCache = IllnessCategory::all();
        }

        return $this->illnessCategoriesCache;
    }

    /**
     * Get known product catalog mappings.
     */
    protected function getKnownProducts(): Collection
    {
        if ($this->knownProductsCache === null) {
            $this->knownProductsCache = Product::whereNotNull('illness_category_id')
                ->select(['id', 'brand_name', 'generic_name', 'illness_category_id'])
                ->get();
        }

        return $this->knownProductsCache;
    }

    /**
     * Get known other products mappings.
     */
    protected function getKnownOtherProducts(): Collection
    {
        if ($this->knownOtherProductsCache === null) {
            $this->knownOtherProductsCache = OtherProduct::where('status', 'active')
                ->whereNotNull('illness_category_id')
                ->select(['id', 'brand_name', 'generic_name', 'illness_category_id'])
                ->get();
        }

        return $this->knownOtherProductsCache;
    }

    /**
     * Clean and normalize a product or drug text string.
     */
    public function cleanDrugText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $cleaned = strtolower(trim($text));

        // Remove dosages like 500mg, 40 mg, 10ml, 1g, 5mcg, 20iu
        $cleaned = preg_replace('/\b\d+(\.\d+)?\s*(mg|mcg|ml|g|gm|iu|%|tablets?|caps?|capsules?|syrup|suspension|drops?|inj|injection|strip|tabs?|tab)\b/i', ' ', $cleaned);

        // Remove standalone numbers
        $cleaned = preg_replace('/\b\d+\b/', ' ', $cleaned);

        // Remove punctuation and special characters
        $cleaned = preg_replace('/[^a-z\s-]/', ' ', $cleaned);

        // Collapse whitespace
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    /**
     * Calculate similarity percentage between two strings.
     */
    public function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === '' || $str2 === '') {
            return 0.0;
        }

        if ($str1 === $str2) {
            return 100.0;
        }

        // Check if one contains the other as full word or major substring
        if (str_contains($str1, $str2) || str_contains($str2, $str1)) {
            $lenRatio = min(strlen($str1), strlen($str2)) / max(strlen($str1), strlen($str2));

            return round(70.0 + ($lenRatio * 25.0), 2);
        }

        // Levenshtein & similar_text hybrid
        similar_text($str1, $str2, $percent);

        $lev = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));
        $levScore = $maxLen > 0 ? (1 - ($lev / $maxLen)) * 100 : 0;

        return round(($percent * 0.6) + ($levScore * 0.4), 2);
    }

    /**
     * Generate top suggested illness categories for a given product or OCR string.
     */
    public function suggestIllnessCategories(?string $brandName, ?string $genericName = null, int $limit = 3): array
    {
        $rawSearch = trim(($brandName ?? '').' '.($genericName ?? ''));
        if (empty($rawSearch)) {
            return [];
        }

        $cleanedBrand = $this->cleanDrugText($brandName);
        $cleanedGeneric = $this->cleanDrugText($genericName);
        $combinedText = trim($cleanedBrand.' '.$cleanedGeneric);

        $categories = $this->getIllnessCategories();
        $scores = []; // category_id => ['score' => float, 'matched_by' => string]

        // 1. Check exact/fuzzy match against Known Products Catalog
        $knownProducts = $this->getKnownProducts();
        foreach ($knownProducts as $product) {
            $catId = $product->illness_category_id;
            if (! $catId) {
                continue;
            }

            // Check brand
            if (! empty($product->brand_name)) {
                $pBrand = $this->cleanDrugText($product->brand_name);
                $sim = $this->calculateSimilarity($cleanedBrand, $pBrand);
                if ($sim >= 60.0) {
                    $this->recordScore($scores, $catId, $sim, "Catalog brand match: {$product->brand_name} ({$sim}%)");
                }
            }

            // Check generic
            if (! empty($product->generic_name)) {
                $pGeneric = $this->cleanDrugText($product->generic_name);
                $simGeneric = $this->calculateSimilarity($cleanedGeneric ?: $cleanedBrand, $pGeneric);
                if ($simGeneric >= 65.0) {
                    $boostedSim = min(99.0, $simGeneric + 5.0);
                    $this->recordScore($scores, $catId, $boostedSim, "Catalog generic match: {$product->generic_name} ({$simGeneric}%)");
                }
            }
        }

        // 2. Check exact/fuzzy match against verified Other Products
        $knownOther = $this->getKnownOtherProducts();
        foreach ($knownOther as $other) {
            $catId = $other->illness_category_id;
            if (! $catId) {
                continue;
            }

            if (! empty($other->brand_name)) {
                $oBrand = $this->cleanDrugText($other->brand_name);
                $sim = $this->calculateSimilarity($cleanedBrand, $oBrand);
                if ($sim >= 60.0) {
                    $this->recordScore($scores, $catId, $sim, "Market brand match: {$other->brand_name} ({$sim}%)");
                }
            }

            if (! empty($other->generic_name)) {
                $oGeneric = $this->cleanDrugText($other->generic_name);
                $simGeneric = $this->calculateSimilarity($cleanedGeneric ?: $cleanedBrand, $oGeneric);
                if ($simGeneric >= 65.0) {
                    $boostedSim = min(99.0, $simGeneric + 5.0);
                    $this->recordScore($scores, $catId, $boostedSim, "Market generic match: {$other->generic_name} ({$simGeneric}%)");
                }
            }
        }

        // 3. Check Pharmacological Drug Class rules
        foreach ($this->drugClassRules as $rule) {
            $patternMatched = false;
            $matchedPattern = '';

            foreach ($rule['patterns'] as $pattern) {
                if (str_contains($combinedText, $pattern) || str_contains(strtolower($rawSearch), $pattern)) {
                    $patternMatched = true;
                    $matchedPattern = $pattern;
                    break;
                }
            }

            if ($patternMatched) {
                // Find matching illness category by rule keywords
                foreach ($categories as $cat) {
                    $catName = strtolower($cat->name);
                    $catDesc = strtolower($cat->description ?? '');
                    foreach ($rule['keywords'] as $kw) {
                        if (str_contains($catName, $kw) || str_contains($catDesc, $kw)) {
                            $this->recordScore($scores, $cat->id, 88.0, "Pharmacological rule '{$matchedPattern}' -> {$cat->name}");
                        }
                    }
                }
            }
        }

        // 4. Direct Illness Category Name & Description keyword matching
        foreach ($categories as $cat) {
            $catName = $this->cleanDrugText($cat->name);
            if (empty($catName)) {
                continue;
            }

            $sim = $this->calculateSimilarity($combinedText, $catName);
            if ($sim >= 50.0) {
                $this->recordScore($scores, $cat->id, $sim, "Illness name similarity: {$cat->name} ({$sim}%)");
            }

            // Word overlap
            $words = explode(' ', $combinedText);
            foreach ($words as $w) {
                if (strlen($w) >= 4 && str_contains(strtolower($cat->name), $w)) {
                    $this->recordScore($scores, $cat->id, 75.0, "Keyword match '{$w}' in {$cat->name}");
                }
            }
        }

        // Format and sort results
        $results = [];
        foreach ($scores as $catId => $data) {
            $category = $categories->firstWhere('id', $catId);
            if ($category) {
                $results[] = [
                    'illness_category_id' => $category->id,
                    'illness_category_name' => $category->name,
                    'confidence_score' => round($data['score'], 1),
                    'matched_by' => $data['matched_by'],
                ];
            }
        }

        usort($results, fn ($a, $b) => $b['confidence_score'] <=> $a['confidence_score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * Record or upgrade a score for an illness category.
     */
    protected function recordScore(array &$scores, int $catId, float $score, string $matchedBy): void
    {
        if (! isset($scores[$catId]) || $score > $scores[$catId]['score']) {
            $scores[$catId] = [
                'score' => min(99.0, $score),
                'matched_by' => $matchedBy,
            ];
        }
    }

    /**
     * Auto scan all unmapped OtherProducts and populate suggestions and confidence scores.
     */
    public function autoScanAndSuggestUnmapped(): array
    {
        $unmapped = OtherProduct::where(function ($q) {
            $q->where('status', 'unmapped')
                ->orWhereNull('illness_category_id')
                ->orWhere('is_verified', false);
        })->get();

        $scanned = 0;
        $suggested = 0;
        $highConfidence = 0;

        foreach ($unmapped as $item) {
            $scanned++;
            $suggestions = $this->suggestIllnessCategories($item->brand_name, $item->generic_name, 1);

            if (! empty($suggestions)) {
                $top = $suggestions[0];
                $item->suggested_illness_category_id = $top['illness_category_id'];
                $item->confidence_score = $top['confidence_score'];
                $item->save();
                $suggested++;

                if ($top['confidence_score'] >= 85.0) {
                    $highConfidence++;
                }
            }
        }

        return [
            'total_scanned' => $scanned,
            'suggested_count' => $suggested,
            'high_confidence_count' => $highConfidence,
        ];
    }

    /**
     * Map a single OtherProduct to an IllnessCategory.
     */
    public function mapSingle(OtherProduct $otherProduct, int $illnessCategoryId, ?int $categoryId = null, ?string $genericName = null, ?string $description = null): bool
    {
        $data = [
            'illness_category_id' => $illnessCategoryId,
            'status' => 'active',
            'is_verified' => true,
        ];

        if ($categoryId) {
            $data['category_id'] = $categoryId;
        }

        if ($genericName) {
            $data['generic_name'] = $genericName;
        }

        if ($description) {
            $data['description'] = $description;
        }

        return $otherProduct->update($data);
    }

    /**
     * Batch map multiple OtherProduct IDs to a single IllnessCategory.
     */
    public function batchMap(array $ids, int $illnessCategoryId, ?int $categoryId = null): int
    {
        $data = [
            'illness_category_id' => $illnessCategoryId,
            'status' => 'active',
            'is_verified' => true,
            'updated_at' => now(),
        ];

        if ($categoryId) {
            $data['category_id'] = $categoryId;
        }

        return OtherProduct::whereIn('id', $ids)->update($data);
    }

    /**
     * Create an unmapped entry from OCR if not already existing.
     */
    public function createUnmappedFromOcr(string $rawText, ?string $brandName = null, ?string $genericName = null): OtherProduct
    {
        $brand = trim($brandName ?: $rawText);
        $generic = $genericName ? trim($genericName) : null;

        // Check if an existing product with same brand already exists
        $existing = OtherProduct::where('brand_name', 'like', $brand)->first();
        if ($existing) {
            return $existing;
        }

        $suggestions = $this->suggestIllnessCategories($brand, $generic, 1);
        $topSuggestion = ! empty($suggestions) ? $suggestions[0] : null;

        return OtherProduct::create([
            'brand_name' => $brand,
            'generic_name' => $generic,
            'source' => 'ocr',
            'raw_ocr_text' => $rawText,
            'status' => 'unmapped',
            'is_verified' => false,
            'suggested_illness_category_id' => $topSuggestion ? $topSuggestion['illness_category_id'] : null,
            'confidence_score' => $topSuggestion ? $topSuggestion['confidence_score'] : null,
        ]);
    }
}
