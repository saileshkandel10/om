<?php

namespace Database\Seeders;

use App\Models\ShareholderMaster;
use Illuminate\Database\Seeder;

class ShareholderMasterSeeder extends Seeder
{
    public function run(): void
    {
        $shareholders = [
            [
                'boid' => '1301000000001234',
                'shareholder_name' => 'Aarav Sharma',
                'mobile' => '9851012345',
                'email' => 'aarav.sharma@example.com',
                'status' => 'active',
                'source_note' => 'OM Pharmaceuticals AGM 2026 Registry',
            ],
            [
                'boid' => '1301000000005678',
                'shareholder_name' => 'Pooja Thapa',
                'mobile' => '9841234567',
                'email' => 'pooja.thapa@example.com',
                'status' => 'active',
                'source_note' => 'OM Pharmaceuticals AGM 2026 Registry',
            ],
            [
                'boid' => '1301000000009999',
                'shareholder_name' => 'Bikash Adhikari',
                'mobile' => '9801234567',
                'email' => 'bikash.adhikari@example.com',
                'status' => 'active',
                'source_note' => 'OM Pharmaceuticals AGM 2026 Registry',
            ],
        ];

        foreach ($shareholders as $data) {
            ShareholderMaster::firstOrCreate(
                ['boid' => $data['boid']],
                $data
            );
        }
    }
}
