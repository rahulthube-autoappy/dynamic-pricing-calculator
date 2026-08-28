<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingSnapshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'order_id'            => $this->order_id,
            'quotation_node_id'   => $this->quotation_node_id,
            'parent_snapshot_id'  => $this->parent_snapshot_id,
            'depth'               => (int) $this->depth,
            'node_name'           => $this->node_name,
            'pricing_category'    => $this->pricing_category,
            'pricing_method'      => $this->pricing_method,
            'billing_type'        => $this->billing_type,
            'unit'                => $this->unit,
            'quantity'            => $this->quantity !== null ? (float) $this->quantity : null,
            'unit_price'          => $this->unit_price !== null ? (float) $this->unit_price : null,
            'calculated_total'    => (float) $this->calculated_total,
            'provider_name'       => $this->provider_name,
            'selected_dimensions' => $this->selected_dimensions,
            'created_at'          => $this->created_at,
            'children'            => PricingSnapshotResource::collection($this->whenLoaded('children')),
        ];
    }
}