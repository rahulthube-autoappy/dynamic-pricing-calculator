<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plans')->insert([
            [
                'name' => 'Starter',
                'code' => 'starter',
                'price' => 0.00,
                'max_tasks' => 50,
                'description' => 'Starter plan for small tasks.',
                'features' => json_encode(['Basic Support', '50 Tasks/month']),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Growth',
                'code' => 'growth',
                'price' => 999.00,
                'max_tasks' => 500,
                'description' => 'Growth plan for scaling.',
                'features' => json_encode(['Standard Support', '500 Tasks/month']),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professional',
                'code' => 'professional',
                'price' => 1999.00,
                'max_tasks' => null, // unlimited
                'description' => 'Professional plan with unlimited tasks.',
                'features' => json_encode(['Priority Support', 'Unlimited Tasks']),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
