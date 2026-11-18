<?php

namespace App\Console\Commands;

use App\Models\CampaignRecipient;
use App\Models\IllnessCategory;
use App\Models\PatientIllness;
use App\Models\PrescriptionItem;
use App\Models\Product;
use Illuminate\Console\Command;

class CleanDuplicateIllnessCategories extends Command
{
    protected $signature = 'illness-categories:clean-duplicates';

    protected $description = 'Find and merge duplicate illness categories, keeping the primary one with all associations';

    public function handle(): int
    {
        $this->info('Scanning illness categories for duplicates...');

        $allCategories = IllnessCategory::withCount(['patients', 'products', 'patientIllnesses'])->orderBy('id')->get();

        $this->info("Total categories in database: {$allCategories->count()}");

        $grouped = [];
        foreach ($allCategories as $cat) {
            $normalizedName = strtolower(trim($cat->name));
            $grouped[$normalizedName][] = $cat;
        }

        $mergedCount = 0;
        $deletedCount = 0;

        foreach ($grouped as $normalizedName => $cats) {
            if (count($cats) <= 1) {
                continue;
            }

            $this->warn("Duplicate detected for: '{$normalizedName}' (".count($cats).' entries)');

            // Pick the primary category (the one with the most patients/products, or lowest ID)
            usort($cats, function ($a, $b) {
                $scoreA = ($a->patients_count * 10) + ($a->products_count * 5) + $a->patient_illnesses_count;
                $scoreB = ($b->patients_count * 10) + ($b->products_count * 5) + $b->patient_illnesses_count;
                if ($scoreA === $scoreB) {
                    return $a->id <=> $b->id;
                }

                return $scoreB <=> $scoreA;
            });

            $primary = $cats[0];
            $duplicates = array_slice($cats, 1);

            $this->info("  -> Keeping Primary: ID {$primary->id} ('{$primary->name}') with {$primary->patients_count} patients, {$primary->products_count} products.");

            foreach ($duplicates as $dup) {
                $this->line("  -> Merging Duplicate: ID {$dup->id} ('{$dup->name}') into Primary ID {$primary->id}");

                // 1. Reassign products
                Product::where('illness_category_id', $dup->id)->update(['illness_category_id' => $primary->id]);

                // 2. Reassign patient illnesses (avoid duplicates for same user)
                $dupPatientIllnesses = PatientIllness::where('illness_category_id', $dup->id)->get();
                foreach ($dupPatientIllnesses as $pi) {
                    $existsOnPrimary = PatientIllness::where('user_id', $pi->user_id)
                        ->where('illness_category_id', $primary->id)
                        ->exists();

                    if (! $existsOnPrimary) {
                        $pi->update(['illness_category_id' => $primary->id]);
                    } else {
                        $pi->delete();
                    }
                }

                // 3. Reassign prescription items
                PrescriptionItem::where('illness_category_id', $dup->id)->update(['illness_category_id' => $primary->id]);

                // 4. Reassign campaign recipients
                CampaignRecipient::where('illness_category_id', $dup->id)->update(['illness_category_id' => $primary->id]);

                // 5. Delete duplicate category
                $dup->delete();
                $deletedCount++;
            }

            $mergedCount++;
        }

        $this->info("Merge complete! {$mergedCount} duplicated groups merged, {$deletedCount} redundant categories removed.");

        // Show remaining clean categories list
        $remaining = IllnessCategory::withCount(['patients', 'products', 'patientIllnesses'])->orderBy('name')->get();
        $this->table(
            ['ID', 'Name', 'Status', 'Unique Patients', 'Products', 'Illness Records'],
            $remaining->map(fn ($c) => [$c->id, $c->name, $c->status ?? 'active', $c->patients_count, $c->products_count, $c->patient_illnesses_count])
        );

        return Command::SUCCESS;
    }
}
