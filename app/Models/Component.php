<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $guarded = [];

    protected $casts = [
        'available_providers'   => 'array',
        'tags'                  => 'array',
        'metadata'              => 'array',
        'is_bundle'             => 'boolean',
        'is_leaf'               => 'boolean',
        'is_active'             => 'boolean',
        'unit_price'            => 'float',
        'quantity'              => 'float',
        'internal_cost'         => 'float',
        'automation_expert_fee' => 'float',
    ];

    public function parent()
    {
        return $this->belongsTo(Component::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Component::class, 'parent_id')->orderBy('sort_order');
    }

    public function pricingCategory()
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recursively load all descendants.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }
}
