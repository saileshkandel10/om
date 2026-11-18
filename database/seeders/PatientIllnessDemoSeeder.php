<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\IllnessCategory;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\PatientIllnessService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientIllnessDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Essential Illness Categories
        $gastric = IllnessCategory::firstOrCreate(['name' => 'Gastric'], ['description' => 'Gastritis, acidity, gastric ulcers, and reflux disease.', 'status' => 'active']);
        $diabetes = IllnessCategory::firstOrCreate(['name' => 'Diabetes'], ['description' => 'Type 1 and Type 2 diabetes and glycemic management.', 'status' => 'active']);
        $hypertension = IllnessCategory::firstOrCreate(['name' => 'Hypertension'], ['description' => 'High blood pressure and hypertension management.', 'status' => 'active']);
        $respiratory = IllnessCategory::firstOrCreate(['name' => 'Respiratory'], ['description' => 'Asthma, cough, COPD, bronchitis, and lung infections.', 'status' => 'active']);
        $skin = IllnessCategory::firstOrCreate(['name' => 'Skin Disease'], ['description' => 'Dermatitis, eczema, psoriasis, and fungal infections.', 'status' => 'active']);
        $fever = IllnessCategory::firstOrCreate(['name' => 'Fever'], ['description' => 'Viral fevers, infectious pyrexia, and flu symptoms.', 'status' => 'active']);
        $arthritis = IllnessCategory::firstOrCreate(['name' => 'Arthritis'], ['description' => 'Osteoarthritis, rheumatoid arthritis, and joint pain.', 'status' => 'active']);
        $migraine = IllnessCategory::firstOrCreate(['name' => 'Migraine'], ['description' => 'Migraine headaches and chronic cephalalgia.', 'status' => 'active']);
        $cardiovascular = IllnessCategory::firstOrCreate(['name' => 'Cardiovascular'], ['description' => 'Cardiovascular and ischemic diseases.', 'status' => 'active']);
        $other = IllnessCategory::firstOrCreate(['name' => 'Other'], ['description' => 'General unclassified medical conditions.', 'status' => 'active']);

        // 2. Ensure Essential Products
        $prodCat = ProductCategory::firstOrCreate(['name' => 'Pharmaceuticals'], ['description' => 'General pharmaceuticals']);

        $corex = Product::updateOrCreate(
            ['brand_name' => 'Corex Cough Syrup'],
            [
                'sku' => 'MED-COR-100',
                'generic_name' => 'Chlorpheniramine + Codeine',
                'strength' => '100ml',
                'dosage_form' => 'Syrup',
                'product_category_id' => $prodCat->id,
                'illness_category_id' => $gastric->id, // Set to Gastric as specifically required by user!
                'mrp' => 185.00,
                'is_active' => true,
            ]
        );

        $panto = Product::updateOrCreate(
            ['brand_name' => 'Pantoprazole 40mg (Pantocid)'],
            [
                'sku' => 'MED-PAN-40',
                'generic_name' => 'Pantoprazole Sodium',
                'strength' => '40mg',
                'dosage_form' => 'Tablet',
                'product_category_id' => $prodCat->id,
                'illness_category_id' => $gastric->id,
                'mrp' => 120.00,
                'is_active' => true,
            ]
        );

        $metformin = Product::updateOrCreate(
            ['brand_name' => 'Metformin 500mg (Glycomet)'],
            [
                'sku' => 'MED-MET-500',
                'generic_name' => 'Metformin Hydrochloride',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'product_category_id' => $prodCat->id,
                'illness_category_id' => $diabetes->id,
                'mrp' => 95.00,
                'is_active' => true,
            ]
        );

        $amlodipine = Product::updateOrCreate(
            ['brand_name' => 'Amlodipine 5mg (Amlong)'],
            [
                'sku' => 'MED-AML-5',
                'generic_name' => 'Amlodipine Besylate',
                'strength' => '5mg',
                'dosage_form' => 'Tablet',
                'product_category_id' => $prodCat->id,
                'illness_category_id' => $hypertension->id,
                'mrp' => 110.00,
                'is_active' => true,
            ]
        );

        $sumatriptan = Product::updateOrCreate(
            ['brand_name' => 'Sumatriptan 50mg (Suminat)'],
            [
                'sku' => 'MED-SUM-50',
                'generic_name' => 'Sumatriptan Succinate',
                'strength' => '50mg',
                'dosage_form' => 'Tablet',
                'product_category_id' => $prodCat->id,
                'illness_category_id' => $migraine->id,
                'mrp' => 240.00,
                'is_active' => true,
            ]
        );

        // 3. Ensure Doctors
        $docRole = Role::firstOrCreate(['name' => 'doctor']);
        $doctorABC = User::firstOrCreate(
            ['email' => 'dr.abc@omconnect.com'],
            ['name' => 'Dr. ABC (Senior Gastroenterologist)', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9841000001']
        );
        $doctorABC->roles()->syncWithoutDetaching([$docRole->id]);
        Doctor::firstOrCreate(['user_id' => $doctorABC->id], ['registration_no' => 'NMC-8821', 'specialty' => 'Gastroenterology', 'status' => 'approved']);

        $doctorXYZ = User::firstOrCreate(
            ['email' => 'dr.xyz@omconnect.com'],
            ['name' => 'Dr. XYZ (Neurologist)', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9841000002']
        );
        $doctorXYZ->roles()->syncWithoutDetaching([$docRole->id]);
        Doctor::firstOrCreate(['user_id' => $doctorXYZ->id], ['registration_no' => 'NMC-9943', 'specialty' => 'Neurology', 'status' => 'approved']);

        // 4. Ensure Pharmacies
        $pharmUser1 = User::firstOrCreate(
            ['email' => 'pharmacy.ktm@omconnect.com'],
            ['name' => 'Om Central Pharmacy User', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9841112233']
        );
        $pharmUser2 = User::firstOrCreate(
            ['email' => 'pharmacy.lalitpur@omconnect.com'],
            ['name' => 'Om Lalitpur Pharmacy User', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9841112244']
        );

        $pharmacy1 = Pharmacy::firstOrCreate(
            ['store_name' => 'Om Mega Shree Central Pharmacy'],
            ['user_id' => $pharmUser1->id, 'pan_vat_no' => '304918291', 'district' => 'Kathmandu', 'address' => 'Putalisadak, Kathmandu', 'status' => 'active']
        );
        $pharmacy2 = Pharmacy::firstOrCreate(
            ['store_name' => 'Om Health Pharmacy Lalitpur'],
            ['user_id' => $pharmUser2->id, 'pan_vat_no' => '304918292', 'district' => 'Lalitpur', 'address' => 'Kumaripati, Lalitpur', 'status' => 'active']
        );

        // 5. Patient 1: Ram Sharma (P-1001, 45 yrs, Male, Gastric + Diabetes + Hypertension)
        $ram = User::updateOrCreate(
            ['email' => 'ram.sharma@example.com'],
            ['name' => 'Ram Sharma', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9841234567']
        );
        UserProfile::updateOrCreate(
            ['user_id' => $ram->id],
            [
                'dob' => Carbon::now()->subYears(45)->format('Y-m-d'),
                'gender' => 'male',
                'province' => 'Bagmati Province',
                'district' => 'Kathmandu',
                'address' => 'Baneshwor, Kathmandu',
            ]
        );

        $billRam1 = PurchaseBill::updateOrCreate(
            ['invoice_number' => 'INV-RAM-2026-01'],
            [
                'user_id' => $ram->id,
                'pharmacy_id' => $pharmacy1->id,
                'invoice_date' => Carbon::parse('2026-09-10'),
                'total_amount' => 1450.00,
                'subtotal_amount' => 1450.00,
                'ocr_status' => 'completed',
                'validation_status' => 'approved',
            ]
        );
        PurchaseBillItem::firstOrCreate(['purchase_bill_id' => $billRam1->id, 'product_id' => $panto->id], ['quantity' => 2, 'unit_price' => 120.00, 'amount' => 240.00, 'product_name_raw' => 'Pantoprazole 40mg']);
        PurchaseBillItem::firstOrCreate(['purchase_bill_id' => $billRam1->id, 'product_id' => $metformin->id], ['quantity' => 4, 'unit_price' => 95.00, 'amount' => 380.00, 'product_name_raw' => 'Metformin 500mg']);
        PurchaseBillItem::firstOrCreate(['purchase_bill_id' => $billRam1->id, 'product_id' => $amlodipine->id], ['quantity' => 3, 'unit_price' => 110.00, 'amount' => 330.00, 'product_name_raw' => 'Amlodipine 5mg']);

        // 6. Patient 2: Sita Thapa (P-1020, 38 yrs, Female, Gastric + Migraine)
        $sita = User::updateOrCreate(
            ['email' => 'sita.thapa@example.com'],
            ['name' => 'Sita Thapa', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9841987654']
        );
        UserProfile::updateOrCreate(
            ['user_id' => $sita->id],
            [
                'dob' => Carbon::now()->subYears(38)->format('Y-m-d'),
                'gender' => 'female',
                'province' => 'Bagmati Province',
                'district' => 'Lalitpur',
                'address' => 'Jawalakhel, Lalitpur',
            ]
        );

        $billSita1 = PurchaseBill::updateOrCreate(
            ['invoice_number' => 'INV-SITA-2026-01'],
            [
                'user_id' => $sita->id,
                'pharmacy_id' => $pharmacy2->id,
                'invoice_date' => Carbon::parse('2026-09-12'),
                'total_amount' => 890.00,
                'subtotal_amount' => 890.00,
                'ocr_status' => 'completed',
                'validation_status' => 'approved',
            ]
        );
        PurchaseBillItem::firstOrCreate(['purchase_bill_id' => $billSita1->id, 'product_id' => $panto->id], ['quantity' => 2, 'unit_price' => 120.00, 'amount' => 240.00, 'product_name_raw' => 'Pantoprazole 40mg']);
        PurchaseBillItem::firstOrCreate(['purchase_bill_id' => $billSita1->id, 'product_id' => $sumatriptan->id], ['quantity' => 2, 'unit_price' => 240.00, 'amount' => 480.00, 'product_name_raw' => 'Sumatriptan 50mg']);

        // 7. Patient 3: Sailesh with Bill 121343202 (purchased Corex Cough Syrup -> flagged as Gastric)
        $sailesh = User::updateOrCreate(
            ['email' => 'sailesh@omconnect.com'],
            ['name' => 'Sailesh Shrestha', 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '9851122334']
        );
        UserProfile::updateOrCreate(
            ['user_id' => $sailesh->id],
            [
                'dob' => Carbon::now()->subYears(34)->format('Y-m-d'),
                'gender' => 'male',
                'province' => 'Bagmati Province',
                'district' => 'Kathmandu',
                'address' => 'New Road, Kathmandu',
            ]
        );

        $billSailesh = PurchaseBill::updateOrCreate(
            ['invoice_number' => '121343202'],
            [
                'user_id' => $sailesh->id,
                'pharmacy_id' => $pharmacy1->id,
                'invoice_date' => Carbon::parse('2026-09-15'),
                'total_amount' => 555.00,
                'subtotal_amount' => 555.00,
                'ocr_status' => 'completed',
                'validation_status' => 'approved',
            ]
        );
        PurchaseBillItem::firstOrCreate(
            ['purchase_bill_id' => $billSailesh->id, 'product_id' => $corex->id],
            ['quantity' => 3, 'unit_price' => 185.00, 'amount' => 555.00, 'product_name_raw' => 'Corex Cough Syrup']
        );

        // 8. Additional Diverse Patients
        $additionalPatients = [
            ['name' => 'Bikash Karki', 'email' => 'bikash.karki@example.com', 'age' => 52, 'gender' => 'male', 'district' => 'Pokhara', 'illnesses' => [$gastric, $diabetes]],
            ['name' => 'Anita Gurung', 'email' => 'anita.gurung@example.com', 'age' => 29, 'gender' => 'female', 'district' => 'Chitwan', 'illnesses' => [$fever, $respiratory]],
            ['name' => 'Gopal Adhikari', 'email' => 'gopal.a@example.com', 'age' => 64, 'gender' => 'male', 'district' => 'Biratnagar', 'illnesses' => [$hypertension, $cardiovascular, $diabetes]],
            ['name' => 'Pooja Shrestha', 'email' => 'pooja.s@example.com', 'age' => 22, 'gender' => 'female', 'district' => 'Kathmandu', 'illnesses' => [$skin, $fever]],
            ['name' => 'Krishna Manandhar', 'email' => 'krishna.m@example.com', 'age' => 70, 'gender' => 'male', 'district' => 'Bhaktapur', 'illnesses' => [$arthritis, $hypertension]],
            ['name' => 'Sunita Rai', 'email' => 'sunita.rai@example.com', 'age' => 16, 'gender' => 'female', 'district' => 'Dharan', 'illnesses' => [$respiratory]],
            ['name' => 'Deepak Basnet', 'email' => 'deepak.b@example.com', 'age' => 48, 'gender' => 'male', 'district' => 'Butwal', 'illnesses' => [$gastric, $hypertension]],
        ];

        foreach ($additionalPatients as $pData) {
            $u = User::updateOrCreate(
                ['email' => $pData['email']],
                ['name' => $pData['name'], 'password' => Hash::make('password'), 'status' => 'active', 'mobile' => '98'.rand(10000000, 99999999)]
            );
            UserProfile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'dob' => Carbon::now()->subYears($pData['age'])->format('Y-m-d'),
                    'gender' => $pData['gender'],
                    'province' => 'Bagmati Province',
                    'district' => $pData['district'],
                    'address' => $pData['district'].', Nepal',
                ]
            );

            // Create a bill for them
            $b = PurchaseBill::updateOrCreate(
                ['invoice_number' => 'INV-GEN-'.strtoupper(substr(md5($pData['email']), 0, 6))],
                [
                    'user_id' => $u->id,
                    'pharmacy_id' => $pharmacy1->id,
                    'invoice_date' => Carbon::now()->subDays(rand(1, 90)),
                    'total_amount' => rand(500, 3500),
                    'subtotal_amount' => rand(500, 3500),
                    'ocr_status' => 'completed',
                    'validation_status' => 'approved',
                ]
            );

            foreach ($pData['illnesses'] as $ill) {
                // Find or create product for this illness
                $prod = Product::where('illness_category_id', $ill->id)->first();
                if ($prod) {
                    PurchaseBillItem::firstOrCreate(
                        ['purchase_bill_id' => $b->id, 'product_id' => $prod->id],
                        ['quantity' => 1, 'unit_price' => $prod->mrp ?? 100, 'amount' => $prod->mrp ?? 100, 'product_name_raw' => $prod->brand_name]
                    );
                }
            }
        }

        // 9. Synchronize all records
        app(PatientIllnessService::class)->backfillFromHistoricalData();
    }
}
