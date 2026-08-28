<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:100|unique:plans,code',
            'price'       => 'nullable|numeric|min:0',
            'max_tasks'   => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'features'    => 'nullable|array',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ];
    }
    
}
