<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateComponentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => 'sometimes|required|string|max:200',
            'parent_id'             => 'sometimes|nullable|integer|exists:components,id',
            'description'           => 'sometimes|nullable|string',
            'is_bundle'             => 'sometimes|boolean',
            'is_leaf'               => 'sometimes|boolean',
            'platform'              => 'sometimes|nullable|string|max:100',
            'category'              => 'sometimes|nullable|string|max:100',
            'pricing_category_id'   => 'sometimes|nullable|integer|exists:pricing_categories,id',
            'pricing_method'        => 'sometimes|nullable|in:fixed,qty_unit,percentage,formula,usage_estimation,manual',
            'billing_type'          => 'sometimes|nullable|in:ONE_TIME,RECURRING',
            'unit'                  => 'sometimes|nullable|string|max:50',
            'unit_price'            => 'sometimes|nullable|numeric|min:0',
            'quantity'              => 'sometimes|nullable|numeric|min:0',
            'formula'               => 'sometimes|nullable|string',
            'internal_cost'         => 'sometimes|nullable|numeric|min:0',
            'expert_fee_mode'       => 'sometimes|nullable|in:COMPONENT_LEVEL,AUTOMATION_LEVEL',
            'automation_expert_fee' => 'sometimes|nullable|numeric|min:0',
            'available_providers'   => 'sometimes|nullable|array',
            'available_providers.*.provider_id' => 'required_with:available_providers|integer|exists:providers,id',
            'available_providers.*.is_default'  => 'nullable|boolean',
            'notes'                 => 'sometimes|nullable|string',
            'tags'                  => 'sometimes|nullable|array',
            'is_active'             => 'sometimes|boolean',
            'sort_order'            => 'sometimes|integer',
        ];
    }
}
