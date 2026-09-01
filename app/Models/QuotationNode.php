<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuotationNode extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'depth'                 => 'integer',
        'is_custom'             => 'boolean',
        'is_selected'           => 'boolean',
        'quantity'              => 'float',
        'unit_price'            => 'float',
        'selected_dimensions'   => 'array',
        'automation_expert_fee' => 'float',
        'internal_cost'         => 'float',
        'sort_order'            => 'integer',
        'metadata'              => 'array',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function parent()
    {
        return $this->belongsTo(QuotationNode::class, 'parent_node_id');
    }

    public function children()
    {
        return $this->hasMany(QuotationNode::class, 'parent_node_id')->orderBy('sort_order');
    }

    public function sourceComponent()
    {
        return $this->belongsTo(Component::class, 'source_component_id');
    }

    public function pricingCategory()
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
    }

    public function selectedProvider()
    {
        return $this->belongsTo(Provider::class, 'selected_provider_id');
    }
}