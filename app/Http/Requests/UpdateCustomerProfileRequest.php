<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'array'],
            'billing_address.street' => ['nullable', 'string', 'max:150'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address.city' => ['nullable', 'string', 'max:100'],
        ];
    }
}
