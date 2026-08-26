<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;

class PricingController extends Controller
{
    /**
     * Calculate quotation pricing.
     * Endpoint: POST /api/v1/pricing/calculate
     */
    public function calculate(Request $request)
    {
        $payload = $request->validate([
            'automationId' => 'required|string',
            'expertFeeStrategy' => 'nullable|string',
            'automationExpertFeeAmount' => 'nullable|numeric',
            'components' => 'required|array',
            'selectedPlanId' => 'nullable|string'
        ]);

        $subtotal = 0;
        $expertFeeTotal = 0;
        $thirdPartyTotal = 0;
        $infrastructureTotal = 0;

        $breakdown = [];

        // Determine plan fee
        $planFee = 0;
        if (!empty($payload['selectedPlanId'])) {
            $plan = Plan::where('code', $payload['selectedPlanId'])->first();
            if ($plan) {
                $planFee = (float) $plan->price;
            } else if ($payload['selectedPlanId'] === 'starter') {
                $planFee = 499; // Fallback mock value
            }
        }

        // Process components
        foreach ($payload['components'] as $comp) {
            $compSubtotal = 0;
            $compCategories = [];

            if (isset($comp['pricing']['categories'])) {
                foreach ($comp['pricing']['categories'] as $category) {
                    $catTotal = 0;
                    
                    if (isset($category['items'])) {
                        foreach ($category['items'] as $item) {
                            $itemTotal = 0;
                            // Basic fixed price handling
                            if (isset($item['unitPrice']) && isset($item['quantity'])) {
                                $itemTotal = $item['unitPrice'] * $item['quantity'];
                            }
                            
                            // Metered pricing handling
                            if (isset($item['meters'])) {
                                foreach ($item['meters'] as $meter) {
                                    $qty = $meter['quantity'] ?? 0;
                                    $rate = $meter['rate'] ?? 0;
                                    $itemTotal += ($qty * $rate);
                                }
                            }

                            // Dimension Multipliers (Factors)
                            $multiplier = 1;
                            if (isset($item['factors'])) {
                                // Simplified mockup logic for multipliers
                                foreach ($item['factors'] as $factor) {
                                    if ($factor['key'] === 'quality' && $factor['value'] === '1080p') {
                                        // $multiplier *= 1.0; 
                                    } elseif ($factor['key'] === 'quality' && strtolower($factor['value']) === '4k') {
                                        $multiplier *= 2.0;
                                    }
                                }
                            }
                            
                            $itemTotal *= $multiplier;
                            $catTotal += $itemTotal;
                        }
                    }

                    // Cycle multiplication
                    $cycles = $comp['cycles'] ?? 1;
                    $catTotal *= $cycles;

                    // Distribute to running totals
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

            // Automation-level expert fee override
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
        $taxRate = 0.18; // 18% GST
        $taxableAmount = $subtotal + $planFee;
        $taxGSTAmount = round($taxableAmount * $taxRate, 2);
        
        $grandTotal = $taxableAmount + $taxGSTAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'automationId' => $payload['automationId'],
                'subtotal' => $subtotal,
                'expertFeeTotal' => $expertFeeTotal,
                'thirdPartyTotal' => $thirdPartyTotal,
                'infrastructureTotal' => $infrastructureTotal,
                'planFee' => $planFee,
                'taxGSTAmount' => $taxGSTAmount,
                'taxRate' => $taxRate,
                'grandTotal' => $grandTotal,
                'currency' => 'INR',
                'breakdown' => $breakdown
            ]
        ]);
    }
}
