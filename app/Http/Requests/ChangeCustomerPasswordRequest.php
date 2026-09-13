<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangeCustomerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
