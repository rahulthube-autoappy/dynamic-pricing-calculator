<?php

namespace App\Services;

use App\Models\QuotationNode;

class QuotationNodePricingEngine
{
    public function calculate(array $rootNodes): array
    {
        $summary = [
            'one_time_total'              => 0.0,
            'recurring_monthly_total'     => 0.0,
            'expert_fee_total'            => 0.0,
            'subtotal'                    => 0.0,
        ];
        $breakdown = [];

        foreach ($rootNodes as $rootNode) {
            $rootResult = $this->processNode($rootNode, $summary);
            $breakdown[] = $rootResult;
        }

        $summary['subtotal'] = $summary['one_time_total'] + $summary['recurring_monthly_total'] + $summary['expert_fee_total'];

        return [
            'summary'   => $summary,
            'breakdown' => $breakdown,
        ];
    }

    protected function processNode(QuotationNode $node, array &$summary): array
    {
        if (!$node->is_selected) {
            return $this->skippedNode($node);
        }

        $nodeArray = [
            'id'             => $node->id,
            'name'           => $node->name,
            'depth'          => $node->depth,
            'billing_type'   => $node->billing_type,
            'pricing_method' => $node->pricing_method,
            'unit'           => $node->unit,
            'quantity'       => $node->quantity,
            'unit_price'     => $node->unit_price,
            'is_selected'    => true,
            'calculated_price' => 0.0,
            'children'       => [],
        ];

        $children = $node->children ? $node->children->filter(fn($c) => true) : collect();
        $hasChildren = $children->count() > 0;

        if (!$hasChildren) {
            $price = $this->computeLeafPrice($node);
            $nodeArray['calculated_price'] = round($price, 2);
            $nodeArray['provider'] = $this->providerSummary($node);

            if ($node->billing_type === 'ONE_TIME') {
                $summary['one_time_total'] += $price;
            } elseif ($node->billing_type === 'RECURRING') {
                $summary['recurring_monthly_total'] += $price;
            }
        } else {
            $childTotal = 0.0;
            $formattedChildren = [];
            foreach ($children as $child) {
                $childResult = $this->processNode($child, $summary);
                $childTotal += $childResult['calculated_price'] ?? 0;
                $formattedChildren[] = $childResult;
            }
            $nodeArray['calculated_price'] = round($childTotal, 2);
            $nodeArray['children'] = $formattedChildren;
        }

        if ($node->expert_fee_mode && $node->automation_expert_fee > 0) {
            $expertFee = 0.0;
            if ($node->depth === 0 && $node->expert_fee_mode === 'AUTOMATION_LEVEL') {
                $expertFee = (float) $node->automation_expert_fee;
            } elseif ($node->depth === 1 && $node->expert_fee_mode === 'COMPONENT_LEVEL') {
                $expertFee = (float) $node->automation_expert_fee;
            }

            if ($expertFee > 0) {
                $summary['expert_fee_total'] += $expertFee;
                $nodeArray['expert_fee'] = round($expertFee, 2);
                $nodeArray['calculated_price'] = round(($nodeArray['calculated_price'] ?? 0) + $expertFee, 2);
            }
        }

        return $nodeArray;
    }

    protected function computeLeafPrice(QuotationNode $node): float
    {
        $quantity = (float) ($node->quantity ?? 1);
        $unitPrice = null;

        if ($node->unit_price !== null) {
            $unitPrice = (float) $node->unit_price;
        }

        if ($unitPrice === null && $node->selectedProvider) {
            $provider = $node->selectedProvider;
            if ($provider->rate !== null) {
                $unitPrice = (float) $provider->rate;
            } elseif ($provider->input_rate !== null) {
                $unitPrice = (((float)$provider->input_rate) + ((float)($provider->output_rate ?? 0))) / 2;
            }
            $unitPrice = $unitPrice ? $unitPrice * $this->getDimensionMultiplier($provider, $node->selected_dimensions) : 0;
        }

        if ($unitPrice === null) {
            return 0.0;
        }

        $granularity = $node->selectedProvider?->billing_granularity ?? 1;
        return ($quantity / max($granularity, 1)) * $unitPrice;
    }

    protected function getDimensionMultiplier($provider, ?array $dimensions): float
    {
        if (!$dimensions || !$provider->multipliers) {
            return 1.0;
        }
        $multipliers = is_array($provider->multipliers) ? $provider->multipliers : json_decode($provider->multipliers, true);
        $result = 1.0;
        foreach ($dimensions as $key => $value) {
            if (isset($multipliers[$key][$value])) {
                $result *= (float) $multipliers[$key][$value];
            }
        }
        return $result;
    }

    protected function providerSummary(QuotationNode $node): ?array
    {
        if (!$node->selectedProvider) {
            return null;
        }
        $p = $node->selectedProvider;
        return [
            'id'           => $p->id,
            'model_name'   => $p->name,
            'company'      => $p->provider_company,
            'billing_unit' => $p->billing_unit,
        ];
    }

    protected function skippedNode(QuotationNode $node): array
    {
        return [
            'id'               => $node->id,
            'name'             => $node->name,
            'depth'            => $node->depth,
            'is_selected'      => false,
            'calculated_price' => 0.0,
            'children'         => [],
        ];
    }
}