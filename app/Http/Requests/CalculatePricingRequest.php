<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculatePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'automationId' => 'required|string',
            'expertFeeStrategy' => 'nullable|string',
            'automationExpertFeeAmount' => 'nullable|numeric',
            'components' => 'required|array',
            'selectedPlanId' => 'nullable|string'
        ];
    }
}
