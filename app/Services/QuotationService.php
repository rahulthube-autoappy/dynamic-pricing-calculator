<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationNode;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationNodeRepository;
use App\Repositories\ComponentRepository;
use App\Repositories\ProviderRepository;
use App\Models\Component;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    protected QuotationRepository $repo;
    protected QuotationNodeRepository $nodeRepo;
    protected ComponentRepository $componentRepo;
    protected QuotationNodePricingEngine $pricingEngine;
    protected ProviderRepository $providerRepo;

    public function __construct(
        QuotationRepository $repo,
        QuotationNodeRepository $nodeRepo,
        ComponentRepository $componentRepo,
        QuotationNodePricingEngine $pricingEngine,
        ProviderRepository $providerRepo
    ) {
        $this->repo = $repo;
        $this->nodeRepo = $nodeRepo;
        $this->componentRepo = $componentRepo;
        $this->pricingEngine = $pricingEngine;
        $this->providerRepo = $providerRepo;
    }

    public function getByUser(int $userId, ?string $status = null, bool $includeArchived = false)
    {
        return $this->repo->getByUser($userId, $status, $includeArchived);
    }

    public function getById(string $id): Quotation
    {
        return $this->repo->getById($id);
    }

    public function create(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $quotation = $this->repo->create($data);

            if (!empty($data['source_component_id'])) {
                $bundle = $this->componentRepo->getBundleWithChildren($data['source_component_id']);
                $this->copyComponentTreeToNodes($bundle, $quotation->id, null, 0);
            }

            return $this->repo->getById($quotation->id);
        });
    }

    public function update(string $id, array $data): Quotation
    {
        return $this->repo->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    public function calculatePricing(string $quotationId): array
    {
        $quotation = $this->repo->getById($quotationId);
        
        $allNodes = QuotationNode::with('selectedProvider')
            ->where('quotation_id', $quotationId)
            ->orderBy('sort_order')
            ->get();

        foreach ($allNodes as $item) {
            $children = $allNodes->filter(fn($c) => $c->parent_node_id === $item->id)->values();
            $item->setRelation('children', $children);
        }

        $rootNodes = $allNodes->whereNull('parent_node_id')->values();

        $result = $this->pricingEngine->calculate($rootNodes->all());

        $planFee = 0.0;
        if ($quotation->selectedPlan) {
            $planFee = (float) $quotation->selectedPlan->price;
        }

        $summary = $result['summary'];
        $taxRate = 0.18;
        $taxableAmount = $summary['subtotal'] + $planFee;
        $taxTotal = round($taxableAmount * $taxRate, 2);
        $grandTotal = $taxableAmount + $taxTotal;

        return [
            'quotation_id'           => $quotationId,
            'currency'               => 'INR',
            'one_time_total'         => round($summary['one_time_total'], 2),
            'recurring_monthly_total'=> round($summary['recurring_monthly_total'], 2),
            'expert_fee_total'       => round($summary['expert_fee_total'], 2),
            'subtotal'               => round($summary['subtotal'], 2),
            'plan_fee'               => $planFee,
            'tax_rate'               => $taxRate,
            'tax_total'              => $taxTotal,
            'grand_total'            => round($grandTotal, 2),
            'breakdown'              => $result['breakdown'],
        ];
    }

    protected function copyComponentTreeToNodes($component, string $quotationId, ?string $parentNodeId, int $depth): void
    {
        $defaultProviderId = null;
        if ($component->available_providers && is_array($component->available_providers)) {
            $defaultConfig = collect($component->available_providers)->firstWhere('is_default', true) 
                ?? collect($component->available_providers)->first();
            $defaultProviderId = $defaultConfig['provider_id'] ?? null;
        }

        $node = QuotationNode::create([
            'quotation_id'          => $quotationId,
            'parent_node_id'        => $parentNodeId,
            'source_component_id'   => $component->id,
            'name'                  => $component->name,
            'description'           => $component->description,
            'depth'                 => $depth,
            'is_custom'             => false,
            'is_selected'           => true,
            'pricing_category_id'   => $component->pricing_category_id,
            'pricing_method'        => $component->pricing_method,
            'billing_type'          => $component->billing_type,
            'unit'                  => $component->unit,
            'quantity'              => $component->quantity,
            'unit_price'            => $component->unit_price,
            'selected_provider_id'  => $defaultProviderId,
            'expert_fee_mode'       => ($depth === 0 || $depth === 1) ? $component->expert_fee_mode : null,
            'automation_expert_fee' => ($depth === 0 || $depth === 1) ? $component->automation_expert_fee : null,
            'sort_order'            => $component->sort_order,
        ]);

        if ($component->children && $component->children->count() > 0) {
            foreach ($component->children as $child) {
                $this->copyComponentTreeToNodes($child, $quotationId, $node->id, $depth + 1);
            }
        }
    }

    public function getQuotationTree(string $quotationId): array
    {
        $quotation = $this->repo->getById($quotationId);

        $allNodes = QuotationNode::with(['selectedProvider', 'pricingCategory'])
            ->where('quotation_id', $quotationId)
            ->orderBy('sort_order')
            ->get();

        foreach ($allNodes as $item) {
            $children = $allNodes->filter(fn($c) => $c->parent_node_id === $item->id)->values();
            $item->setRelation('children', $children);
        }

        $allComponents = Component::all()->keyBy('id');
        $allProviders = $this->providerRepo->getAllActiveKeyedById();

        $rootNodes = $allNodes->whereNull('parent_node_id')->values();

        $formattedRoots = [];
        foreach ($rootNodes as $root) {
            $formattedRoots[] = $this->formatQuotationNode($root, $allProviders, $allComponents);
        }

        return count($formattedRoots) === 1 ? $formattedRoots[0] : $formattedRoots;
    }

    protected function formatQuotationNode(QuotationNode $node, $allProviders, $allComponents): array
    {
        $hasChildren = $node->relationLoaded('children') && $node->children && $node->children->count() > 0;
        $sourceComp = $node->source_component_id ? $allComponents->get($node->source_component_id) : null;

        $data = [
            'id'                  => $node->id,
            'quotation_id'        => $node->quotation_id,
            'parent_node_id'      => $node->parent_node_id,
            'source_component_id' => $node->source_component_id,
            'name'                => $node->name,
            'description'         => $node->description ?? ($sourceComp ? $sourceComp->description : null),
            'depth'               => (int) $node->depth,
            'is_custom'           => (bool) $node->is_custom,
            'is_selected'         => (bool) $node->is_selected,
            'pricing_category'    => $node->pricingCategory ? [
                'id'   => $node->pricingCategory->id,
                'name' => $node->pricingCategory->name,
                'code' => $node->pricingCategory->code,
            ] : null,
        ];

        if (!$hasChildren) {
            $price = $this->computeQuotationLeafPrice($node, $allProviders);
            $data['estimated_price'] = $node->is_selected ? round($price, 2) : 0.0;
            $data['pricing_method']  = $node->pricing_method ?? ($sourceComp ? $sourceComp->pricing_method : null);
            $data['billing_type']    = $node->billing_type ?? ($sourceComp ? $sourceComp->billing_type : null);
            $data['unit']            = $node->unit ?? ($sourceComp ? $sourceComp->unit : null);
            $data['unit_price']      = $node->unit_price !== null ? (float) $node->unit_price : ($sourceComp ? (float) $sourceComp->unit_price : null);
            $data['quantity']        = $node->quantity !== null ? (float) $node->quantity : 1;
            $data['selected_dimensions'] = $node->selected_dimensions;

            $expandedProviders = [];
            $defaultProvider = null;
            $availProviders = $sourceComp ? $sourceComp->available_providers : null;

            if ($availProviders && is_array($availProviders)) {
                foreach ($availProviders as $p) {
                    $provider = $allProviders->get($p['provider_id'] ?? null);
                    if ($provider) {
                        $isDefault = (bool) ($p['is_default'] ?? false);
                        $isSelected = ($node->selected_provider_id === $provider->id) || (!$node->selected_provider_id && $isDefault);
                        $providerData = [
                            'provider_id'         => $provider->id,
                            'provider_key'        => $provider->provider_company_code,
                            'provider_name'       => $provider->provider_company,
                            'model_id'            => $provider->code,
                            'model_name'          => $provider->name,
                            'billing_unit'        => $provider->billing_unit,
                            'billing_granularity' => (int) ($provider->billing_granularity ?? 1),
                            'effective_rate'      => (float) $provider->effective_rate,
                            'input_rate'          => $provider->input_rate !== null ? (float) $provider->input_rate : null,
                            'output_rate'         => $provider->output_rate !== null ? (float) $provider->output_rate : null,
                            'multipliers'         => $provider->multipliers ?? (object) [],
                            'is_default'          => $isDefault,
                            'is_selected'         => $isSelected,
                        ];
                        $expandedProviders[] = $providerData;

                        if ($isDefault || $defaultProvider === null) {
                            $defaultProvider = $providerData;
                        }
                    }
                }
            } elseif ($node->selectedProvider) {
                $p = $node->selectedProvider;
                $selectedData = [
                    'provider_id'         => $p->id,
                    'provider_key'        => $p->provider_company_code,
                    'provider_name'       => $p->provider_company,
                    'model_id'            => $p->code,
                    'model_name'          => $p->name,
                    'billing_unit'        => $p->billing_unit,
                    'billing_granularity' => (int) ($p->billing_granularity ?? 1),
                    'effective_rate'      => (float) $p->effective_rate,
                    'input_rate'          => $p->input_rate !== null ? (float) $p->input_rate : null,
                    'output_rate'         => $p->output_rate !== null ? (float) $p->output_rate : null,
                    'multipliers'         => $p->multipliers ?? (object) [],
                    'is_default'          => true,
                    'is_selected'         => true,
                ];
                $expandedProviders[] = $selectedData;
                $defaultProvider = $selectedData;
            }

            $data['providers']        = $expandedProviders;
            $data['default_provider'] = $defaultProvider;
            $data['selected_provider'] = $node->selected_provider_id 
                ? (collect($expandedProviders)->firstWhere('provider_id', $node->selected_provider_id) ?? $defaultProvider)
                : $defaultProvider;
        } else {
            $children = [];
            $childSum = 0.0;
            foreach ($node->children as $child) {
                $formattedChild = $this->formatQuotationNode($child, $allProviders, $allComponents);
                $childSum += $formattedChild['estimated_price'] ?? 0;
                $children[] = $formattedChild;
            }

            $expertFee = 0.0;
            if ($node->depth === 1 && $node->expert_fee_mode === 'COMPONENT_LEVEL' && $node->automation_expert_fee > 0) {
                $expertFee = (float) $node->automation_expert_fee;
            }

            $data['estimated_price'] = $node->is_selected ? round($childSum + $expertFee, 2) : 0.0;

            if ($node->expert_fee_mode || ($sourceComp && $sourceComp->expert_fee_mode)) {
                $data['expert_fee_mode']       = $node->expert_fee_mode ?? $sourceComp->expert_fee_mode;
                $data['automation_expert_fee'] = (float) ($node->automation_expert_fee ?? ($sourceComp ? $sourceComp->automation_expert_fee : 0));
            }

            $data['children'] = $children;
        }

        $meta = $node->metadata ?? ($sourceComp ? $sourceComp->metadata : null);
        if ($meta) {
            $data['metadata'] = $meta;
        }

        $data['sort_order'] = (int) ($node->sort_order ?? ($sourceComp ? $sourceComp->sort_order : 0));

        return $data;
    }

    protected function computeQuotationLeafPrice(QuotationNode $node, $allProviders): float
    {
        $quantity = (float) ($node->quantity ?? 1);
        $unitPrice = null;

        if ($node->unit_price !== null) {
            $unitPrice = (float) $node->unit_price;
        }

        $provider = $node->selected_provider_id ? $allProviders->get($node->selected_provider_id) : null;
        if ($unitPrice === null && $provider) {
            $unitPrice = $provider->effective_rate * $this->getDimensionMultiplier($provider, $node->selected_dimensions);
        }

        if ($unitPrice === null) {
            return 0.0;
        }

        $granularity = $provider?->billing_granularity ?? 1;
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
}