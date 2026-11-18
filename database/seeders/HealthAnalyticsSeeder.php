<?php

namespace Database\Seeders;

use App\Models\HealthCategory;
use App\Models\Medicine;
use App\Models\MedicineIndication;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HealthAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Blood Sugar Management', 'description' => 'Medications for glycemic control and diabetes care', 'icon' => 'iconoir-drop', 'color' => '#E67E22'],
            ['name' => 'Blood Pressure Management', 'description' => 'Medications for hypertension and blood pressure regulation', 'icon' => 'iconoir-activity', 'color' => '#E74C3C'],
            ['name' => 'Cardiovascular', 'description' => 'Cardiac, ischemic, and vascular support therapies', 'icon' => 'iconoir-heart', 'color' => '#C0392B'],
            ['name' => 'Lipid/Cholesterol Management', 'description' => 'Statins and lipid-lowering pharmacological agents', 'icon' => 'iconoir-filter', 'color' => '#8E44AD'],
            ['name' => 'Respiratory', 'description' => 'Bronchodilators, inhalers, and pulmonary medications', 'icon' => 'iconoir-lungs', 'color' => '#2980B9'],
            ['name' => 'Gastrointestinal/Digestive', 'description' => 'Antacids, proton pump inhibitors, and digestive motility aids', 'icon' => 'iconoir-pill', 'color' => '#27AE60'],
            ['name' => 'Thyroid', 'description' => 'Thyroid hormone replacement and endocrine regulation', 'icon' => 'iconoir-shield-alert', 'color' => '#F39C12'],
            ['name' => 'Pain Management', 'description' => 'Analgesics, NSAIDs, and antipyretics for pain relief', 'icon' => 'iconoir-bandage', 'color' => '#D35400'],
            ['name' => 'Infection Management', 'description' => 'Antibiotics, antivirals, and antimicrobial agents', 'icon' => 'iconoir-virus', 'color' => '#16A085'],
            ['name' => 'Allergy', 'description' => 'Antihistamines and allergic rhinitis treatments', 'icon' => 'iconoir-leaf', 'color' => '#2ECC71'],
            ['name' => 'Dermatological', 'description' => 'Topical agents and dermatologic disease treatments', 'icon' => 'iconoir-sun-light', 'color' => '#1ABC9C'],
            ['name' => 'Neurological', 'description' => 'Neurotropic, anticonvulsant, and central nervous agents', 'icon' => 'iconoir-brain', 'color' => '#34495E'],
            ['name' => 'Mental Health', 'description' => 'Anxiolytics, antidepressants, and psychiatric therapies', 'icon' => 'iconoir-face-neutral', 'color' => '#5D6D7E'],
            ['name' => 'Bone & Joint', 'description' => 'Calcium supplements, vitamin D, and anti-arthritic therapies', 'icon' => 'iconoir-bone', 'color' => '#7F8C8D'],
            ['name' => 'Kidney/Urinary', 'description' => 'Renal protectants, diuretics, and urinary agents', 'icon' => 'iconoir-droplet', 'color' => '#3498DB'],
            ['name' => 'Liver/Gallbladder', 'description' => 'Hepatoprotectants and biliary tract medications', 'icon' => 'iconoir-database', 'color' => '#9B59B6'],
            ['name' => 'Women\'s Health', 'description' => 'Gynecological, obstetric, and hormonal therapies', 'icon' => 'iconoir-female', 'color' => '#CD0B6F'],
            ['name' => 'Other', 'description' => 'General vitamins, minerals, and supportive healthcare', 'icon' => 'iconoir-more-horiz', 'color' => '#95A5A6'],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $record = HealthCategory::firstOrCreate(
                ['name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'status' => 'active',
                ]
            );
            $categoryMap[$cat['name']] = $record->id;
        }

        $medicines = [
            [
                'brand_name' => 'Glucophage',
                'generic_name' => 'Metformin',
                'active_ingredients' => 'Metformin Hydrochloride',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Biguanide Antidiabetic',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Blood Sugar Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Amaryl',
                'generic_name' => 'Glimepiride',
                'active_ingredients' => 'Glimepiride',
                'strength' => '2mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Sulfonylurea Antidiabetic',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Blood Sugar Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Telma',
                'generic_name' => 'Telmisartan',
                'active_ingredients' => 'Telmisartan',
                'strength' => '40mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Angiotensin II Receptor Blocker',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Blood Pressure Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Norvasc',
                'generic_name' => 'Amlodipine',
                'active_ingredients' => 'Amlodipine Besylate',
                'strength' => '5mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Calcium Channel Blocker',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Blood Pressure Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Lipitor',
                'generic_name' => 'Atorvastatin',
                'active_ingredients' => 'Atorvastatin Calcium',
                'strength' => '20mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'HMG-CoA Reductase Inhibitor',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Lipid/Cholesterol Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Crestor',
                'generic_name' => 'Rosuvastatin',
                'active_ingredients' => 'Rosuvastatin Calcium',
                'strength' => '10mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'HMG-CoA Reductase Inhibitor',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Lipid/Cholesterol Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Pan',
                'generic_name' => 'Pantoprazole',
                'active_ingredients' => 'Pantoprazole Sodium',
                'strength' => '40mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Proton Pump Inhibitor',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Gastrointestinal/Digestive',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'OmParacetamol',
                'generic_name' => 'Paracetamol',
                'active_ingredients' => 'Acetaminophen',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Analgesic & Antipyretic',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Pain Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Zithromax',
                'generic_name' => 'Azithromycin',
                'active_ingredients' => 'Azithromycin Dihydrate',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Macrolide Antibiotic',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Infection Management',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Singulair',
                'generic_name' => 'Montelukast',
                'active_ingredients' => 'Montelukast Sodium',
                'strength' => '10mg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Leukotriene Receptor Antagonist',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Respiratory',
                'evidence' => 'High',
            ],
            [
                'brand_name' => 'Eltroxin',
                'generic_name' => 'Thyroxine',
                'active_ingredients' => 'Levothyroxine Sodium',
                'strength' => '50mcg',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Thyroid Hormone',
                'manufacturer' => 'OM Megashree Pharmaceuticals',
                'category' => 'Thyroid',
                'evidence' => 'High',
            ],
        ];

        foreach ($medicines as $med) {
            $product = Product::where('brand_name', 'like', "%{$med['brand_name']}%")
                ->orWhere('generic_name', 'like', "%{$med['generic_name']}%")
                ->first();

            $medicine = Medicine::firstOrCreate(
                ['brand_name' => $med['brand_name']],
                [
                    'generic_name' => $med['generic_name'],
                    'active_ingredients' => $med['active_ingredients'],
                    'strength' => $med['strength'],
                    'dosage_form' => $med['dosage_form'],
                    'therapeutic_class' => $med['therapeutic_class'],
                    'manufacturer' => $med['manufacturer'],
                    'product_id' => $product?->id,
                    'status' => 'active',
                ]
            );

            if (isset($categoryMap[$med['category']])) {
                MedicineIndication::firstOrCreate(
                    [
                        'medicine_id' => $medicine->id,
                        'health_category_id' => $categoryMap[$med['category']],
                    ],
                    [
                        'evidence_level' => $med['evidence'],
                        'notes' => "Standard primary therapeutic mapping for {$med['generic_name']}",
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
