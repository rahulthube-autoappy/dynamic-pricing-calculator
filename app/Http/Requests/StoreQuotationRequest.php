<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'                => 'required|in:cart,custom_automation',
            'title'               => 'nullable|string|max:200',
            'source_component_id' => 'nullable|integer|exists:components,id',
            'selected_plan_id'    => 'nullable|integer|exists:plans,id',
            'requires_expert'     => 'nullable|boolean',
            'expert_notes'        => 'nullable|string',
            'notes'               => 'nullable|string',
            'user_id'             => 'nullable|integer|exists:users,id',
        ];
    }
}
