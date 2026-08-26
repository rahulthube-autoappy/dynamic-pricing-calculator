<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cartItems' => 'required|array',
            'selectedPlanId' => 'required|string',
            'customerDetails' => 'required|array',
            'appliedDiscounts' => 'nullable|array'
        ];
    }
}
