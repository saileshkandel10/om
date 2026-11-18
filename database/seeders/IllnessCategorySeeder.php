<?php

namespace Database\Seeders;

use App\Models\IllnessCategory;
use Illuminate\Database\Seeder;

class IllnessCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Gastric', 'description' => 'Gastritis, acidity, ulcers, IBS, and digestive disorders.', 'status' => 'active'],
            ['name' => 'Diabetes', 'description' => 'Type 1 and Type 2 diabetes, insulin therapy, and glycemic control.', 'status' => 'active'],
            ['name' => 'Hypertension', 'description' => 'High blood pressure and related vascular management.', 'status' => 'active'],
            ['name' => 'Respiratory', 'description' => 'Asthma, cough, COPD, pneumonia, and other lung conditions.', 'status' => 'active'],
            ['name' => 'Skin Disease', 'description' => 'Skin infections, eczema, psoriasis, dermatitis, and allergies.', 'status' => 'active'],
            ['name' => 'Fever', 'description' => 'Viral fevers, infectious illnesses, and antipyretic care.', 'status' => 'active'],
            ['name' => 'Arthritis', 'description' => 'Joint pain, osteoarthritis, rheumatoid arthritis, and gout.', 'status' => 'active'],
            ['name' => 'Migraine', 'description' => 'Headache disorders, migraines, and neurological pain.', 'status' => 'active'],
            ['name' => 'Cardiovascular', 'description' => 'Heart disease, stroke prevention, and vascular health.', 'status' => 'active'],
            ['name' => 'Other', 'description' => 'General and unclassified medical conditions.', 'status' => 'active'],
            ['name' => 'Infectious Diseases', 'description' => 'Bacterial, viral, fungal, and parasitic infections.', 'status' => 'active'],
            ['name' => 'Endocrine', 'description' => 'Thyroid disorders and hormonal imbalances.', 'status' => 'active'],
            ['name' => 'Musculoskeletal', 'description' => 'Osteoporosis and muscle-related conditions.', 'status' => 'active'],
            ['name' => 'ENT', 'description' => 'Ear, nose, and throat infections and disorders.', 'status' => 'active'],
            ['name' => 'Ophthalmological', 'description' => 'Eye infections, glaucoma, and vision disorders.', 'status' => 'active'],
            ['name' => 'Psychiatric', 'description' => 'Depression, anxiety, and mental health.', 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            IllnessCategory::firstOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'status' => $category['status'] ?? 'active',
                ]
            );
        }
    }
}
