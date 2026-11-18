<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductDemandDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(NepalLocationSeeder::class);

        $districts = District::with('province')->where('status', 'active')->get()->keyBy('name');
        $ktm = $districts->get('Kathmandu');
        $llp = $districts->get('Lalitpur');
        $bkt = $districts->get('Bhaktapur');
        $kaski = $districts->get('Kaski');
        $cht = $districts->get('Chitwan');
        $jhapa = $districts->get('Jhapa');
        $morang = $districts->get('Morang');
        $rupandehi = $districts->get('Rupandehi');
        $kailali = $districts->get('Kailali');
        $surkhet = $districts->get('Surkhet');

        $activeDistricts = array_filter([$ktm, $llp, $bkt, $kaski, $cht, $jhapa, $morang, $rupandehi, $kailali, $surkhet]);

        $catPharma = ProductCategory::firstOrCreate(['name' => 'Pharmaceuticals'], ['description' => 'Medicines & healthcare']);
        $catSupplements = ProductCategory::firstOrCreate(['name' => 'Supplements & Nutrition'], ['description' => 'Vitamins and dietary supplements']);
        $catPersonal = ProductCategory::firstOrCreate(['name' => 'Personal Care'], ['description' => 'Dermatological and personal hygiene']);

        $products = [
            Product::updateOrCreate(['brand_name' => 'Corex Cough Syrup'], ['sku' => 'MED-COR-100', 'product_category_id' => $catPharma->id, 'mrp' => 185.00, 'is_active' => true]),
            Product::updateOrCreate(['brand_name' => 'Pantoprazole 40mg (Pantocid)'], ['sku' => 'MED-PAN-40', 'product_category_id' => $catPharma->id, 'mrp' => 120.00, 'is_active' => true]),
            Product::updateOrCreate(['brand_name' => 'Metformin 500mg (Glycomet)'], ['sku' => 'MED-MET-500', 'product_category_id' => $catPharma->id, 'mrp' => 95.00, 'is_active' => true]),
            Product::updateOrCreate(['brand_name' => 'Amlodipine 5mg (Amlong)'], ['sku' => 'MED-AML-5', 'product_category_id' => $catPharma->id, 'mrp' => 110.00, 'is_active' => true]),
            Product::updateOrCreate(['brand_name' => 'Omega-3 Fish Oil 1000mg'], ['sku' => 'SUP-OMG-1000', 'product_category_id' => $catSupplements->id, 'mrp' => 850.00, 'is_active' => true]),
            Product::updateOrCreate(['brand_name' => 'Vitamin C 500mg (Limcee)'], ['sku' => 'SUP-VIT-500', 'product_category_id' => $catSupplements->id, 'mrp' => 60.00, 'is_active' => true]),
            Product::updateOrCreate(['brand_name' => 'CeraVe Hydrating Cleanser'], ['sku' => 'SKN-CER-236', 'product_category_id' => $catPersonal->id, 'mrp' => 1450.00, 'is_active' => true]),
        ];

        $customersData = [
            ['name' => 'Suman Shrestha', 'email' => 'suman.shrestha@example.com', 'mobile' => '9841100001', 'district' => $ktm, 'purchases' => 5],
            ['name' => 'Radha Adhikari', 'email' => 'radha.adhikari@example.com', 'mobile' => '9841100002', 'district' => $ktm, 'purchases' => 3],
            ['name' => 'Prakash Thapa', 'email' => 'prakash.thapa@example.com', 'mobile' => '9841100003', 'district' => $llp, 'purchases' => 4],
            ['name' => 'Maya Gurung', 'email' => 'maya.gurung@example.com', 'mobile' => '9841100004', 'district' => $kaski, 'purchases' => 2],
            ['name' => 'Dipendra Karki', 'email' => 'dipendra.karki@example.com', 'mobile' => '9841100005', 'district' => $cht, 'purchases' => 3],
            ['name' => 'Binod Rai', 'email' => 'binod.rai@example.com', 'mobile' => '9841100006', 'district' => $jhapa, 'purchases' => 2],
            ['name' => 'Sarita Dahal', 'email' => 'sarita.dahal@example.com', 'mobile' => '9841100007', 'district' => $morang, 'purchases' => 1],
            ['name' => 'Santosh Yadav', 'email' => 'santosh.yadav@example.com', 'mobile' => '9841100008', 'district' => $rupandehi, 'purchases' => 1],
            ['name' => 'Gita Sharma', 'email' => 'gita.sharma@example.com', 'mobile' => '9841100009', 'district' => $bkt, 'purchases' => 2],
            ['name' => 'Bishnu BK', 'email' => 'bishnu.bk@example.com', 'mobile' => '9841100010', 'district' => $kailali, 'purchases' => 1],
            ['name' => 'Kopila Khatri', 'email' => 'kopila.khatri@example.com', 'mobile' => '9841100011', 'district' => $surkhet, 'purchases' => 1],
        ];

        foreach ($customersData as $cd) {
            $user = User::updateOrCreate(
                ['email' => $cd['email']],
                ['name' => $cd['name'], 'password' => Hash::make('password'), 'mobile' => $cd['mobile'], 'status' => 'active']
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'dob' => '1990-01-01',
                    'gender' => 'male',
                    'district_id' => $cd['district']?->id,
                    'province_id' => $cd['district']?->province_id,
                    'district' => $cd['district']?->name,
                    'province' => $cd['district']?->province?->name,
                    'address' => $cd['district']?->name.', Nepal',
                ]
            );

            for ($i = 0; $i < $cd['purchases']; $i++) {
                $orderDate = Carbon::now()->subDays($i * 12 + rand(1, 5))->setTime(rand(9, 20), rand(0, 59));
                $invNum = 'INV-DEMO-'.strtoupper(substr(md5($cd['email'].$i), 0, 7));

                $bill = PurchaseBill::updateOrCreate(
                    ['invoice_number' => $invNum],
                    [
                        'user_id' => $user->id,
                        'district_id' => $cd['district']?->id,
                        'province_id' => $cd['district']?->province_id,
                        'invoice_date' => $orderDate->toDateString(),
                        'created_at' => $orderDate,
                        'total_amount' => 0,
                        'subtotal_amount' => 0,
                        'order_status' => 'delivered',
                        'payment_status' => 'paid',
                        'validation_status' => 'approved',
                    ]
                );

                $totalAmount = 0;
                $pickedProducts = collect($products)->random(rand(1, 3));
                foreach ($pickedProducts as $prod) {
                    $qty = rand(1, 4);
                    $price = $prod->mrp ?? 100;
                    $itemTotal = $qty * $price;
                    $totalAmount += $itemTotal;

                    PurchaseBillItem::updateOrCreate(
                        ['purchase_bill_id' => $bill->id, 'product_id' => $prod->id],
                        ['product_name_raw' => $prod->brand_name, 'quantity' => $qty, 'unit_price' => $price, 'amount' => $itemTotal]
                    );
                }

                $bill->update(['total_amount' => $totalAmount, 'subtotal_amount' => $totalAmount]);
            }
        }
    }
}
