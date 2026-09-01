<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Quotation extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'requires_expert' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceComponent()
    {
        return $this->belongsTo(Component::class, 'source_component_id');
    }

    public function selectedPlan()
    {
        return $this->belongsTo(Plan::class, 'selected_plan_id');
    }

    public function nodes()
    {
        return $this->hasMany(QuotationNode::class, 'quotation_id')->orderBy('sort_order');
    }

    public function rootNodes()
    {
        return $this->hasMany(QuotationNode::class, 'quotation_id')->whereNull('parent_node_id')->orderBy('sort_order');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'quotation_id');
    }
}