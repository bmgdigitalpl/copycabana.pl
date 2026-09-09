<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductConfigurationRequest extends FormRequest
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
            'product_slug' => ['required', Rule::exists('products', 'slug')->where('is_active', true)],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'option_value_ids' => ['nullable', 'array', 'max:30'],
            'option_value_ids.*' => ['integer', 'exists:option_values,id'],
            'configuration' => ['nullable', 'array'],
        ];
    }
}
