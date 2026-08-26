<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('providers')->insert([
            [
                'name' => 'GPT-4o',
                'code' => 'gpt-4o',
                'provider_company' => 'OpenAI',
                'provider_company_code' => 'openai',
                'description' => 'OpenAI GPT-4o language model.',
                'capabilities' => json_encode(['text_generation']),
                'billing_unit' => 'token',
                'billing_granularity' => 1000,
                'allow_decimals' => 0,
                'input_rate' => 0.150000,
                'output_rate' => 0.100000,
                'rate' => null,
                'multipliers' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Claude 3.5 Sonnet',
                'code' => 'claude-3-5-sonnet',
                'provider_company' => 'Anthropic',
                'provider_company_code' => 'anthropic',
                'description' => 'Anthropic Claude model.',
                'capabilities' => json_encode(['text_generation']),
                'billing_unit' => 'token',
                'billing_granularity' => 1000,
                'allow_decimals' => 0,
                'input_rate' => 0.200000,
                'output_rate' => 0.150000,
                'rate' => null,
                'multipliers' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meta API',
                'code' => 'meta-api',
                'provider_company' => 'Meta',
                'provider_company_code' => 'meta',
                'description' => 'WhatsApp Cloud API by Meta.',
                'capabilities' => json_encode(['messaging']),
                'billing_unit' => 'call',
                'billing_granularity' => 1,
                'allow_decimals' => 0,
                'input_rate' => null,
                'output_rate' => null,
                'rate' => 0.050000,
                'multipliers' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
