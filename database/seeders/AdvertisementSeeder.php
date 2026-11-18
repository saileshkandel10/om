<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ads = [
            [
                'title' => 'Special Health Consultation & Medicine Rewards',
                'description' => 'Upload your pharmacy purchase bills to earn loyalty points, cashback vouchers, and lucky draw tickets.',
                'type' => 'text',
                'background_color' => 'linear-gradient(135deg, #CD0B6F 0%, #830444 100%)',
                'text_color' => '#FFFFFF',
                'button_color' => '#FFFFFF',
                'button_text_color' => '#CD0B6F',
                'cta_text' => 'Upload Bill Now',
                'cta_action' => 'https://ommegashreepharmaceuticals.com/rewards',
                'target_role' => 'patient',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Continuous Medical Education (CME) Webinars',
                'description' => 'Join clinical seminars on modern pharmacology, drug interactions, and digital prescription tracking.',
                'type' => 'text',
                'background_color' => 'linear-gradient(135deg, #065F46 0%, #047857 100%)',
                'text_color' => '#FFFFFF',
                'button_color' => '#FDE047',
                'button_text_color' => '#064E3B',
                'cta_text' => 'Join CME Portal',
                'cta_action' => 'https://ommegashreepharmaceuticals.com/cme',
                'target_role' => 'doctor',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Exclusive Direct Wholesale Sourcing Rewards',
                'description' => 'Order genuine OM Megashree pharmaceutical batches directly and unlock volume discount tiers.',
                'type' => 'text',
                'background_color' => 'linear-gradient(135deg, #C2410C 0%, #EA580C 100%)',
                'text_color' => '#FFFFFF',
                'button_color' => '#FFFFFF',
                'button_text_color' => '#C2410C',
                'cta_text' => 'View Catalog',
                'cta_action' => 'https://ommegashreepharmaceuticals.com/products',
                'target_role' => 'pharmacy',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Annual Shareholder Financial Report & AGM',
                'description' => 'Review audited statements, dividend distribution policies, and strategic expansion milestones.',
                'type' => 'text',
                'background_color' => 'linear-gradient(135deg, #0F2C59 0%, #1E3A5F 100%)',
                'text_color' => '#FFFFFF',
                'button_color' => '#CD0B6F',
                'button_text_color' => '#FFFFFF',
                'cta_text' => 'Download Report',
                'cta_action' => 'https://ommegashreepharmaceuticals.com/reports',
                'target_role' => 'shareholder',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Om Megashree Pharmaceuticals: A Pledge for Health & Nation',
                'description' => '"स्वास्थ्य र राष्ट्रका लागि एक सङ्कल्प" — Serving quality healthcare products nationwide.',
                'type' => 'text',
                'background_color' => 'linear-gradient(135deg, #581C87 0%, #7E22CE 100%)',
                'text_color' => '#FFFFFF',
                'button_color' => '#FFFFFF',
                'button_text_color' => '#6B21A8',
                'cta_text' => 'Learn More',
                'cta_action' => 'https://ommegashreepharmaceuticals.com/about',
                'target_role' => 'all',
                'display_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($ads as $ad) {
            Advertisement::firstOrCreate(
                ['title' => $ad['title'], 'target_role' => $ad['target_role']],
                $ad
            );
        }
    }
}
