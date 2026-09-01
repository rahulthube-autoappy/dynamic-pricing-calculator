<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PricingSnapshot extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'depth'               => 'integer',
        'quantity'            => 'float',
        'unit_price'          => 'float',
        'calculated_total'    => 'float',
        'selected_dimensions' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function quotationNode()
    {
        return $this->belongsTo(QuotationNode::class, 'quotation_node_id');
    }

    public function parentSnapshot()
    {
        return $this->belongsTo(PricingSnapshot::class, 'parent_snapshot_id');
    }

    public function childrenSnapshots()
    {
        return $this->hasMany(PricingSnapshot::class, 'parent_snapshot_id');
    }
}