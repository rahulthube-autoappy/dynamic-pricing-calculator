<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Component extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'parent_id'             => 'array',
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

    public function pricingCategory()
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateEstimatedPrice(?\Illuminate\Support\Collection $providers = null): float
    {
        if ($providers === null) {
            static $cachedProviders = null;
            if ($cachedProviders === null) {
                $cachedProviders = Provider::where('is_active', true)->get()->keyBy('id');
            }
            $providers = $cachedProviders;
        }

        if ($this->is_leaf) {
            $qty = (float) ($this->quantity ?? 1);
            if ($this->unit_price !== null && (float) $this->unit_price > 0) {
                return round($qty * (float) $this->unit_price, 2);
            }

            if ($this->available_providers && is_array($this->available_providers) && count($this->available_providers) > 0) {
                $defaultConfig = collect($this->available_providers)->firstWhere('is_default', true) 
                    ?? collect($this->available_providers)->first();
                
                $providerId = $defaultConfig['provider_id'] ?? null;
                $provider = $providerId ? $providers->get($providerId) : null;
                if ($provider) {
                    $rate = $provider->effective_rate;
                    $granularity = max(1, (int) ($provider->billing_granularity ?? 1));
                    return round(($qty / $granularity) * $rate, 2);
                }
            }

            return round((float) ($this->unit_price ?? 0), 2);
        }

        // Non-leaf / Bundle: recursively sum children
        $total = 0.0;
        if ($this->relationLoaded('children') && $this->children) {
            foreach ($this->children as $child) {
                $total += $child->calculateEstimatedPrice($providers);
            }
        }

        // Add component-level expert fee if set on this group (depth=1)
        if (!$this->is_bundle && $this->expert_fee_mode === 'COMPONENT_LEVEL') {
            $total += (float) ($this->automation_expert_fee ?? 0);
        }

        // Add automation-level expert fee if root bundle
        // if ($this->is_bundle && $this->expert_fee_mode === 'AUTOMATION_LEVEL') {
        //     $total += (float) ($this->automation_expert_fee ?? 0);
        // }

        return round($total, 2);
    }
}