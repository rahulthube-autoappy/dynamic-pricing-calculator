<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WhatsAppAutomationSeeder extends Seeder
{
    public function run(): void
    {
        $expertFeeCategoryId = DB::table('pricing_categories')->where('code', 'expert_fee')->value('id');
        $thirdPartyCategoryId = DB::table('pricing_categories')->where('code', 'third_party')->value('id');
        
        $metaProviderId = DB::table('providers')->where('code', 'meta-api')->value('id');
        $gptProviderId = DB::table('providers')->where('code', 'gpt-4o')->value('id');
        $claudeProviderId = DB::table('providers')->where('code', 'claude-3-5-sonnet')->value('id');

        // Root Bundle: WhatsApp Automation
        $rootId = DB::table('components')->insertGetId([
            'uuid' => Str::uuid(),
            'parent_id' => null,
            'name' => 'WhatsApp Automation',
            'description' => 'Complete WhatsApp Automation Setup and Management',
            'is_bundle' => true,
            'is_leaf' => false,
            'platform' => 'WhatsApp',
            'category' => 'communication',
            'expert_fee_mode' => 'COMPONENT_LEVEL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 1: WhatsApp Setup
        $setupId = DB::table('components')->insertGetId([
            'uuid' => Str::uuid(),
            'parent_id' => $rootId,
            'name' => 'WhatsApp Setup',
            'description' => 'Initial setup and integration',
            'is_bundle' => false,
            'is_leaf' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 2 (Leaves for Setup)
        DB::table('components')->insert([
            [
                'uuid' => Str::uuid(),
                'parent_id' => $setupId,
                'name' => 'WhatsApp Business Account Setup',
                'description' => 'Configure business profile and settings',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $expertFeeCategoryId,
                'pricing_method' => 'fixed',
                'billing_type' => 'ONE_TIME',
                'unit' => 'setup',
                'unit_price' => 5000.00, // INR
                'quantity' => 1,
                'available_providers' => null,
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 1, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $setupId,
                'name' => 'API / Platform Integration',
                'description' => 'Integrate Meta Cloud API with AutoAppy',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $expertFeeCategoryId,
                'pricing_method' => 'fixed',
                'billing_type' => 'ONE_TIME',
                'unit' => 'integration',
                'unit_price' => 3000.00, // INR
                'quantity' => 1,
                'available_providers' => null,
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 1, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Level 1: Message Automation
        $messageAutoId = DB::table('components')->insertGetId([
            'uuid' => Str::uuid(),
            'parent_id' => $rootId,
            'name' => 'Message Automation',
            'description' => 'Automated standard replies',
            'is_bundle' => false,
            'is_leaf' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $metaProviderJson = json_encode([
            ['provider_id' => $metaProviderId, 'is_default' => true]
        ]);

        // Level 2 (Leaves for Message Automation)
        DB::table('components')->insert([
            [
                'uuid' => Str::uuid(),
                'parent_id' => $messageAutoId,
                'name' => 'Welcome Message',
                'description' => 'Automatic welcome to new users',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $thirdPartyCategoryId,
                'pricing_method' => 'qty_unit',
                'billing_type' => 'RECURRING',
                'unit' => 'message',
                'unit_price' => null, // Comes from provider
                'quantity' => 1000,
                'available_providers' => $metaProviderJson,
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 100000, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $messageAutoId,
                'name' => 'Auto Reply',
                'description' => 'Keyword-based auto replies',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $thirdPartyCategoryId,
                'pricing_method' => 'qty_unit',
                'billing_type' => 'RECURRING',
                'unit' => 'message',
                'unit_price' => null, 
                'quantity' => 2000,
                'available_providers' => $metaProviderJson,
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 100000, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $messageAutoId,
                'name' => 'Away Message',
                'description' => 'Out of office replies',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $thirdPartyCategoryId,
                'pricing_method' => 'qty_unit',
                'billing_type' => 'RECURRING',
                'unit' => 'message',
                'unit_price' => null, 
                'quantity' => 500,
                'available_providers' => $metaProviderJson,
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 100000, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Level 1: Lead Automation
        $leadAutoId = DB::table('components')->insertGetId([
            'uuid' => Str::uuid(),
            'parent_id' => $rootId,
            'name' => 'Lead Automation',
            'description' => 'Lead capture and notifications',
            'is_bundle' => false,
            'is_leaf' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 2 (Leaves for Lead Automation)
        DB::table('components')->insert([
            [
                'uuid' => Str::uuid(),
                'parent_id' => $leadAutoId,
                'name' => 'Lead Capture',
                'description' => 'Extract details from chat',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $thirdPartyCategoryId,
                'pricing_method' => 'qty_unit',
                'billing_type' => 'RECURRING',
                'unit' => 'capture',
                'unit_price' => 1.50, // Custom fixed price
                'quantity' => 1000,
                'available_providers' => null, // Custom implementation
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 10000, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $leadAutoId,
                'name' => 'Lead Notification',
                'description' => 'Notify team on new leads',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $thirdPartyCategoryId,
                'pricing_method' => 'qty_unit',
                'billing_type' => 'RECURRING',
                'unit' => 'notification',
                'unit_price' => 0.50, 
                'quantity' => 1000,
                'available_providers' => null,
                'metadata' => json_encode(['min_quantity' => 1, 'max_quantity' => 10000, 'step' => 1, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Level 1: Conversation Management
        $convAutoId = DB::table('components')->insertGetId([
            'uuid' => Str::uuid(),
            'parent_id' => $rootId,
            'name' => 'Conversation Management',
            'description' => 'AI based conversation handling',
            'is_bundle' => false,
            'is_leaf' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $llmProvidersJson = json_encode([
            ['provider_id' => $gptProviderId, 'is_default' => true],
            ['provider_id' => $claudeProviderId, 'is_default' => false]
        ]);

        // Level 2 (Leaves for Conversation Management)
        DB::table('components')->insert([
            [
                'uuid' => Str::uuid(),
                'parent_id' => $convAutoId,
                'name' => 'Basic Chatbot',
                'description' => 'AI Chatbot handling basic queries',
                'is_bundle' => false,
                'is_leaf' => true,
                'pricing_category_id' => $thirdPartyCategoryId,
                'pricing_method' => 'usage_estimation', // LLMs use usage estimation
                'billing_type' => 'RECURRING',
                'unit' => 'token',
                'unit_price' => null, // Derived from provider
                'quantity' => 100000, // e.g. 100,000 tokens estimated
                'available_providers' => $llmProvidersJson,
                'metadata' => json_encode(['min_quantity' => 1000, 'max_quantity' => 10000000, 'step' => 1000, 'allow_decimals' => false]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
