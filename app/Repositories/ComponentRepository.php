<?php

namespace App\Repositories;

use App\Models\Component;

class ComponentRepository
{
    public function getAll()
    {
        $all = Component::with('pricingCategory')->orderBy('sort_order')->get();
        $this->linkChildren($all);
        return $all;
    }

    public function getBundles()
    {
        $all = Component::with('pricingCategory')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $this->linkChildren($all);
        return $all->where('is_bundle', true)->values();
    }

    public function getById(string $id): Component
    {
        $all = Component::with('pricingCategory')->orderBy('sort_order')->get();
        $this->linkChildren($all);
        return $all->firstWhere('id', $id) ?? Component::findOrFail($id);
    }

    public function getBundleWithChildren(string $id): Component
    {
        $all = Component::with('pricingCategory')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $this->linkChildren($all);
        return $all->firstWhere('id', $id) ?? Component::findOrFail($id);
    }

    public function create(array $data): Component
    {
        return Component::create($data);
    }

    public function update(string $id, array $data): Component
    {
        $record = Component::findOrFail($id);
        $record->update($data);
        return $this->getById($id);
    }

    public function delete(string $id): bool
    {
        $record = Component::findOrFail($id);
        return $record->delete();
    }

    protected function linkChildren($collection)
    {
        foreach ($collection as $item) {
            $children = $collection->filter(function ($c) use ($item) {
                return is_array($c->parent_id) && in_array((string) $item->id, array_map('strval', $c->parent_id));
            })->values();
            $item->setRelation('children', $children);
        }
    }
}