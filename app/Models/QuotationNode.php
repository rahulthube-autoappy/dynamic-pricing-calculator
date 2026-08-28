<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationNode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'selected_dimensions'   => 'array',
        'metadata'              => 'array',
        'is_custom'             => 'boolean',
        'is_selected'           => 'boolean',
        'quantity'              => 'float',
        'unit_price'            => 'float',
        'automation_expert_fee' => 'float',
        'internal_cost'         => 'float',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function parent()
    {
        return $this->belongsTo(QuotationNode::class, 'parent_node_id');
    }

    public function children()
    {
        return $this->hasMany(QuotationNode::class, 'parent_node_id')->orderBy('sort_order');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function selectedProvider()
    {
        return $this->belongsTo(Provider::class, 'selected_provider_id');
    }

    public function sourceComponent()
    {
        return $this->belongsTo(Component::class, 'source_component_id');
    }

    public function pricingCategory()
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
    }
}
