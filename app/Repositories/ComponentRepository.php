<?php

namespace App\Repositories;

use App\Models\Component;
use Illuminate\Support\Str;

class ComponentRepository
{
    public function getAll()
    {
        return Component::with('pricingCategory')->orderBy('sort_order')->get();
    }

    public function getBundles()
    {
        return Component::with('allChildren')
            ->where('is_bundle', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getById($id)
    {
        return Component::with('allChildren', 'pricingCategory')->findOrFail($id);
    }

    public function getBundleWithChildren($id)
    {
        return Component::with(['children' => function ($query) {
            $query->with(['children' => function ($q) {
                $q->with('children');
            }]);
        }])->where('is_bundle', true)->findOrFail($id);
    }

    public function create(array $data): Component
    {
        $data['uuid'] = $data['uuid'] ?? (string) Str::uuid();
        return Component::create($data);
    }

    public function update($id, array $data): Component
    {
        $record = $this->getById($id);
        $record->update($data);
        return $record->fresh(['allChildren', 'pricingCategory']);
    }

    public function delete($id): bool
    {
        $record = $this->getById($id);
        return $record->delete();
    }
}
