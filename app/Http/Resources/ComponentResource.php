<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'uuid'                  => $this->uuid,
            'parent_id'             => $this->parent_id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'is_bundle'             => $this->is_bundle,
            'is_leaf'               => $this->is_leaf,
            'is_active'             => $this->is_active,
            'platform'              => $this->platform,
            'category'              => $this->category,
            'pricing_category'      => $this->whenLoaded('pricingCategory', fn() => [
                'id'   => $this->pricingCategory->id,
                'name' => $this->pricingCategory->name,
                'code' => $this->pricingCategory->code,
            ]),
            'pricing_method'        => $this->pricing_method,
            'billing_type'          => $this->billing_type,
            'unit'                  => $this->unit,
            'unit_price'            => $this->unit_price,
            'quantity'              => $this->quantity,
            'expert_fee_mode'       => $this->expert_fee_mode,
            'automation_expert_fee' => $this->automation_expert_fee,
            'available_providers'   => $this->available_providers,
            'tags'                  => $this->tags,
            'sort_order'            => $this->sort_order,
            'children'              => ComponentResource::collection($this->whenLoaded('children')),
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
