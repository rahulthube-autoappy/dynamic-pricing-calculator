<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requires_expert' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'selected_plan_id');
    }

    public function sourceComponent()
    {
        return $this->belongsTo(Component::class, 'source_component_id');
    }

    public function nodes()
    {
        return $this->hasMany(QuotationNode::class);
    }

    public function rootNodes()
    {
        return $this->hasMany(QuotationNode::class)->whereNull('parent_node_id')->orderBy('sort_order');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
