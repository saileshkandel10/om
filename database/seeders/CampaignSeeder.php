<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            [
                'name' => 'Cardio Care Awareness & Loyalty Month',
                'campaign_type' => 'chronic_care',
                'target_segment' => 'shareholder',
                'description' => 'Special shareholder bonus and health checkup discounts for cardiovascular care products and free lipid profile testing coupons.',
                'status' => 'active',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
            ],
            [
                'name' => 'Pharmacy Partner Summer Incentive',
                'campaign_type' => 'trade_incentive',
                'target_segment' => 'pharmacy',
                'description' => 'Achieve purchase volume targets across OM branded syrups and suspension lines for exclusive bulk trade rebates and loyalty credits.',
                'status' => 'active',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(45),
            ],
            [
                'name' => 'Doctor CME & Chronic Disease Awareness Camp',
                'campaign_type' => 'cme',
                'target_segment' => 'doctor',
                'description' => 'Continuing medical education series on modern hypertensive protocols with clinical case sharing and patient adherence programs.',
                'status' => 'active',
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(30),
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::firstOrCreate(
                ['name' => $campaign['name']],
                $campaign
            );
        }
    }
}
