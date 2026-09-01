<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PricingCategory;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Component;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Default User
        $user = User::firstOrCreate(
            ['email' => 'test@autoappy.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
            ]
        );

        // 2. Pricing Categories (with deterministic UUIDs)
        $catExpertFee = PricingCategory::create([
            'id'          => '11111111-1111-1111-1111-111111111101',
            'name'        => 'Expert Fee',
            'code'        => 'expert_fee',
            'description' => 'Manual intervention and setup by experts.',
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        $catThirdParty = PricingCategory::create([
            'id'          => '11111111-1111-1111-1111-111111111102',
            'name'        => 'Third Party',
            'code'        => 'third_party',
            'description' => 'Usage-based AI API and external services.',
            'is_active'   => true,
            'sort_order'  => 2,
        ]);

        $catInfra = PricingCategory::create([
            'id'          => '11111111-1111-1111-1111-111111111103',
            'name'        => 'Infrastructure',
            'code'        => 'infrastructure',
            'description' => 'Hosting and computing costs.',
            'is_active'   => true,
            'sort_order'  => 3,
        ]);

        $catMisc = PricingCategory::create([
            'id'          => '11111111-1111-1111-1111-111111111104',
            'name'        => 'Miscellaneous',
            'code'        => 'miscellaneous',
            'description' => 'Other costs.',
            'is_active'   => true,
            'sort_order'  => 4,
        ]);

        // 3. Plans
        $planStarter = Plan::create([
            'id'          => '22222222-2222-2222-2222-222222222201',
            'name'        => 'Starter',
            'code'        => 'starter',
            'price'       => 0.00,
            'max_tasks'   => 50,
            'description' => 'Starter plan for small tasks.',
            'features'    => ['Basic Support', '50 Tasks/month'],
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        $planGrowth = Plan::create([
            'id'          => '22222222-2222-2222-2222-222222222202',
            'name'        => 'Growth Pro',
            'code'        => 'growth',
            'price'       => 999.00,
            'max_tasks'   => 500,
            'description' => 'Growth plan for scaling.',
            'features'    => ['Standard Support', '500 Tasks/month', 'Priority Queue'],
            'is_active'   => true,
            'sort_order'  => 2,
        ]);

        $planPro = Plan::create([
            'id'          => '22222222-2222-2222-2222-222222222203',
            'name'        => 'Professional',
            'code'        => 'professional',
            'price'       => 1999.00,
            'max_tasks'   => null,
            'description' => 'Professional plan with unlimited tasks.',
            'features'    => ['Priority Support', 'Unlimited Tasks', 'Dedicated Account Manager'],
            'is_active'   => true,
            'sort_order'  => 3,
        ]);

        $planEnterprise = Plan::create([
            'id'          => '22222222-2222-2222-2222-222222222204',
            'name'        => 'Enterprise',
            'code'        => 'enterprise',
            'price'       => 4999.00,
            'max_tasks'   => null,
            'description' => 'Enterprise customized solutions.',
            'features'    => ['VIP 24/7 Support', 'Custom Integrations', 'SLA Guarantee'],
            'is_active'   => true,
            'sort_order'  => 4,
        ]);

        // 4. Providers
        $provGpt4o = Provider::create([
            'id'                    => '33333333-3333-3333-3333-333333333301',
            'name'                  => 'GPT-4o',
            'code'                  => 'gpt-4o',
            'provider_company'      => 'OpenAI',
            'provider_company_code' => 'openai',
            'description'           => 'OpenAI GPT-4o multimodal model.',
            'capabilities'          => ['text_generation'],
            'billing_unit'          => 'token',
            'billing_granularity'   => 1000,
            'input_rate'            => 0.150000,
            'output_rate'           => 0.100000,
            'rate'                  => null,
            'is_active'             => true,
        ]);

        $provClaude = Provider::create([
            'id'                    => '33333333-3333-3333-3333-333333333302',
            'name'                  => 'Claude 3.5 Sonnet',
            'code'                  => 'claude-3-5-sonnet',
            'provider_company'      => 'Anthropic',
            'provider_company_code' => 'anthropic',
            'description'           => 'Anthropic Claude 3.5 Sonnet model.',
            'capabilities'          => ['text_generation'],
            'billing_unit'          => 'token',
            'billing_granularity'   => 1000,
            'input_rate'            => 0.200000,
            'output_rate'           => 0.150000,
            'rate'                  => null,
            'is_active'             => true,
        ]);

        $provMeta = Provider::create([
            'id'                    => '33333333-3333-3333-3333-333333333303',
            'name'                  => 'Meta API',
            'code'                  => 'meta-api',
            'provider_company'      => 'Meta',
            'provider_company_code' => 'meta',
            'description'           => 'WhatsApp Cloud API by Meta.',
            'capabilities'          => ['messaging'],
            'billing_unit'          => 'call',
            'billing_granularity'   => 1,
            'rate'                  => 0.050000,
            'is_active'             => true,
        ]);

        $provElevenLabs = Provider::create([
            'id'                    => '33333333-3333-3333-3333-333333333304',
            'name'                  => 'ElevenLabs',
            'code'                  => 'elevenlabs',
            'provider_company'      => 'ElevenLabs',
            'provider_company_code' => 'elevenlabs',
            'description'           => 'AI voice generation and text-to-speech.',
            'capabilities'          => ['voice_generation', 'text_to_speech'],
            'billing_unit'          => 'character',
            'billing_granularity'   => 1000,
            'rate'                  => 0.300000,
            'is_active'             => true,
        ]);

        // 5. Components — Bundle 1: WhatsApp Automation
        $waBundleId = '44444444-4444-4444-4444-444444444401';
        $waBundle = Component::create([
            'id'                    => $waBundleId,
            'parent_id'             => null,
            'name'                  => 'WhatsApp Automation',
            'description'           => 'Complete WhatsApp customer interaction suite',
            'is_bundle'             => true,
            'is_leaf'               => false,
            'platform'              => 'WhatsApp',
            'category'              => 'support',
            'expert_fee_mode'       => 'COMPONENT_LEVEL',
            'automation_expert_fee' => 0.00,
            'is_active'             => true,
            'sort_order'            => 1,
        ]);

        // WhatsApp Setup Group
        $waSetupGroupId = '44444444-4444-4444-4444-444444444402';
        Component::create([
            'id'          => $waSetupGroupId,
            'parent_id'   => [$waBundleId],
            'name'        => 'WhatsApp Setup',
            'description' => 'Initial account creation and webhook configuration',
            'is_bundle'   => false,
            'is_leaf'     => false,
            'is_active'   => true,
            'sort_order'  => 1,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444403',
            'parent_id'           => [$waSetupGroupId],
            'name'                => 'WhatsApp Business Setup',
            'description'         => 'Business account verification and profile configuration',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catExpertFee->id,
            'pricing_method'      => 'fixed',
            'billing_type'        => 'ONE_TIME',
            'unit'                => 'setup',
            'unit_price'          => 5000.00,
            'quantity'            => 1,
            'is_active'           => true,
            'sort_order'          => 1,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444404',
            'parent_id'           => [$waSetupGroupId],
            'name'                => 'API Integration',
            'description'         => 'Connecting webhooks to CRM',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catExpertFee->id,
            'pricing_method'      => 'fixed',
            'billing_type'        => 'ONE_TIME',
            'unit'                => 'integration',
            'unit_price'          => 3000.00,
            'quantity'            => 1,
            'is_active'           => true,
            'sort_order'          => 2,
        ]);

        // Message Automation Group
        $msgGroupId = '44444444-4444-4444-4444-444444444405';
        Component::create([
            'id'          => $msgGroupId,
            'parent_id'   => [$waBundleId],
            'name'        => 'Message Automation',
            'description' => 'Automated standard replies',
            'is_bundle'   => false,
            'is_leaf'     => false,
            'is_active'   => true,
            'sort_order'  => 2,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444406',
            'parent_id'           => [$msgGroupId],
            'name'                => 'Welcome Message',
            'description'         => 'Automatic welcome to new users',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'qty_unit',
            'billing_type'        => 'RECURRING',
            'unit'                => 'message',
            'quantity'            => 1000,
            'available_providers' => [['is_default' => true, 'provider_id' => $provMeta->id]],
            'is_active'           => true,
            'sort_order'          => 1,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444407',
            'parent_id'           => [$msgGroupId],
            'name'                => 'Auto Reply',
            'description'         => 'Keyword-based auto replies',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'qty_unit',
            'billing_type'        => 'RECURRING',
            'unit'                => 'message',
            'quantity'            => 2000,
            'available_providers' => [['is_default' => true, 'provider_id' => $provMeta->id]],
            'is_active'           => true,
            'sort_order'          => 2,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444408',
            'parent_id'           => [$msgGroupId],
            'name'                => 'Away Message',
            'description'         => 'Out of office replies',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'qty_unit',
            'billing_type'        => 'RECURRING',
            'unit'                => 'message',
            'quantity'            => 500,
            'available_providers' => [['is_default' => true, 'provider_id' => $provMeta->id]],
            'is_active'           => true,
            'sort_order'          => 3,
        ]);

        // Lead Automation Group
        $leadGroupId = '44444444-4444-4444-4444-444444444409';
        Component::create([
            'id'          => $leadGroupId,
            'parent_id'   => [$waBundleId],
            'name'        => 'Lead Automation',
            'description' => 'Lead capture and notifications',
            'is_bundle'   => false,
            'is_leaf'     => false,
            'is_active'   => true,
            'sort_order'  => 3,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444410',
            'parent_id'           => [$leadGroupId],
            'name'                => 'Lead Capture',
            'description'         => 'Extract details from chat',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'qty_unit',
            'billing_type'        => 'RECURRING',
            'unit'                => 'capture',
            'unit_price'          => 1.50,
            'quantity'            => 1000,
            'is_active'           => true,
            'sort_order'          => 1,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444411',
            'parent_id'           => [$leadGroupId],
            'name'                => 'Lead Notification',
            'description'         => 'Notify team on new leads',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'qty_unit',
            'billing_type'        => 'RECURRING',
            'unit'                => 'notification',
            'unit_price'          => 0.50,
            'quantity'            => 1000,
            'is_active'           => true,
            'sort_order'          => 2,
        ]);

        // Conversation Management Group
        $convGroupId = '44444444-4444-4444-4444-444444444412';
        Component::create([
            'id'          => $convGroupId,
            'parent_id'   => [$waBundleId],
            'name'        => 'Conversation Management',
            'description' => 'AI based conversation handling',
            'is_bundle'   => false,
            'is_leaf'     => false,
            'is_active'   => true,
            'sort_order'  => 4,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444413',
            'parent_id'           => [$convGroupId],
            'name'                => 'Basic Chatbot',
            'description'         => 'AI Chatbot handling basic queries',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'usage_estimation',
            'billing_type'        => 'RECURRING',
            'unit'                => 'token',
            'quantity'            => 100000,
            'available_providers' => [
                ['is_default' => true, 'provider_id' => $provGpt4o->id],
                ['is_default' => false, 'provider_id' => $provClaude->id],
            ],
            'is_active'           => true,
            'sort_order'          => 1,
        ]);

        // 6. Components — Bundle 2: Instagram Reels Automation
        $igBundleId = '44444444-4444-4444-4444-444444444420';
        Component::create([
            'id'                    => $igBundleId,
            'parent_id'             => null,
            'name'                  => 'Instagram Reels Automation',
            'description'           => 'Automated viral short-form video generation',
            'is_bundle'             => true,
            'is_leaf'               => false,
            'platform'              => 'Instagram',
            'category'              => 'marketing',
            'expert_fee_mode'       => 'AUTOMATION_LEVEL',
            'automation_expert_fee' => 1500.00,
            'is_active'             => true,
            'sort_order'            => 2,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444421',
            'parent_id'           => [$igBundleId],
            'name'                => 'Video Scripting',
            'description'         => 'AI-powered reel script generator',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'fixed',
            'billing_type'        => 'RECURRING',
            'unit'                => 'script',
            'unit_price'          => 500.00,
            'quantity'            => 1,
            'is_active'           => true,
            'sort_order'          => 1,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444422',
            'parent_id'           => [$igBundleId],
            'name'                => 'Voiceover Generation',
            'description'         => 'Realistic AI voice synthesis',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catThirdParty->id,
            'pricing_method'      => 'usage_estimation',
            'billing_type'        => 'RECURRING',
            'unit'                => 'character',
            'quantity'            => 2500,
            'available_providers' => [['is_default' => true, 'provider_id' => $provElevenLabs->id]],
            'is_active'           => true,
            'sort_order'          => 2,
        ]);

        Component::create([
            'id'                  => '44444444-4444-4444-4444-444444444423',
            'parent_id'           => [$igBundleId],
            'name'                => 'Video Assembly & Rendering',
            'description'         => 'Cloud GPU rendering and subtitle overlay',
            'is_bundle'           => false,
            'is_leaf'             => true,
            'pricing_category_id' => $catInfra->id,
            'pricing_method'      => 'fixed',
            'billing_type'        => 'RECURRING',
            'unit'                => 'video',
            'unit_price'          => 1725.00,
            'quantity'            => 1,
            'is_active'           => true,
            'sort_order'          => 3,
        ]);

        // 7. Components — Bundle 3: Landing Page Automation (Loaded from JSON)
        $landingPageJsonPath = database_path('data/landing_page_automation.json');
        if (file_exists($landingPageJsonPath)) {
            $landingPageData = json_decode(file_get_contents($landingPageJsonPath), true);
            if ($landingPageData) {
                Component::create($landingPageData['bundle']);
                foreach ($landingPageData['groups_and_components'] as $item) {
                    Component::create($item);
                }
            }
        }
    }
}