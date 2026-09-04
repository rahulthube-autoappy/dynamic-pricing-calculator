<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $estimatedPrice = $this->calculateEstimatedPrice();
        $isBundle = (bool) $this->is_bundle;
        $isLeaf = (bool) $this->is_leaf;

        $data = [
            'id'               => $this->id,
            'parent_id'        => $this->parent_id,
            'name'             => $this->name,
            'description'      => $this->description,
            'depth'            => $isBundle ? 0 : ($isLeaf ? 2 : 1),
            'is_bundle'        => $isBundle,
            'is_leaf'          => $isLeaf,
            'platform'         => $this->platform,
            'category'         => $this->category,
            'pricing_category' => $this->whenLoaded('pricingCategory', fn() => [
                'id'   => $this->pricingCategory->id,
                'name' => $this->pricingCategory->name,
                'code' => $this->pricingCategory->code,
            ]),
            'estimated_price'  => $estimatedPrice,
        ];

        if ($isLeaf) {
            $data['pricing_method'] = $this->pricing_method;
            $data['billing_type']   = $this->billing_type;
            $data['unit']           = $this->unit;
            $data['unit_price']     = $this->unit_price !== null ? (float) $this->unit_price : null;
            $data['quantity']       = $this->quantity !== null ? (float) $this->quantity : 1;
        } else {
            if ($this->expert_fee_mode) {
                $data['expert_fee_mode']       = $this->expert_fee_mode;
                $data['automation_expert_fee'] = (float) ($this->automation_expert_fee ?? 0);
            }
        }

        if ($this->relationLoaded('children') && $this->children && $this->children->count() > 0) {
            $data['children'] = ComponentResource::collection($this->children);
        }

        if ($this->metadata) {
            $data['metadata'] = $this->metadata;
        }
        if ($this->tags) {
            $data['tags'] = $this->tags;
        }

        $data['sort_order'] = (int) ($this->sort_order ?? 0);
        $data['is_active']  = (bool) $this->is_active;

        return $data;
    }
}