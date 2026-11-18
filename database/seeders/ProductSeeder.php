<?php

namespace Database\Seeders;

use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const array BRANDS = [
        ['brand_name' => 'Napa', 'generic_name' => 'Paracetamol', 'strength' => '500mg', 'dosage_form' => 'Tablet', 'pack_size' => '100\'s', 'mrp' => 35.00, 'min_category' => 'Analgesics', 'illness' => 'Infectious Diseases'],
        ['brand_name' => 'Fexo', 'generic_name' => 'Fexofenadine', 'strength' => '120mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 120.00, 'min_category' => 'Antihistamines', 'illness' => 'Allergic'],
        ['brand_name' => 'Omepra', 'generic_name' => 'Omeprazole', 'strength' => '20mg', 'dosage_form' => 'Capsule', 'pack_size' => '30\'s', 'mrp' => 80.00, 'min_category' => 'Antacids', 'illness' => 'Gastrointestinal'],
        ['brand_name' => 'Amlo', 'generic_name' => 'Amlodipine', 'strength' => '5mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 45.00, 'min_category' => 'Cardiovascular', 'illness' => 'Cardiovascular'],
        ['brand_name' => 'Metro', 'generic_name' => 'Metronidazole', 'strength' => '400mg', 'dosage_form' => 'Tablet', 'pack_size' => '20\'s', 'mrp' => 25.00, 'min_category' => 'Antibiotics', 'illness' => 'Infectious Diseases'],
        ['brand_name' => 'Cipro', 'generic_name' => 'Ciprofloxacin', 'strength' => '500mg', 'dosage_form' => 'Tablet', 'pack_size' => '20\'s', 'mrp' => 60.00, 'min_category' => 'Antibiotics', 'illness' => 'Infectious Diseases'],
        ['brand_name' => 'Doxy', 'generic_name' => 'Doxycycline', 'strength' => '100mg', 'dosage_form' => 'Capsule', 'pack_size' => '15\'s', 'mrp' => 55.00, 'min_category' => 'Antibiotics', 'illness' => 'Infectious Diseases'],
        ['brand_name' => 'Rani', 'generic_name' => 'Ranitidine', 'strength' => '150mg', 'dosage_form' => 'Tablet', 'pack_size' => '50\'s', 'mrp' => 30.00, 'min_category' => 'Antacids', 'illness' => 'Gastrointestinal'],
        ['brand_name' => 'Salbo', 'generic_name' => 'Salbutamol', 'strength' => '100mcg', 'dosage_form' => 'Inhaler', 'pack_size' => '200 doses', 'mrp' => 250.00, 'min_category' => 'Respiratory', 'illness' => 'Respiratory'],
        ['brand_name' => 'Glimi', 'generic_name' => 'Glimepiride', 'strength' => '2mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 65.00, 'min_category' => 'Diabetes Care', 'illness' => 'Endocrine'],
        ['brand_name' => 'Eso', 'generic_name' => 'Esomeprazole', 'strength' => '40mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 150.00, 'min_category' => 'Antacids', 'illness' => 'Gastrointestinal'],
        ['brand_name' => 'Ceta', 'generic_name' => 'Cetirizine', 'strength' => '10mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 25.00, 'min_category' => 'Antihistamines', 'illness' => 'Allergic'],
        ['brand_name' => 'Montel', 'generic_name' => 'Montelukast', 'strength' => '10mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 180.00, 'min_category' => 'Respiratory', 'illness' => 'Respiratory'],
        ['brand_name' => 'Atorva', 'generic_name' => 'Atorvastatin', 'strength' => '10mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 90.00, 'min_category' => 'Cardiovascular', 'illness' => 'Cardiovascular'],
        ['brand_name' => 'Losar', 'generic_name' => 'Losartan', 'strength' => '50mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 75.00, 'min_category' => 'Cardiovascular', 'illness' => 'Cardiovascular'],
        ['brand_name' => 'Pantop', 'generic_name' => 'Pantoprazole', 'strength' => '40mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 110.00, 'min_category' => 'Antacids', 'illness' => 'Gastrointestinal'],
        ['brand_name' => 'Flu', 'generic_name' => 'Fluconazole', 'strength' => '150mg', 'dosage_form' => 'Capsule', 'pack_size' => '1\'s', 'mrp' => 40.00, 'min_category' => 'Antifungals', 'illness' => 'Infectious Diseases'],
        ['brand_name' => 'Acyclo', 'generic_name' => 'Acyclovir', 'strength' => '200mg', 'dosage_form' => 'Tablet', 'pack_size' => '25\'s', 'mrp' => 135.00, 'min_category' => 'Antibiotics', 'illness' => 'Dermatological'],
        ['brand_name' => 'Predni', 'generic_name' => 'Prednisolone', 'strength' => '5mg', 'dosage_form' => 'Tablet', 'pack_size' => '50\'s', 'mrp' => 70.00, 'min_category' => 'Anti-inflammatories', 'illness' => 'Autoimmune'],
        ['brand_name' => 'Dia', 'generic_name' => 'Diazepam', 'strength' => '5mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 45.00, 'min_category' => 'Psychiatry', 'illness' => 'Psychiatric'],
        ['brand_name' => 'Metfor', 'generic_name' => 'Metformin', 'strength' => '500mg', 'dosage_form' => 'Tablet', 'pack_size' => '50\'s', 'mrp' => 40.00, 'min_category' => 'Diabetes Care', 'illness' => 'Endocrine'],
        ['brand_name' => 'Asam', 'generic_name' => 'Acetylsalicylic Acid', 'strength' => '75mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 15.00, 'min_category' => 'Cardiovascular', 'illness' => 'Cardiovascular'],
        ['brand_name' => 'Diclofen', 'generic_name' => 'Diclofenac Sodium', 'strength' => '50mg', 'dosage_form' => 'Tablet', 'pack_size' => '50\'s', 'mrp' => 30.00, 'min_category' => 'Anti-inflammatories', 'illness' => 'Musculoskeletal'],
        ['brand_name' => 'Amox', 'generic_name' => 'Amoxicillin', 'strength' => '500mg', 'dosage_form' => 'Capsule', 'pack_size' => '20\'s', 'mrp' => 35.00, 'min_category' => 'Antibiotics', 'illness' => 'Infectious Diseases'],
        ['brand_name' => 'Ome', 'generic_name' => 'Omeprazole', 'strength' => '40mg', 'dosage_form' => 'Injection', 'pack_size' => '1 vial', 'mrp' => 85.00, 'min_category' => 'Antacids', 'illness' => 'Gastrointestinal'],
        ['brand_name' => 'Vit C', 'generic_name' => 'Ascorbic Acid', 'strength' => '500mg', 'dosage_form' => 'Tablet', 'pack_size' => '100\'s', 'mrp' => 50.00, 'min_category' => 'Vitamins & Supplements', 'illness' => 'Metabolic'],
        ['brand_name' => 'Calci D', 'generic_name' => 'Calcium + Vitamin D3', 'strength' => '500mg+400IU', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 120.00, 'min_category' => 'Vitamins & Supplements', 'illness' => 'Musculoskeletal'],
        ['brand_name' => 'B Comp', 'generic_name' => 'Vitamin B Complex', 'strength' => '—', 'dosage_form' => 'Tablet', 'pack_size' => '100\'s', 'mrp' => 65.00, 'min_category' => 'Vitamins & Supplements', 'illness' => 'Metabolic'],
        ['brand_name' => 'Iron Plus', 'generic_name' => 'Ferrous Sulfate + Folic Acid', 'strength' => '200mg+0.5mg', 'dosage_form' => 'Tablet', 'pack_size' => '30\'s', 'mrp' => 45.00, 'min_category' => 'Vitamins & Supplements', 'illness' => 'Hematological'],
        ['brand_name' => 'Napa Extra', 'generic_name' => 'Paracetamol + Caffeine', 'strength' => '500mg+65mg', 'dosage_form' => 'Tablet', 'pack_size' => '50\'s', 'mrp' => 40.00, 'min_category' => 'Analgesics', 'illness' => 'Infectious Diseases'],
    ];

    public function run(): void
    {
        $categories = ProductCategory::pluck('id', 'name');
        $illnessCategories = IllnessCategory::pluck('id', 'name');

        foreach (self::BRANDS as $index => $brand) {
            $sku = 'PH-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'brand_name' => $brand['brand_name'],
                    'generic_name' => $brand['generic_name'],
                    'strength' => $brand['strength'],
                    'dosage_form' => $brand['dosage_form'],
                    'pack_size' => $brand['pack_size'],
                    'product_category_id' => $categories[$brand['min_category']] ?? null,
                    'illness_category_id' => $illnessCategories[$brand['illness']] ?? null,
                    'mrp' => $brand['mrp'],
                    'image' => null,
                    'launch_status' => 'launched',
                    'is_active' => true,
                    'description' => $brand['brand_name'].' '.$brand['strength'].' '.$brand['dosage_form'].' - '.$brand['generic_name'].'. Used for '.strtolower($brand['illness']).'.',
                ]
            );
        }
    }
}
