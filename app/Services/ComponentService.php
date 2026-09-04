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

    public function getGroups(): array
    {
        $groups = $this->repo->getGroups();
        $allComponents = $this->repo->getAll();
        $allProviders = $this->providerRepo->getAllActiveKeyedById();
        $bundlesMap = $allComponents->where('is_bundle', true)->keyBy('id');

        $result = [];
        foreach ($groups as $group) {
            $parentBundleId = is_array($group->parent_id) ? ($group->parent_id[0] ?? null) : null;
            $parentBundle = $parentBundleId ? $bundlesMap->get($parentBundleId) : null;
            $estimatedPrice = $group->calculateEstimatedPrice($allProviders);

            $result[] = [
                'id'                    => $group->id,
                'parent_id'             => $group->parent_id,
                'bundle_id'             => $parentBundleId,
                'bundle_name'           => $parentBundle ? $parentBundle->name : null,
                'name'                  => $group->name,
                'description'           => $group->description,
                'depth'                 => 1,
                'is_bundle'             => false,
                'is_leaf'               => false,
                'platform'              => $group->platform ?? ($parentBundle ? $parentBundle->platform : null),
                'category'              => $group->category ?? ($parentBundle ? $parentBundle->category : null),
                'pricing_category'      => $group->pricingCategory ? [
                    'id'          => $group->pricingCategory->id,
                    'name'        => $group->pricingCategory->name,
                    'code'        => $group->pricingCategory->code,
                    'description' => $group->pricingCategory->description,
                ] : null,
                'pricing_category_id'   => $group->pricing_category_id,
                'estimated_price'       => $estimatedPrice,
                'starting_price'        => $estimatedPrice,
                'expert_fee_mode'       => $group->expert_fee_mode,
                'automation_expert_fee' => $group->automation_expert_fee !== null ? (float) $group->automation_expert_fee : 0.0,
                'child_count'           => $group->children ? $group->children->count() : 0,
                'tags'                  => $group->tags,
                'metadata'              => $group->metadata,
                'notes'                 => $group->notes,
                'sort_order'            => (int) ($group->sort_order ?? 0),
                'is_active'             => (bool) $group->is_active,
            ];
        }

        return $result;
    }

    public function getSubcomponents(): array
    {
        $subcomponents = $this->repo->getSubcomponents();
        $allComponents = $this->repo->getAll();
        $allProviders = $this->providerRepo->getAllActiveKeyedById();
        $componentsMap = $allComponents->keyBy('id');

        $result = [];
        foreach ($subcomponents as $item) {
            $parentGroupId = is_array($item->parent_id) ? ($item->parent_id[0] ?? null) : null;
            $parentGroup = $parentGroupId ? $componentsMap->get($parentGroupId) : null;
            $parentBundleId = ($parentGroup && is_array($parentGroup->parent_id)) ? ($parentGroup->parent_id[0] ?? null) : null;
            $parentBundle = $parentBundleId ? $componentsMap->get($parentBundleId) : null;

            $depth = $item->is_leaf ? 2 : 2; // subcomponents level
            $formatted = $this->formatNode($item, $allProviders, $depth);
            $formatted['parent_group_id']   = $parentGroupId;
            $formatted['parent_group_name'] = $parentGroup ? $parentGroup->name : null;
            $formatted['bundle_id']         = $parentBundleId;
            $formatted['bundle_name']       = $parentBundle ? $parentBundle->name : null;

            $result[] = $formatted;
        }

        return $result;
    }

    public function getLeaves(): array
    {
        return $this->getSubcomponents();
    }

    public function getComponentTree(string $id): array
    {
        $node = $this->repo->getById($id);
        $allProviders = $this->providerRepo->getAllActiveKeyedById();
        $depth = $node->is_bundle ? 0 : ($node->is_leaf ? 2 : 1);
        return $this->formatNode($node, $allProviders, $depth);
    }

    public function getBundleTree(string $id): array
    {
        return $this->getComponentTree($id);
    }

    protected function formatNode(Component $node, $allProviders, int $depth = 0): array
    {
        $isLeaf = (bool) $node->is_leaf;
        $isBundle = (bool) $node->is_bundle;
        $estimatedPrice = $node->calculateEstimatedPrice($allProviders);

        $data = [
            'id'               => $node->id,
            'parent_id'        => $node->parent_id,
            'name'             => $node->name,
            'description'      => $node->description,
            'depth'            => $depth,
            'is_bundle'        => $isBundle,
            'is_leaf'          => $isLeaf,
            'platform'         => $node->platform,
            'category'         => $node->category,
            'pricing_category' => $node->pricingCategory ? [
                'id'   => $node->pricingCategory->id,
                'name' => $node->pricingCategory->name,
                'code' => $node->pricingCategory->code,
            ] : null,
            'estimated_price'  => $estimatedPrice,
        ];

        if ($isLeaf) {
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
                            'billing_unit'        => $provider->billing_unit,
                            'billing_granularity' => (int) ($provider->billing_granularity ?? 1),
                            'effective_rate'      => (float) $provider->effective_rate,
                            'input_rate'          => $provider->input_rate !== null ? (float) $provider->input_rate : null,
                            'output_rate'         => $provider->output_rate !== null ? (float) $provider->output_rate : null,
                            'multipliers'         => $provider->multipliers ?? (object) [],
                            'is_default'          => $isDefault,
                        ];

                        $expandedProviders[] = $providerData;

                        if ($isDefault || $defaultProvider === null) {
                            $defaultProvider = $providerData;
                        }
                    }
                }
            }

            $data['pricing_method']   = $node->pricing_method;
            $data['billing_type']     = $node->billing_type;
            $data['unit']             = $node->unit;
            $data['unit_price']       = $node->unit_price !== null ? (float) $node->unit_price : null;
            $data['quantity']         = $node->quantity !== null ? (float) $node->quantity : 1;
            $data['providers']        = $expandedProviders;
            $data['default_provider'] = $defaultProvider;
        } else {
            // Container / Group / Bundle
            if ($node->expert_fee_mode) {
                $data['expert_fee_mode']       = $node->expert_fee_mode;
                $data['automation_expert_fee'] = (float) ($node->automation_expert_fee ?? 0);
            }

            $children = [];
            if ($node->relationLoaded('children') && $node->children && $node->children->count() > 0) {
                foreach ($node->children as $child) {
                    $children[] = $this->formatNode($child, $allProviders, $depth + 1);
                }
            }
            $data['children'] = $children;
        }

        if ($node->metadata) {
            $data['metadata'] = $node->metadata;
        }
        if ($node->tags) {
            $data['tags'] = $node->tags;
        }

        $data['sort_order'] = (int) ($node->sort_order ?? 0);
        $data['is_active']  = (bool) $node->is_active;

        return $data;
    }
}