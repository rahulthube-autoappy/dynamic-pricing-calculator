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

    public function getById($id): Component
    {
        return $this->repo->getById($id);
    }

    public function create(array $data): Component
    {
        return $this->repo->create($data);
    }

    public function update($id, array $data): Component
    {
        return $this->repo->update($id, $data);
    }

    public function delete($id): bool
    {
        return $this->repo->delete($id);
    }

    /**
     * Get a bundle with its full nested tree and expanded provider info.
     */
    public function getBundleTree(int $id): array
    {
        $bundle = $this->repo->getBundleWithChildren($id);
        $allProviders = $this->providerRepo->getAllActiveKeyedById();
        return $this->formatNode($bundle, $allProviders);
    }

    protected function formatNode(Component $node, $allProviders): array
    {
        $expandedProviders = [];

        if ($node->available_providers) {
            foreach ($node->available_providers as $p) {
                $provider = $allProviders->get($p['provider_id'] ?? null);
                if ($provider) {
                    $expandedProviders[] = [
                        'provider_id'         => $provider->id,
                        'provider_key'        => $provider->provider_company_code,
                        'provider_name'       => $provider->provider_company,
                        'model_id'            => $provider->code,
                        'model_name'          => $provider->name,
                        'billing_unit'        => $provider->billing_unit,
                        'billing_granularity' => (int) $provider->billing_granularity,
                        'input_rate'          => $provider->input_rate,
                        'output_rate'         => $provider->output_rate,
                        'rate'                => $provider->rate,
                        'is_default'          => $p['is_default'] ?? false,
                    ];
                }
            }
        }

        $children = [];
        if ($node->children && $node->children->count() > 0) {
            foreach ($node->children as $child) {
                $children[] = $this->formatNode($child, $allProviders);
            }
        }

        return [
            'id'                    => $node->id,
            'uuid'                  => $node->uuid,
            'parent_id'             => $node->parent_id,
            'name'                  => $node->name,
            'description'           => $node->description,
            'is_bundle'             => $node->is_bundle,
            'is_leaf'               => $node->is_leaf,
            'platform'              => $node->platform,
            'category'              => $node->category,
            'pricing_method'        => $node->pricing_method,
            'billing_type'          => $node->billing_type,
            'unit'                  => $node->unit,
            'unit_price'            => $node->unit_price,
            'quantity'              => $node->quantity,
            'expert_fee_mode'       => $node->expert_fee_mode,
            'automation_expert_fee' => $node->automation_expert_fee,
            'sort_order'            => $node->sort_order,
            'is_active'             => $node->is_active,
            'available_providers'   => $expandedProviders ?: null,
            'children'              => $children,
        ];
    }
}
