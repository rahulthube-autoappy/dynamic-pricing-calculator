<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'subtotal'                => 'float',
        'expert_fee_total'        => 'float',
        'one_time_total'          => 'float',
        'recurring_monthly_total' => 'float',
        'plan_price'              => 'float',
        'discount_total'          => 'float',
        'tax_total'               => 'float',
        'grand_total'             => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function pricingSnapshots()
    {
        return $this->hasMany(PricingSnapshot::class, 'order_id');
    }
}