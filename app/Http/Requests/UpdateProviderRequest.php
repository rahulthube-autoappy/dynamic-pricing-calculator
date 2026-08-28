<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                  => 'sometimes|required|string|max:200',
            'code'                  => 'sometimes|required|string|max:100|unique:providers,code,' . $this->route('provider'),
            'provider_company'      => 'sometimes|required|string|max:100',
            'provider_company_code' => 'sometimes|required|string|max:100',
            'description'           => 'sometimes|nullable|string',
            'capabilities'          => 'sometimes|nullable|array',
            'billing_unit'          => 'sometimes|nullable|string|max:50',
            'billing_granularity'   => 'sometimes|integer|min:1',
            'allow_decimals'        => 'sometimes|boolean',
            'input_rate'            => 'sometimes|nullable|numeric|min:0',
            'output_rate'           => 'sometimes|nullable|numeric|min:0',
            'rate'                  => 'sometimes|nullable|numeric|min:0',
            'multipliers'           => 'sometimes|nullable|array',
            'logo_url'              => 'sometimes|nullable|string|max:500',
            'is_active'             => 'sometimes|boolean',
            'metadata'              => 'sometimes|nullable|array',
        ];
    }
    
}
