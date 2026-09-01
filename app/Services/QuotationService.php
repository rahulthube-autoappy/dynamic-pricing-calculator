<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationNode;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationNodeRepository;
use App\Repositories\ComponentRepository;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    protected QuotationRepository $repo;
    protected QuotationNodeRepository $nodeRepo;
    protected ComponentRepository $componentRepo;
    protected QuotationNodePricingEngine $pricingEngine;

    public function __construct(
        QuotationRepository $repo,
        QuotationNodeRepository $nodeRepo,
        ComponentRepository $componentRepo,
        QuotationNodePricingEngine $pricingEngine
    ) {
        $this->repo = $repo;
        $this->nodeRepo = $nodeRepo;
        $this->componentRepo = $componentRepo;
        $this->pricingEngine = $pricingEngine;
    }

    public function getByUser(int $userId)
    {
        return $this->repo->getByUser($userId);
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
}