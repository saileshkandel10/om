<?php

namespace App\Services;

use App\Models\District;
use App\Models\OcrJob;
use App\Models\Pharmacy;
use App\Models\PurchaseBill;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LocationNormalizationService
{
    protected ?Collection $districtsCache = null;

    /**
     * Get all active districts with provinces.
     */
    public function getDistricts(): Collection
    {
        if ($this->districtsCache === null) {
            $this->districtsCache = District::with('province')->where('status', 'active')->orderBy('name')->get();
        }

        return $this->districtsCache;
    }

    /**
     * Match a free-text location string to a known District.
     */
    public function matchDistrict(?string $locationText): ?District
    {
        if (empty($locationText)) {
            return null;
        }

        $text = trim((string) $locationText);
        $districts = $this->getDistricts();

        // 1. Exact case-insensitive match on name
        $exact = $districts->first(fn ($d) => strcasecmp($d->name, $text) === 0);
        if ($exact) {
            return $exact;
        }

        // 2. Clean common words like "District", "Jilla", "Nepal", commas
        $cleaned = preg_replace('/\b(district|jilla|nepal|prov|province|no|zone|bazar|chowk|road|marg)\b/i', '', $text);
        $cleaned = trim(preg_replace('/[^a-zA-Z\s]/', ' ', $cleaned));

        if (! empty($cleaned)) {
            $exactCleaned = $districts->first(fn ($d) => strcasecmp($d->name, $cleaned) === 0);
            if ($exactCleaned) {
                return $exactCleaned;
            }
        }

        // 3. Word boundary substring match for full district name
        foreach ($districts as $district) {
            $districtName = $district->name;
            $aliases = [$districtName];
            if ($districtName === 'Kavrepalanchok') {
                $aliases[] = 'Kavre';
            } elseif ($districtName === 'Nawalpur') {
                $aliases[] = 'Nawalparasi East';
            } elseif ($districtName === 'Parasi') {
                $aliases[] = 'Nawalparasi West';
            } elseif ($districtName === 'Sindhupalchok') {
                $aliases[] = 'Sindhupalchowk';
            } elseif ($districtName === 'Makwanpur') {
                $aliases[] = 'Makawanpur';
            }

            foreach ($aliases as $alias) {
                if (preg_match('/\b'.preg_quote($alias, '/').'\b/i', $text)) {
                    return $district;
                }
            }
        }

        return null;
    }

    /**
     * Auto-normalize all existing user profiles, purchase bills, and pharmacies.
     */
    public function normalizeAllRecords(): array
    {
        $profilesUpdated = 0;
        $billsUpdated = 0;
        $pharmaciesUpdated = 0;

        // 1. User Profiles
        $profiles = UserProfile::whereNull('district_id')->get();
        foreach ($profiles as $profile) {
            $matched = $this->matchDistrict($profile->district)
                ?? $this->matchDistrict($profile->address)
                ?? $this->matchDistrict($profile->province);

            if ($matched) {
                $profile->update([
                    'district_id' => $matched->id,
                    'province_id' => $matched->province_id,
                ]);
                $profilesUpdated++;
            }
        }

        // 2. Pharmacies
        $pharmacies = Pharmacy::whereNull('district_id')->get();
        foreach ($pharmacies as $pharmacy) {
            $matched = $this->matchDistrict($pharmacy->district)
                ?? $this->matchDistrict($pharmacy->address);

            if ($matched) {
                $pharmacy->update([
                    'district_id' => $matched->id,
                    'province_id' => $matched->province_id,
                ]);
                $pharmaciesUpdated++;
            }
        }

        // 3. Purchase Bills
        $bills = PurchaseBill::with(['pharmacy', 'user.profile'])
            ->whereNull('district_id')
            ->where('location_status', '!=', 'skipped')
            ->get();

        foreach ($bills as $bill) {
            $matched = null;

            if ($bill->pharmacy && $bill->pharmacy->district_id) {
                $matched = District::find($bill->pharmacy->district_id);
            } elseif ($bill->pharmacy) {
                $matched = $this->matchDistrict($bill->pharmacy->district) ?? $this->matchDistrict($bill->pharmacy->address);
            }

            if (! $matched && $bill->user?->profile) {
                if ($bill->user->profile->district_id) {
                    $matched = District::find($bill->user->profile->district_id);
                } else {
                    $matched = $this->matchDistrict($bill->user->profile->district) ?? $this->matchDistrict($bill->user->profile->address);
                }
            }

            if ($matched) {
                $bill->update([
                    'district_id' => $matched->id,
                    'province_id' => $matched->province_id,
                    'location_status' => 'mapped',
                ]);
                $billsUpdated++;
            }
        }

        return [
            'profiles_updated' => $profilesUpdated,
            'pharmacies_updated' => $pharmaciesUpdated,
            'bills_updated' => $billsUpdated,
            'total_updated' => $profilesUpdated + $pharmaciesUpdated + $billsUpdated,
        ];
    }

    /**
     * Get unmapped bills with full contextual data for individual or bulk review.
     */
    public function getUnmappedBillsQuery(array $filters = []): Builder
    {
        $status = $filters['status'] ?? 'pending'; // 'pending', 'skipped', 'all'

        $query = PurchaseBill::with(['user.profile', 'pharmacy', 'items.product', 'uploadedFile']);

        if ($status === 'skipped') {
            $query->where('location_status', 'skipped');
        } elseif ($status === 'all') {
            $query->whereNull('district_id');
        } else {
            // Default pending
            $query->whereNull('district_id')->where(function ($q) {
                $q->whereNull('location_status')->orWhere('location_status', '!=', 'skipped');
            });
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"))
                    ->orWhereHas('pharmacy', fn ($pq) => $pq->where('store_name', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%"));
            });
        }

        return $query->latest('id');
    }

    /**
     * Format a bill record for unmapped review modal and table.
     */
    public function formatUnmappedBillRow(PurchaseBill $bill): array
    {
        $rawAddresses = [];
        $sources = [];

        if ($bill->pharmacy?->address) {
            $rawAddresses[] = $bill->pharmacy->address;
            $sources[] = 'Pharmacy: '.$bill->pharmacy->store_name;
        }
        if ($bill->pharmacy?->district) {
            $rawAddresses[] = $bill->pharmacy->district;
        }
        if ($bill->user?->profile?->address) {
            $rawAddresses[] = $bill->user->profile->address;
            $sources[] = 'Customer Profile';
        }
        if ($bill->user?->profile?->district) {
            $rawAddresses[] = $bill->user->profile->district;
        }

        // Try extracting from OCR parsed job if available
        $ocrSnippet = null;
        if ($bill->uploaded_file_id) {
            $ocrJob = OcrJob::where('source_file_id', $bill->uploaded_file_id)->latest()->first();
            if ($ocrJob && ! empty($ocrJob->raw_text)) {
                $ocrSnippet = Str::limit($ocrJob->raw_text, 150);
                $rawAddresses[] = $ocrSnippet;
            }
        }

        $combinedRaw = implode(' · ', array_unique(array_filter($rawAddresses)));
        $suggested = null;

        foreach ($rawAddresses as $addr) {
            $m = $this->matchDistrict($addr);
            if ($m) {
                $suggested = $m;
                break;
            }
        }

        return [
            'id' => $bill->id,
            'bill' => $bill,
            'invoice_number' => $bill->invoice_number ?? 'BILL-'.$bill->id,
            'invoice_date' => $bill->invoice_date ? $bill->invoice_date->format('M d, Y') : $bill->created_at->format('M d, Y'),
            'total_amount' => (float) $bill->total_amount,
            'customer_name' => $bill->user?->name ?? 'Unknown Customer',
            'customer_phone' => $bill->user?->mobile ?? '—',
            'pharmacy_name' => $bill->pharmacy?->store_name ?? 'Direct / No Pharmacy',
            'raw_address' => $combinedRaw ?: 'No address text available in bill or profile',
            'sources' => implode(', ', array_unique($sources)) ?: 'Bill Header',
            'ocr_snippet' => $ocrSnippet,
            'suggested_district' => $suggested,
            'suggested_district_id' => $suggested?->id,
            'suggested_district_name' => $suggested?->name,
            'suggested_province_name' => $suggested?->province?->name,
            'location_status' => $bill->location_status ?? 'pending',
            'location_notes' => $bill->location_notes,
            'items_count' => $bill->items->count(),
        ];
    }

    /**
     * Map a single bill to a district.
     */
    public function mapSingleBill(PurchaseBill $bill, District $district, ?string $notes = null): bool
    {
        return $bill->update([
            'district_id' => $district->id,
            'province_id' => $district->province_id,
            'location_status' => 'mapped',
            'location_notes' => $notes ?? 'Manually mapped by administrator',
        ]);
    }

    /**
     * Skip/Ignore a single bill from unmapped review.
     */
    public function skipSingleBill(PurchaseBill $bill, ?string $reason = null): bool
    {
        return $bill->update([
            'location_status' => 'skipped',
            'location_notes' => $reason ?? 'Skipped/ignored by administrator',
        ]);
    }

    /**
     * Bulk map selected bills to a district.
     */
    public function bulkMapBills(array $billIds, int $districtId, ?string $notes = null): int
    {
        $district = District::find($districtId);
        if (! $district || empty($billIds)) {
            return 0;
        }

        return PurchaseBill::whereIn('id', $billIds)->update([
            'district_id' => $district->id,
            'province_id' => $district->province_id,
            'location_status' => 'mapped',
            'location_notes' => $notes ?? 'Bulk mapped to '.$district->name.' by administrator',
        ]);
    }

    /**
     * Bulk skip selected bills.
     */
    public function bulkSkipBills(array $billIds, ?string $reason = null): int
    {
        if (empty($billIds)) {
            return 0;
        }

        return PurchaseBill::whereIn('id', $billIds)->update([
            'location_status' => 'skipped',
            'location_notes' => $reason ?? 'Bulk skipped by administrator',
        ]);
    }

    /**
     * Bulk auto-map all selected (or all pending) bills that have a confident suggestion.
     */
    public function bulkAutoMapSuggested(array $billIds = []): int
    {
        $query = PurchaseBill::with(['pharmacy', 'user.profile'])->whereNull('district_id');
        if (! empty($billIds)) {
            $query->whereIn('id', $billIds);
        } else {
            $query->where(fn ($q) => $q->whereNull('location_status')->orWhere('location_status', '!=', 'skipped'));
        }

        $bills = $query->get();
        $mapped = 0;

        foreach ($bills as $bill) {
            $matched = null;
            if ($bill->pharmacy) {
                $matched = $this->matchDistrict($bill->pharmacy->district) ?? $this->matchDistrict($bill->pharmacy->address);
            }
            if (! $matched && $bill->user?->profile) {
                $matched = $this->matchDistrict($bill->user->profile->district) ?? $this->matchDistrict($bill->user->profile->address);
            }

            if ($matched) {
                $bill->update([
                    'district_id' => $matched->id,
                    'province_id' => $matched->province_id,
                    'location_status' => 'mapped',
                    'location_notes' => 'Auto-mapped based on match: '.$matched->name,
                ]);
                $mapped++;
            }
        }

        return $mapped;
    }

    /**
     * Get statistics for unmapped bills dashboard.
     */
    public function getUnmappedStats(): array
    {
        $pendingBills = PurchaseBill::whereNull('district_id')
            ->where(fn ($q) => $q->whereNull('location_status')->orWhere('location_status', '!=', 'skipped'))
            ->get();

        $skippedCount = PurchaseBill::where('location_status', 'skipped')->count();
        $totalPending = $pendingBills->count();
        $totalRev = $pendingBills->sum('total_amount');

        $suggestedCount = 0;
        foreach ($pendingBills as $b) {
            $m = null;
            if ($b->pharmacy) {
                $m = $this->matchDistrict($b->pharmacy->district) ?? $this->matchDistrict($b->pharmacy->address);
            }
            if (! $m && $b->user?->profile) {
                $m = $this->matchDistrict($b->user->profile->district) ?? $this->matchDistrict($b->user->profile->address);
            }
            if ($m) {
                $suggestedCount++;
            }
        }

        return [
            'total_pending' => $totalPending,
            'with_suggestions' => $suggestedCount,
            'need_manual' => max(0, $totalPending - $suggestedCount),
            'total_skipped' => $skippedCount,
            'total_revenue' => $totalRev,
        ];
    }
}
