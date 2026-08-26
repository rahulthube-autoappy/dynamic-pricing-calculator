<?php

namespace App\Services;

class PricingEngineService
{
    /**
     * Recursive function to compute a single tree node
     */
    public function processNode($node, &$summary, &$keyPoints, $allProviders)
    {
        $calculatedPrice = 0;
        $expandedProviders = null;
        $selectedProviderId = null;
        
        $nodeArray = [
            'id' => $node->id,
            'uuid' => $node->uuid,
            'parent_id' => $node->parent_id,
            'name' => $node->name,
            'description' => $node->description,
            'is_bundle' => (bool) $node->is_bundle,
            'is_leaf' => (bool) $node->is_leaf,
            'pricing_category_id' => $node->pricing_category_id,
            'pricing_method' => $node->pricing_method,
            'billing_type' => $node->billing_type,
            'unit' => $node->unit,
            'unit_price' => $node->unit_price ? (float) $node->unit_price : null,
            'quantity' => $node->quantity ? (float) $node->quantity : null,
            'sort_order' => $node->sort_order,
        ];

        // Metadata merging for leaf nodes
        if ($node->is_leaf && $node->metadata) {
            $meta = is_string($node->metadata) ? json_decode($node->metadata, true) : $node->metadata;
            if (is_array($meta)) {
                $nodeArray = array_merge($nodeArray, $meta);
            }
        }

        if ($node->is_leaf) {
            $keyPoints[] = $node->name;

            // Expand providers
            if ($node->available_providers) {
                $providers = is_string($node->available_providers) ? json_decode($node->available_providers, true) : $node->available_providers;
                $expandedProviders = [];
                
                if (is_array($providers)) {
                    foreach ($providers as $p) {
                        $provider = $allProviders->get($p['provider_id']);
                        if ($provider) {
                            $compositeUnitPrice = null;
                            if ($provider->rate !== null) {
                                $compositeUnitPrice = (float) $provider->rate;
                            } else if ($provider->input_rate !== null || $provider->output_rate !== null) {
                                $compositeUnitPrice = (((float)$provider->input_rate) + ((float)$provider->output_rate)) / 2;
                            }

                            $expanded = [
                                'provider_id' => $provider->id,
                                'provider_key' => $provider->provider_company_code,
                                'provider_name' => $provider->provider_company,
                                'model_id' => $provider->code,
                                'model_name' => $provider->name,
                                'unit_price' => $compositeUnitPrice,
                                'billing_unit' => $provider->billing_unit,
                                'billing_granularity' => (int) $provider->billing_granularity,
                                'is_default' => $p['is_default'] ?? false,
                            ];
                            
                            $expandedProviders[] = $expanded;

                            if ($expanded['is_default']) {
                                $selectedProviderId = $provider->id;
                                $calculatedPrice = (($nodeArray['quantity'] ?? 1) / ($expanded['billing_granularity'] ?: 1)) * ($compositeUnitPrice ?? 0);
                            }
                        }
                    }
                }
            }

            if ($nodeArray['unit_price'] !== null) {
                $calculatedPrice = $nodeArray['unit_price'] * ($nodeArray['quantity'] ?? 1);
            }

            if ($selectedProviderId) {
                $nodeArray['selected_provider_id'] = $selectedProviderId;
                $nodeArray['available_providers'] = $expandedProviders;
                $defaultProv = collect($expandedProviders)->firstWhere('is_default', true);
                if ($defaultProv) {
                    $nodeArray['billing_granularity'] = $defaultProv['billing_granularity'];
                }
            } else {
                $nodeArray['available_providers'] = null;
            }

            if ($node->billing_type === 'ONE_TIME') {
                $summary['one_time_total'] += $calculatedPrice;
            } else if ($node->billing_type === 'RECURRING') {
                $summary['recurring_monthly_subtotal'] += $calculatedPrice;
            }
        }

        $formattedChildren = [];
        if ($node->children && $node->children->count() > 0) {
            foreach ($node->children as $child) {
                $formattedChild = $this->processNode($child, $summary, $keyPoints, $allProviders);
                $calculatedPrice += $formattedChild['calculated_price'];
                $formattedChildren[] = $formattedChild;
            }
        }
        
        $nodeArray['calculated_price'] = round($calculatedPrice, 2);
        $nodeArray['children'] = $formattedChildren;

        return $nodeArray;
    }

    /**
     * Compute full pricing quotation
     */
    public function calculateQuotationPricing(array $payload, float $planFee)
    {
        $expertFeeTotal = 0;
        $thirdPartyTotal = 0;
        $infrastructureTotal = 0;
        $breakdown = [];

        foreach ($payload['components'] as $comp) {
            $compSubtotal = 0;
            $compCategories = [];

            if (isset($comp['pricing']['categories'])) {
                foreach ($comp['pricing']['categories'] as $category) {
                    $catTotal = 0;
                    
                    if (isset($category['items'])) {
                        foreach ($category['items'] as $item) {
                            $itemTotal = 0;
                            if (isset($item['unitPrice']) && isset($item['quantity'])) {
                                $itemTotal = $item['unitPrice'] * $item['quantity'];
                            }
                            
                            if (isset($item['meters'])) {
                                foreach ($item['meters'] as $meter) {
                                    $qty = $meter['quantity'] ?? 0;
                                    $rate = $meter['rate'] ?? 0;
                                    $itemTotal += ($qty * $rate);
                                }
                            }

                            $multiplier = 1;
                            if (isset($item['factors'])) {
                                foreach ($item['factors'] as $factor) {
                                    if ($factor['key'] === 'quality' && strtolower($factor['value']) === '4k') {
                                        $multiplier *= 2.0;
                                    }
                                }
                            }
                            
                            $itemTotal *= $multiplier;
                            $catTotal += $itemTotal;
                        }
                    }

                    $cycles = $comp['cycles'] ?? 1;
                    $catTotal *= $cycles;

                    if ($category['type'] === 'expert_fee') {
                        $expertFeeTotal += $catTotal;
                    } elseif (isset($category['isAiCategory']) && $category['isAiCategory']) {
                        $thirdPartyTotal += $catTotal;
                    }

                    $compSubtotal += $catTotal;
                    
                    $compCategories[] = [
                        'categoryId' => $category['id'] ?? $category['type'],
                        'categoryName' => $category['name'],
                        'total' => $catTotal
                    ];
                }
            }

            if ($payload['expertFeeStrategy'] === 'PER_AUTOMATION') {
                $expertFeeTotal = (float) ($payload['automationExpertFeeAmount'] ?? 0);
            }

            $breakdown[] = [
                'componentId' => $comp['id'],
                'name' => $comp['name'],
                'cycles' => $comp['cycles'] ?? 1,
                'componentSubtotal' => $compSubtotal,
                'categories' => $compCategories
            ];
        }

        $subtotal = $expertFeeTotal + $thirdPartyTotal + $infrastructureTotal;
        $taxRate = 0.18;
        $taxableAmount = $subtotal + $planFee;
        $taxGSTAmount = round($taxableAmount * $taxRate, 2);
        $grandTotal = $taxableAmount + $taxGSTAmount;

        return [
            'subtotal' => $subtotal,
            'expertFeeTotal' => $expertFeeTotal,
            'thirdPartyTotal' => $thirdPartyTotal,
            'infrastructureTotal' => $infrastructureTotal,
            'planFee' => $planFee,
            'taxGSTAmount' => $taxGSTAmount,
            'taxRate' => $taxRate,
            'grandTotal' => $grandTotal,
            'breakdown' => $breakdown
        ];
    }
}
