<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PharmacistQuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            [
                'name' => 'Medicine Knowledge',
                'description' => 'Fundamental pharmacology, indications, dosage ranges, and mechanism of actions of essential medicines.',
                'icon' => 'las la-pills',
                'color' => '#CD0B6F',
                'display_order' => 1,
            ],
            [
                'name' => 'OMPL Knowledge',
                'description' => 'Om Megashree Pharmaceuticals corporate standards, mission, GMP manufacturing ethos, and portfolio.',
                'icon' => 'las la-building',
                'color' => '#1E3A5F',
                'display_order' => 2,
            ],
            [
                'name' => 'Product Knowledge',
                'description' => 'Comprehensive formulations, brand names, generic compositions, and therapeutic indications of OMPL products.',
                'icon' => 'las la-prescription-bottle-alt',
                'color' => '#0D9488',
                'display_order' => 3,
            ],
            [
                'name' => 'Medicine Usage',
                'description' => 'Patient counseling protocols, route of administration, pediatric/geriatric adjustments, and adherence guidance.',
                'icon' => 'las la-capsules',
                'color' => '#3B82F6',
                'display_order' => 4,
            ],
            [
                'name' => 'Drug Interaction',
                'description' => 'Major drug-drug, drug-food, and drug-disease contraindications critical to dispensing safety.',
                'icon' => 'las la-exclamation-triangle',
                'color' => '#DC2626',
                'display_order' => 5,
            ],
            [
                'name' => 'Pharmacy Practice',
                'description' => 'Good Pharmacy Practice (GPP), inventory cold-chain storage standards, and professional ethics.',
                'icon' => 'las la-clinic-medical',
                'color' => '#F59E0B',
                'display_order' => 6,
            ],
            [
                'name' => 'General Pharmaceutical Knowledge',
                'description' => 'Department of Drug Administration (DDA) Nepal regulatory frameworks, schedules, and national health policies.',
                'icon' => 'las la-book-medical',
                'color' => '#8B5CF6',
                'display_order' => 7,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = QuizCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'display_order' => $cat['display_order'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Sample Quizzes
        $quiz1 = Quiz::firstOrCreate(
            ['slug' => 'antibiotic-stewardship-dispensing-2026'],
            [
                'quiz_category_id' => $categories['Drug Interaction']->id,
                'title' => 'Antibiotic Stewardship & Dispensing Guidelines',
                'description' => 'Evaluate your clinical knowledge on antibiotic selection, patient counseling, and minimizing microbial resistance.',
                'instructions' => 'Complete all questions within the allocated time. Passing score earns reward points credited directly to your pharmacy wallet.',
                'time_limit_minutes' => 10,
                'passing_percentage' => 70.00,
                'reward_points' => 75.00,
                'max_attempts' => 2,
                'is_randomized' => true,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        // 3. Question Bank Seed Items
        $bankQuestions = [
            [
                'quiz_id' => $quiz1->id,
                'quiz_category_id' => $categories['Medicine Knowledge']->id,
                'question_text' => 'Which of the following is the primary mechanism of action of Beta-lactam antibiotics (e.g. Amoxicillin)?',
                'difficulty' => 'easy',
                'explanation' => 'Beta-lactam antibiotics inhibit bacterial cell wall synthesis by binding to penicillin-binding proteins (PBPs).',
                'product_reference' => 'Amoxicillin 500mg',
                'points' => 1,
                'display_order' => 1,
                'options' => [
                    ['text' => 'Inhibition of bacterial cell wall synthesis', 'is_correct' => true],
                    ['text' => 'Inhibition of 50S ribosomal subunit', 'is_correct' => false],
                    ['text' => 'Disruption of fungal cell membrane', 'is_correct' => false],
                    ['text' => 'Inhibition of DNA gyrase enzyme', 'is_correct' => false],
                ],
            ],
            [
                'quiz_id' => $quiz1->id,
                'quiz_category_id' => $categories['Medicine Usage']->id,
                'question_text' => 'When dispensing Azithromycin, what is the most important counseling advice regarding food interaction?',
                'difficulty' => 'medium',
                'explanation' => 'Completing the full prescribed course is critical to preventing microbial resistance.',
                'product_reference' => 'Azithromycin 500mg',
                'points' => 1,
                'display_order' => 2,
                'options' => [
                    ['text' => 'Complete the full prescribed course even if symptoms resolve earlier', 'is_correct' => true],
                    ['text' => 'Take strictly with high-fat meals only', 'is_correct' => false],
                    ['text' => 'Double the next dose if a dose is missed', 'is_correct' => false],
                    ['text' => 'Store exclusively in freezer temperatures below 0°C', 'is_correct' => false],
                ],
            ],
            [
                'quiz_id' => null,
                'quiz_category_id' => $categories['Drug Interaction']->id,
                'question_text' => 'Which combination of cardiovascular and analgesic medications carries severe risk of gastrointestinal ulceration and bleeding?',
                'difficulty' => 'hard',
                'explanation' => 'Concomitant administration of oral anticoagulants/antiplatelets with non-steroidal anti-inflammatory drugs (NSAIDs) exponentially elevates GI bleeding incidence.',
                'product_reference' => 'Warfarin / Aspirin',
                'points' => 2,
                'display_order' => 3,
                'options' => [
                    ['text' => 'Warfarin with High-Dose Aspirin or Ibuprofen', 'is_correct' => true],
                    ['text' => 'Amlodipine with Paracetamol', 'is_correct' => false],
                    ['text' => 'Atorvastatin with Vitamin D3', 'is_correct' => false],
                    ['text' => 'Pantoprazole with Oral Rehydration Salts', 'is_correct' => false],
                ],
            ],
            [
                'quiz_id' => null,
                'quiz_category_id' => $categories['Pharmacy Practice']->id,
                'question_text' => 'What is the required standard cold-chain storage temperature range for biologicals, insulin, and vaccines under GPP?',
                'difficulty' => 'easy',
                'explanation' => 'Standard cold chain maintenance mandates 2°C to 8°C (36°F to 46°F) continuous refrigeration with monitored thermometer logging.',
                'product_reference' => 'Human Insulin / Vaccines',
                'points' => 1,
                'display_order' => 4,
                'options' => [
                    ['text' => '+2°C to +8°C', 'is_correct' => true],
                    ['text' => '-10°C to -2°C', 'is_correct' => false],
                    ['text' => '+15°C to +25°C (Controlled room temperature)', 'is_correct' => false],
                    ['text' => 'Ambient sunlight temperature', 'is_correct' => false],
                ],
            ],
            [
                'quiz_id' => null,
                'quiz_category_id' => $categories['General Pharmaceutical Knowledge']->id,
                'question_text' => 'According to Nepal Department of Drug Administration (DDA) regulatory schedule, which classification applies to Narcotic and Psychotropic drugs?',
                'difficulty' => 'medium',
                'explanation' => 'Schedule ‘Ka’ (क) encompasses narcotic and psychotropic substances demanding double-locked storage and prescription register archiving.',
                'product_reference' => 'Schedule Ka Controlled Substances',
                'points' => 1,
                'display_order' => 5,
                'options' => [
                    ['text' => 'Schedule Ka (क)', 'is_correct' => true],
                    ['text' => 'Schedule Kha (ख)', 'is_correct' => false],
                    ['text' => 'Schedule Ga (ग)', 'is_correct' => false],
                    ['text' => 'Over The Counter (OTC)', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($bankQuestions as $bq) {
            $options = $bq['options'];
            unset($bq['options']);

            $q = QuizQuestion::firstOrCreate(
                ['question_text' => $bq['question_text']],
                $bq
            );

            foreach ($options as $idx => $opt) {
                QuizQuestionOption::firstOrCreate(
                    [
                        'quiz_question_id' => $q->id,
                        'option_text' => $opt['text'],
                    ],
                    [
                        'is_correct' => $opt['is_correct'],
                        'display_order' => $idx + 1,
                    ]
                );
            }
        }
    }
}
