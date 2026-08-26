<?php

namespace App\Repositories;

use App\Models\Component;

class ComponentRepository
{
    public function getBundleWithChildren($id)
    {
        return Component::with(['children' => function($query) {
            $query->with(['children' => function($q) {
                $q->with('children');
            }]);
        }])->where('is_bundle', true)->findOrFail($id);
    }
}
