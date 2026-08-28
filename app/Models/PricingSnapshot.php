<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingSnapshot extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'selected_dimensions' => 'array',
        'quantity'            => 'float',
        'unit_price'          => 'float',
        'calculated_total'    => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function quotationNode()
    {
        return $this->belongsTo(QuotationNode::class);
    }

    public function parent()
    {
        return $this->belongsTo(PricingSnapshot::class, 'parent_snapshot_id');
    }

    public function children()
    {
        return $this->hasMany(PricingSnapshot::class, 'parent_snapshot_id');
    }
}
