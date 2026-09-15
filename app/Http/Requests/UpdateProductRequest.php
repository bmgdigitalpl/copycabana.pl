<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

class UpdateProductRequest extends ProductImageRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'required', 'string', 'max:5000'],
            'starting_price' => ['sometimes', 'nullable', 'numeric', 'between:0,100000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'between:0,10000'],
            'fields' => ['sometimes', 'array', 'min:1', 'max:30'],
            'fields.*' => ['array:key,label,type,required,min,max,price,values'],
            'fields.*.key' => ['required', 'alpha_dash:ascii', 'max:80', 'distinct:strict'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.type' => ['required', 'in:chips,number'],
            'fields.*.required' => ['required', 'boolean'],
            'fields.*.min' => ['nullable', 'integer', 'between:1,100000'],
            'fields.*.max' => ['nullable', 'integer', 'between:1,100000'],
            'fields.*.price' => ['nullable', 'numeric', 'between:0,100000'],
            'fields.*.values' => ['nullable', 'array', 'max:100'],
            'fields.*.values.*' => ['array:value,label,price'],
            'fields.*.values.*.value' => ['required', 'string', 'max:100'],
            'fields.*.values.*.label' => ['required', 'string', 'max:150'],
            'fields.*.values.*.price' => ['required', 'numeric', 'between:0,100000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach ($this->input('fields', []) as $index => $field) {
                if ($field['type'] === 'number' && (! isset($field['min'], $field['max']) || $field['min'] > $field['max'])) {
                    $validator->errors()->add("fields.{$index}.max", 'Podaj poprawny zakres: minimum nie może przekraczać maksimum.');
                }
                if ($field['type'] === 'chips') {
                    $values = collect($field['values'] ?? [])->pluck('value');
                    if ($values->isEmpty() || $values->uniqueStrict()->count() !== $values->count()) {
                        $validator->errors()->add("fields.{$index}.values", 'Podaj co najmniej jeden wariant. Kody wariantów muszą być unikalne w obrębie opcji.');
                    }
                }
                if ($field['key'] === 'qty') {
                    foreach ($field['values'] ?? [] as $value) {
                        if (filter_var($value['value'], FILTER_VALIDATE_INT) === false || (int) $value['value'] < 1 || (int) $value['value'] > 100000) {
                            $validator->errors()->add("fields.{$index}.values", 'Nakład musi być dodatnią liczbą całkowitą do 100000.');
                        }
                    }
                }
            }
        }];
    }
}
