<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Seeder;

class NepalLocationSeeder extends Seeder
{
    public function run(): void
    {
        $provincesData = [
            [
                'name' => 'Koshi Province',
                'code' => 'P1',
                'districts' => [
                    ['name' => 'Bhojpur', 'code' => 'BHO'],
                    ['name' => 'Dhankuta', 'code' => 'DHA'],
                    ['name' => 'Ilam', 'code' => 'ILA'],
                    ['name' => 'Jhapa', 'code' => 'JHA'],
                    ['name' => 'Khotang', 'code' => 'KHO'],
                    ['name' => 'Morang', 'code' => 'MOR'],
                    ['name' => 'Okhaldhunga', 'code' => 'OKH'],
                    ['name' => 'Panchthar', 'code' => 'PAN'],
                    ['name' => 'Sankhuwasabha', 'code' => 'SAN'],
                    ['name' => 'Solukhumbu', 'code' => 'SOL'],
                    ['name' => 'Sunsari', 'code' => 'SUN'],
                    ['name' => 'Taplejung', 'code' => 'TAP'],
                    ['name' => 'Terhathum', 'code' => 'TER'],
                    ['name' => 'Udayapur', 'code' => 'UDA'],
                ],
            ],
            [
                'name' => 'Madhesh Province',
                'code' => 'P2',
                'districts' => [
                    ['name' => 'Saptari', 'code' => 'SAP'],
                    ['name' => 'Siraha', 'code' => 'SIR'],
                    ['name' => 'Dhanusha', 'code' => 'DHN'],
                    ['name' => 'Mahottari', 'code' => 'MAH'],
                    ['name' => 'Sarlahi', 'code' => 'SAR'],
                    ['name' => 'Rautahat', 'code' => 'RAU'],
                    ['name' => 'Bara', 'code' => 'BAR'],
                    ['name' => 'Parsa', 'code' => 'PAR'],
                ],
            ],
            [
                'name' => 'Bagmati Province',
                'code' => 'P3',
                'districts' => [
                    ['name' => 'Sindhuli', 'code' => 'SIN'],
                    ['name' => 'Ramechhap', 'code' => 'RAM'],
                    ['name' => 'Dolakha', 'code' => 'DOL'],
                    ['name' => 'Bhaktapur', 'code' => 'BKT'],
                    ['name' => 'Dhading', 'code' => 'DHD'],
                    ['name' => 'Kathmandu', 'code' => 'KTM'],
                    ['name' => 'Kavrepalanchok', 'code' => 'KAV'],
                    ['name' => 'Lalitpur', 'code' => 'LLP'],
                    ['name' => 'Nuwakot', 'code' => 'NUW'],
                    ['name' => 'Rasuwa', 'code' => 'RAS'],
                    ['name' => 'Sindhupalchok', 'code' => 'SDP'],
                    ['name' => 'Chitwan', 'code' => 'CHT'],
                    ['name' => 'Makwanpur', 'code' => 'MAK'],
                ],
            ],
            [
                'name' => 'Gandaki Province',
                'code' => 'P4',
                'districts' => [
                    ['name' => 'Baglung', 'code' => 'BAG'],
                    ['name' => 'Gorkha', 'code' => 'GOR'],
                    ['name' => 'Kaski', 'code' => 'KAS'],
                    ['name' => 'Lamjung', 'code' => 'LAM'],
                    ['name' => 'Manang', 'code' => 'MAN'],
                    ['name' => 'Mustang', 'code' => 'MUS'],
                    ['name' => 'Myagdi', 'code' => 'MYA'],
                    ['name' => 'Nawalpur', 'code' => 'NWP'],
                    ['name' => 'Parbat', 'code' => 'PRB'],
                    ['name' => 'Syangja', 'code' => 'SYA'],
                    ['name' => 'Tanahun', 'code' => 'TAN'],
                ],
            ],
            [
                'name' => 'Lumbini Province',
                'code' => 'P5',
                'districts' => [
                    ['name' => 'Kapilvastu', 'code' => 'KAP'],
                    ['name' => 'Parasi', 'code' => 'PRS'],
                    ['name' => 'Rupandehi', 'code' => 'RUP'],
                    ['name' => 'Arghakhanchi', 'code' => 'ARG'],
                    ['name' => 'Gulmi', 'code' => 'GUL'],
                    ['name' => 'Palpa', 'code' => 'PAL'],
                    ['name' => 'Dang', 'code' => 'DAN'],
                    ['name' => 'Pyuthan', 'code' => 'PYU'],
                    ['name' => 'Rolpa', 'code' => 'ROL'],
                    ['name' => 'Eastern Rukum', 'code' => 'ERU'],
                    ['name' => 'Banke', 'code' => 'BAN'],
                    ['name' => 'Bardiya', 'code' => 'BRD'],
                ],
            ],
            [
                'name' => 'Karnali Province',
                'code' => 'P6',
                'districts' => [
                    ['name' => 'Western Rukum', 'code' => 'WRU'],
                    ['name' => 'Salyan', 'code' => 'SAL'],
                    ['name' => 'Dolpa', 'code' => 'DLP'],
                    ['name' => 'Humla', 'code' => 'HUM'],
                    ['name' => 'Jumla', 'code' => 'JUM'],
                    ['name' => 'Kalikot', 'code' => 'KAL'],
                    ['name' => 'Mugu', 'code' => 'MUG'],
                    ['name' => 'Surkhet', 'code' => 'SUR'],
                    ['name' => 'Dailekh', 'code' => 'DAI'],
                    ['name' => 'Jajarkot', 'code' => 'JAJ'],
                ],
            ],
            [
                'name' => 'Sudurpashchim Province',
                'code' => 'P7',
                'districts' => [
                    ['name' => 'Kailali', 'code' => 'KAI'],
                    ['name' => 'Achham', 'code' => 'ACH'],
                    ['name' => 'Doti', 'code' => 'DOT'],
                    ['name' => 'Bajhang', 'code' => 'BJH'],
                    ['name' => 'Bajura', 'code' => 'BJR'],
                    ['name' => 'Kanchanpur', 'code' => 'KAN'],
                    ['name' => 'Dadeldhura', 'code' => 'DAD'],
                    ['name' => 'Baitadi', 'code' => 'BAI'],
                    ['name' => 'Darchula', 'code' => 'DAR'],
                ],
            ],
        ];

        foreach ($provincesData as $pData) {
            $province = Province::firstOrCreate(
                ['code' => $pData['code']],
                ['name' => $pData['name'], 'status' => 'active']
            );

            foreach ($pData['districts'] as $dData) {
                District::firstOrCreate(
                    ['name' => $dData['name']],
                    [
                        'province_id' => $province->id,
                        'code' => $dData['code'],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
