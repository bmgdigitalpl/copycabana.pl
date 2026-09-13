<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'alpha_dash', 'max:100', 'unique:options,code,'.$this->route('option')?->id],
            'input_type' => ['required', 'in:select,multi'],
            'pricing_model' => ['required', 'in:fixed,percentage'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'products' => ['nullable', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.label' => ['required', 'string', 'max:100'],
            'values.*.value' => ['required', 'alpha_dash', 'max:100'],
            'values.*.price_modifier' => ['required', 'numeric', 'between:-100000,100000'],
            'values.*.is_active' => ['sometimes', 'boolean'],
            'values.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
