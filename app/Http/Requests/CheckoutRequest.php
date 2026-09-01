<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'quotation_id'     => 'required|string|exists:quotations,id',
            'user_id'          => 'nullable|integer|exists:users,id',
            'idempotency_key'  => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_id.required' => 'A quotation_id is required to checkout.',
            'idempotency_key.required' => 'An idempotency_key is required to prevent duplicate orders.',
        ];
    }
}