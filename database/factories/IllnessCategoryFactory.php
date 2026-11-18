<?php

namespace Database\Factories;

use App\Models\IllnessCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class IllnessCategoryFactory extends Factory
{
    protected $model = IllnessCategory::class;

    private static array $categories = [
        'Infectious Diseases' => 'Bacterial, viral, fungal and parasitic infections.',
        'Cardiovascular' => 'Heart disease, hypertension, stroke, and vascular conditions.',
        'Respiratory' => 'Asthma, COPD, pneumonia, and other lung conditions.',
        'Gastrointestinal' => 'Gastritis, ulcers, IBS, and digestive disorders.',
        'Neurological' => 'Epilepsy, migraine, Parkinson\'s, and nerve disorders.',
        'Endocrine' => 'Diabetes, thyroid disorders, and hormonal imbalances.',
        'Musculoskeletal' => 'Arthritis, osteoporosis, and muscle-related conditions.',
        'Dermatological' => 'Skin infections, eczema, psoriasis, and dermatitis.',
        'Psychiatric' => 'Depression, anxiety, bipolar disorder, and schizophrenia.',
        'Ophthalmological' => 'Eye infections, glaucoma, and vision disorders.',
        'ENT' => 'Ear, nose, and throat infections and disorders.',
        'Urological' => 'Urinary tract infections, kidney stones, and prostate issues.',
        'Gynecological' => 'Menstrual disorders, infections, and reproductive health.',
        'Oncological' => 'Cancer and related treatment conditions.',
        'Hematological' => 'Anemia, blood clotting disorders, and hemoglobinopathies.',
        'Autoimmune' => 'Rheumatoid arthritis, lupus, and immune system disorders.',
        'Metabolic' => 'Obesity, hyperlipidemia, and metabolic syndrome.',
        'Allergic' => 'Allergies, hay fever, food allergies, and anaphylaxis.',
        'Pediatric' => 'Childhood illnesses, growth disorders, and vaccinations.',
        'Geriatric' => 'Age-related conditions, dementia, and osteoporosis.',
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
