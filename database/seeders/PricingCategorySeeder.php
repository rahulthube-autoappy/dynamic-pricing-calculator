<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pricing_categories')->insert([
            [
                'name' => 'Expert Fee',
                'code' => 'expert_fee',
                'description' => 'Manual intervention and setup by experts.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Third Party',
                'code' => 'third_party',
                'description' => 'Usage-based AI API and external services.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Infrastructure',
                'code' => 'infrastructure',
                'description' => 'Hosting and computing costs.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Miscellaneous',
                'code' => 'miscellaneous',
                'description' => 'Other costs.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
