<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ProductCategorySeeder::class,
            IllnessCategorySeeder::class,
            ProductSeeder::class,
            RewardRuleSeeder::class,
            ShareholderMasterSeeder::class,
            NoticeSeeder::class,
            CampaignSeeder::class,
            DemoUserSeeder::class,
        ]);
    }
}
