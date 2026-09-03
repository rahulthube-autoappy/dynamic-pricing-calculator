<?php

namespace App\Services;

use App\Models\Component;
use App\Repositories\ComponentRepository;
use App\Repositories\ProviderRepository;

class ComponentService
{
    protected ComponentRepository $repo;
    protected ProviderRepository $providerRepo;

    public function __construct(ComponentRepository $repo, ProviderRepository $providerRepo)
    {
        $this->repo = $repo;
        $this->providerRepo = $providerRepo;
    }

    public function getAll()
    {
        return $this->repo->getAll();
    }

    public function getBundles()
    {
        return $this->repo->getBundles();
    }

    public function getById(string $id): Component
    {
        return $this->repo->getById($id);
    }

    public function create(array $data): Component
    {
        return $this->repo->create($data);
    }

    public function update(string $id, array $data): Component
    {
        return $this->repo->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    public function getBundleTree(string $id): array
    {
        $bundle = $this->repo->getBundleWithChildren($id);
        $allProviders = $this->providerRepo->getAllActiveKeyedById();
        return $this->formatNode($bundle, $allProviders);
    }

    protected function formatNode(Component $node, $allProviders): array
    {
        $expandedProviders = [];
        $defaultProvider = null;

        if ($node->available_providers && is_array($node->available_providers)) {
            foreach ($node->available_providers as $p) {
                $provider = $allProviders->get($p['provider_id'] ?? null);
                if ($provider) {
                    $isDefault = (bool) ($p['is_default'] ?? false);
                    $providerData = [
                        'provider_id'         => $provider->id,
                        'provider_key'        => $provider->provider_company_code,
                        'provider_name'       => $provider->provider_company,
                        'model_id'            => $provider->code,
                        'model_name'          => $provider->name,
                        'description'         => $provider->description,
                        'capabilities'        => $provider->capabilities ?? [],
                        'billing_unit'        => $provider->billing_unit,
                        'billing_granularity' => (int) ($provider->billing_granularity ?? 1),
                        'allow_decimals'      => (bool) $provider->allow_decimals,
                        'input_rate'          => $provider->input_rate !== null ? (float) $provider->input_rate : null,
                        'output_rate'         => $provider->output_rate !== null ? (float) $provider->output_rate : null,
                        'rate'                => $provider->rate !== null ? (float) $provider->rate : null,
                        'effective_rate'      => (float) $provider->effective_rate,
                        'multipliers'         => $provider->multipliers ?? (object) [],
                        'metadata'            => $provider->metadata ?? null,
                        'is_default'          => $isDefault,
                    ];

                    $expandedProviders[] = $providerData;

                    if ($isDefault || $defaultProvider === null) {
                        $defaultProvider = $providerData;
                    }
                }
            }
        }

        $children = [];
        if ($node->relationLoaded('children') && $node->children && $node->children->count() > 0) {
            foreach ($node->children as $child) {
                $children[] = $this->formatNode($child, $allProviders);
            }
        }

        $estimatedPrice = $node->calculateEstimatedPrice($allProviders);

        return [
            'id'                    => $node->id,
            'parent_id'             => $node->parent_id,
            'name'                  => $node->name,
            'description'           => $node->description,
            'is_bundle'             => (bool) $node->is_bundle,
            'is_leaf'               => (bool) $node->is_leaf,
            'platform'              => $node->platform,
            'category'              => $node->category,
            'pricing_category'      => $node->pricingCategory ? [
                'id'          => $node->pricingCategory->id,
                'name'        => $node->pricingCategory->name,
                'code'        => $node->pricingCategory->code,
                'description' => $node->pricingCategory->description,
            ] : null,
            'pricing_category_id'   => $node->pricing_category_id,
            'pricing_method'        => $node->pricing_method,
            'billing_type'          => $node->billing_type,
            'unit'                  => $node->unit,
            'unit_price'            => $node->unit_price !== null ? (float) $node->unit_price : null,
            'quantity'              => $node->quantity !== null ? (float) $node->quantity : null,
            'estimated_price'       => $estimatedPrice,
            'starting_price'        => $estimatedPrice,
            'expert_fee_mode'       => $node->expert_fee_mode,
            'automation_expert_fee' => $node->automation_expert_fee !== null ? (float) $node->automation_expert_fee : 0.0,
            'available_providers'   => $node->available_providers,
            'providers'             => $expandedProviders,
            'default_provider'      => $defaultProvider,
            'tags'                  => $node->tags,
            'metadata'              => $node->metadata,
            'notes'                 => $node->notes,
            'sort_order'            => (int) ($node->sort_order ?? 0),
            'is_active'             => (bool) $node->is_active,
            'children'              => $children,
        ];
    }
}