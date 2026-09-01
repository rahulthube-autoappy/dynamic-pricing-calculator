<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'quotation_id'          => $this->quotation_id,
            'parent_node_id'        => $this->parent_node_id,
            'source_component_id'   => $this->source_component_id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'depth'                 => $this->depth,
            'is_custom'             => $this->is_custom,
            'is_selected'           => $this->is_selected,
            'pricing_category_id'   => $this->pricing_category_id,
            'pricing_method'        => $this->pricing_method,
            'billing_type'          => $this->billing_type,
            'unit'                  => $this->unit,
            'quantity'              => $this->quantity,
            'unit_price'            => $this->unit_price,
            'formula'               => $this->formula,
            'selected_provider'     => $this->whenLoaded('selectedProvider', fn() => $this->selectedProvider ? [
                'id'           => $this->selectedProvider->id,
                'name'         => $this->selectedProvider->name,
                'company'      => $this->selectedProvider->provider_company,
                'billing_unit' => $this->selectedProvider->billing_unit,
                'input_rate'   => $this->selectedProvider->input_rate,
                'output_rate'  => $this->selectedProvider->output_rate,
                'rate'         => $this->selectedProvider->rate,
            ] : null),
            'selected_dimensions'   => $this->selected_dimensions,
            'custom_provider_name'  => $this->custom_provider_name,
            'feasibility_status'    => $this->feasibility_status,
            'expert_fee_mode'       => $this->expert_fee_mode,
            'automation_expert_fee' => $this->automation_expert_fee,
            'sort_order'            => $this->sort_order,
            'metadata'              => $this->metadata,
            'children'              => QuotationNodeResource::collection($this->whenLoaded('children')),
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}