<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingCategoryRequest extends FormRequest
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
            'name'        => 'sometimes|required|string|max:100',
            'code'        => 'sometimes|required|string|max:100|unique:pricing_categories,code,' . $this->route('pricing_category'),
            'description' => 'sometimes|nullable|string',
            'is_active'   => 'sometimes|boolean',
            'sort_order'  => 'sometimes|integer',
        ];
    }
    
}
