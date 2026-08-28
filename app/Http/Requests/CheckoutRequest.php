<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // The quotation being checked out
            'quotation_id'     => 'required|integer|exists:quotations,id',
            // user_id — will be from auth() in production; accepted here for dev
            'user_id'          => 'nullable|integer|exists:users,id',
            // Idempotency key supplied by client to prevent double-submit
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