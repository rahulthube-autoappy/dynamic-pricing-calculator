<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'               => 'sometimes|nullable|string|max:200',
            'selected_plan_id'    => 'sometimes|nullable|integer|exists:plans,id',
            'status'              => 'sometimes|in:draft,active,submitted,checked_out,archived',
            'requires_expert'     => 'sometimes|boolean',
            'expert_notes'        => 'sometimes|nullable|string',
            'notes'               => 'sometimes|nullable|string',
        ];
    }
}
