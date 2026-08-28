<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationNodeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:200',
            'parent_node_id'        => 'nullable|integer|exists:quotation_nodes,id',
            'source_component_id'   => 'nullable|integer|exists:components,id',
            'description'           => 'nullable|string',
            'depth'                 => 'required|integer|min:0',
            'is_custom'             => 'nullable|boolean',
            'is_selected'           => 'nullable|boolean',
            'pricing_category_id'   => 'nullable|integer|exists:pricing_categories,id',
            'pricing_method'        => 'nullable|in:fixed,qty_unit,percentage,formula,usage_estimation,manual',
            'billing_type'          => 'nullable|in:ONE_TIME,RECURRING',
            'unit'                  => 'nullable|string|max:50',
            'quantity'              => 'nullable|numeric|min:0',
            'unit_price'            => 'nullable|numeric|min:0',
            'formula'               => 'nullable|string',
            'selected_provider_id'  => 'nullable|integer|exists:providers,id',
            'selected_dimensions'   => 'nullable|array',
            'custom_provider_name'  => 'nullable|string|max:200',
            'feasibility_status'    => 'nullable|in:not_required,pending,approved,rejected',
            'expert_fee_mode'       => 'nullable|in:COMPONENT_LEVEL,AUTOMATION_LEVEL',
            'automation_expert_fee' => 'nullable|numeric|min:0',
            'sort_order'            => 'nullable|integer',
            'metadata'              => 'nullable|array',
        ];
    }
}
