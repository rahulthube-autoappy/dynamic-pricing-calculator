<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
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
            'code'        => 'sometimes|required|string|max:100|unique:plans,code,' . $this->route('plan'),
            'price'       => 'sometimes|required|numeric|min:0',
            'max_tasks'   => 'sometimes|nullable|integer|min:1',
            'description' => 'sometimes|nullable|string',
            'features'    => 'sometimes|nullable|array',
            'is_active'   => 'sometimes|boolean',
            'sort_order'  => 'sometimes|integer',
        ];
    }
    
}
