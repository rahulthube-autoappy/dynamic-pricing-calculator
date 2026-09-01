<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComponentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => 'sometimes|string|max:200',
            'parent_id'             => 'nullable|array',
            'parent_id.*'           => 'string|exists:components,id',
            'description'           => 'nullable|string',
            'is_bundle'             => 'nullable|boolean',
            'is_leaf'               => 'nullable|boolean',
            'platform'              => 'nullable|string|max:100',
            'category'              => 'nullable|string|max:100',
            'pricing_category_id'   => 'nullable|string|exists:pricing_categories,id',
            'pricing_method'        => 'nullable|in:fixed,qty_unit,percentage,formula,usage_estimation,manual',
            'billing_type'          => 'nullable|in:ONE_TIME,RECURRING',
            'unit'                  => 'nullable|string|max:50',
            'unit_price'            => 'nullable|numeric|min:0',
            'quantity'              => 'nullable|numeric|min:0',
            'formula'               => 'nullable|string',
            'internal_cost'         => 'nullable|numeric|min:0',
            'expert_fee_mode'       => 'nullable|in:COMPONENT_LEVEL,AUTOMATION_LEVEL',
            'automation_expert_fee' => 'nullable|numeric|min:0',
            'available_providers'   => 'nullable|array',
            'available_providers.*.provider_id' => 'required_with:available_providers|string|exists:providers,id',
            'available_providers.*.is_default'  => 'nullable|boolean',
            'notes'                 => 'nullable|string',
            'tags'                  => 'nullable|array',
            'is_active'             => 'nullable|boolean',
            'sort_order'            => 'nullable|integer',
        ];
    }
}