<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            [
                'title' => 'Annual General Meeting 2026 Announcement',
                'notice_type' => 'agm',
                'audience_type' => 'shareholder',
                'body' => 'Dear Shareholders, The 12th Annual General Meeting of OM Pharmaceuticals Limited is scheduled for October 15, 2026. The book closure date is set for October 1, 2026.',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Special Bonus Points on OM Essential Products',
                'notice_type' => 'bonus',
                'audience_type' => 'all',
                'body' => 'Earn 2x reward loyalty points on all cardiac and diabetic healthcare products throughout this month. Upload purchase invoices to claim instant rewards.',
                'status' => 'published',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Authorized Pharmacy Sourcing Quality Protocol Update',
                'notice_type' => 'general',
                'audience_type' => 'pharmacy',
                'body' => 'All partner pharmacies must ensure batch validation and QR scanning compliance for all incoming antibiotic and pain management shipments.',
                'status' => 'published',
                'published_at' => now()->subHours(6),
            ],
        ];

        foreach ($notices as $notice) {
            Notice::firstOrCreate(
                ['title' => $notice['title']],
                $notice
            );
        }
    }
}
