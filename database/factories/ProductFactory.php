<?php

namespace Database\Factories;

use App\Models\IllnessCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static array $brands = [
        'Napa' => 'Paracetamol',
        'Fexo' => 'Fexofenadine',
        'Omepra' => 'Omeprazole',
        'Amlo' => 'Amlodipine',
        'Metro' => 'Metronidazole',
        'Cipro' => 'Ciprofloxacin',
        'Doxy' => 'Doxycycline',
        'Rani' => 'Ranitidine',
        'Salbo' => 'Salbutamol',
        'Glimi' => 'Glimepiride',
        'Eso' => 'Esomeprazole',
        'Ceta' => 'Cetirizine',
        'Montel' => 'Montelukast',
        'Atorva' => 'Atorvastatin',
        'Losar' => 'Losartan',
        'Pantop' => 'Pantoprazole',
        'Flu' => 'Fluconazole',
        'Acyclo' => 'Acyclovir',
        'Predni' => 'Prednisolone',
        'Dia' => 'Diazepam',
    ];

    private static array $strengths = ['250mg', '500mg', '1g', '5mg', '10mg', '20mg', '40mg', '50mg', '100mg', '200mg', '2.5mg', '7.5mg'];

    private static array $dosageForms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Ointment', 'Drops', 'Inhaler', 'Suspension'];

    private static array $packSizes = ['10\'s', '20\'s', '30\'s', '50\'s', '100\'s', '5ml', '10ml', '30ml', '50ml', '100ml'];

    public function definition(): array
    {
        $brandName = $this->faker->unique()->randomElement(array_keys(static::$brands));
        $genericName = static::$brands[$brandName];
        $sku = strtoupper(Str::random(2)).'-'.$this->faker->unique()->numberBetween(1000, 99999);

        return [
            'sku' => $sku,
            'brand_name' => $brandName,
            'generic_name' => $genericName,
            'strength' => $this->faker->randomElement(static::$strengths),
            'dosage_form' => $this->faker->randomElement(static::$dosageForms),
            'pack_size' => $this->faker->randomElement(static::$packSizes),
            'product_category_id' => ProductCategory::factory(),
            'illness_category_id' => IllnessCategory::factory(),
            'mrp' => $this->faker->randomFloat(2, 10, 500),
            'image' => null,
            'launch_status' => $this->faker->randomElement(['launched', 'pending', 'discontinued']),
            'is_active' => $this->faker->boolean(85),
            'description' => $this->faker->sentence(10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withCategory(ProductCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'product_category_id' => $category->id,
        ]);
    }

    public function withIllnessCategory(IllnessCategory $illnessCategory): static
    {
        return $this->state(fn (array $attributes) => [
            'illness_category_id' => $illnessCategory->id,
        ]);
    }
}
