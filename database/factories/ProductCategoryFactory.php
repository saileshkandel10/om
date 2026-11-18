<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    private static array $categories = [
        'Analgesics' => 'Pain relief medications for mild to severe pain.',
        'Antibiotics' => 'Medications used to treat bacterial infections.',
        'Antacids' => 'Medications for gastric acidity and heartburn relief.',
        'Antihistamines' => 'Allergy relief medications for hay fever and urticaria.',
        'Cough & Cold' => 'Cough suppressants, expectorants, and cold remedies.',
        'Vitamins & Supplements' => 'Multivitamins, minerals, and dietary supplements.',
        'Dermatologicals' => 'Topical treatments for skin conditions and infections.',
        'Cardiovascular' => 'Medications for hypertension, cholesterol, and heart health.',
        'Diabetes Care' => 'Anti-diabetic medications and glucose monitoring supplies.',
        'Gastrointestinal' => 'Treatments for digestive disorders and IBS.',
        'Respiratory' => 'Inhalers and medications for asthma and COPD.',
        'Neurology' => 'Medications for epilepsy, migraine, and neurological disorders.',
        'Psychiatry' => 'Antidepressants, anxiolytics, and mood stabilizers.',
        'Ophthalmology' => 'Eye drops and ophthalmic preparations.',
        'Hormonal' => 'Hormone replacement therapies and contraceptives.',
        'Antifungals' => 'Fungal infection treatments for skin and systemic use.',
        'Anti-inflammatories' => 'NSAIDs and corticosteroids for inflammation.',
        'Pediatric' => 'Children-specific formulations and dosages.',
        'Oncology' => 'Cancer treatment medications and supportive care.',
        'Vaccines' => 'Preventive vaccines and immunoglobulins.',
    ];

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(array_keys(static::$categories));
        $description = static::$categories[$name];

        return [
            'name' => $name,
            'description' => $description,
        ];
    }
}
