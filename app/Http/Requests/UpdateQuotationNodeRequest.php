<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationNodeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => 'sometimes|required|string|max:200',
            'description'           => 'sometimes|nullable|string',
            'is_selected'           => 'sometimes|boolean',
            'pricing_category_id'   => 'sometimes|nullable|integer|exists:pricing_categories,id',
            'pricing_method'        => 'sometimes|nullable|in:fixed,qty_unit,percentage,formula,usage_estimation,manual',
            'billing_type'          => 'sometimes|nullable|in:ONE_TIME,RECURRING',
            'unit'                  => 'sometimes|nullable|string|max:50',
            'quantity'              => 'sometimes|nullable|numeric|min:0',
            'unit_price'            => 'sometimes|nullable|numeric|min:0',
            'formula'               => 'sometimes|nullable|string',
            'selected_provider_id'  => 'sometimes|nullable|integer|exists:providers,id',
            'selected_dimensions'   => 'sometimes|nullable|array',
            'custom_provider_name'  => 'sometimes|nullable|string|max:200',
            'feasibility_status'    => 'sometimes|in:not_required,pending,approved,rejected',
            'expert_fee_mode'       => 'sometimes|nullable|in:COMPONENT_LEVEL,AUTOMATION_LEVEL',
            'automation_expert_fee' => 'sometimes|nullable|numeric|min:0',
            'sort_order'            => 'sometimes|integer',
            'metadata'              => 'sometimes|nullable|array',
        ];
    }
}
