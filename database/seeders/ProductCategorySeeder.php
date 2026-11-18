<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Analgesics', 'description' => 'Pain relief medications for mild to severe pain.'],
            ['name' => 'Antibiotics', 'description' => 'Medications used to treat bacterial infections.'],
            ['name' => 'Antacids', 'description' => 'Medications for gastric acidity and heartburn relief.'],
            ['name' => 'Antihistamines', 'description' => 'Allergy relief medications for hay fever and urticaria.'],
            ['name' => 'Cough & Cold', 'description' => 'Cough suppressants, expectorants, and cold remedies.'],
            ['name' => 'Vitamins & Supplements', 'description' => 'Multivitamins, minerals, and dietary supplements.'],
            ['name' => 'Dermatologicals', 'description' => 'Topical treatments for skin conditions and infections.'],
            ['name' => 'Cardiovascular', 'description' => 'Medications for hypertension, cholesterol, and heart health.'],
            ['name' => 'Diabetes Care', 'description' => 'Anti-diabetic medications and glucose monitoring supplies.'],
            ['name' => 'Gastrointestinal', 'description' => 'Treatments for digestive disorders and IBS.'],
            ['name' => 'Respiratory', 'description' => 'Inhalers and medications for asthma and COPD.'],
            ['name' => 'Neurology', 'description' => 'Medications for epilepsy, migraine, and neurological disorders.'],
            ['name' => 'Psychiatry', 'description' => 'Antidepressants, anxiolytics, and mood stabilizers.'],
            ['name' => 'Ophthalmology', 'description' => 'Eye drops and ophthalmic preparations.'],
            ['name' => 'Hormonal', 'description' => 'Hormone replacement therapies and contraceptives.'],
            ['name' => 'Antifungals', 'description' => 'Fungal infection treatments for skin and systemic use.'],
            ['name' => 'Anti-inflammatories', 'description' => 'NSAIDs and corticosteroids for inflammation.'],
            ['name' => 'Pediatric', 'description' => 'Children-specific formulations and dosages.'],
            ['name' => 'Oncology', 'description' => 'Cancer treatment medications and supportive care.'],
            ['name' => 'Vaccines', 'description' => 'Preventive vaccines and immunoglobulins.'],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
