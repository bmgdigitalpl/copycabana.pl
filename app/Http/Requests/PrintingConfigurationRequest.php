<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintingConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'page_prices' => ['required', 'array:bw,color'],
            'page_prices.bw' => ['required', 'numeric', 'between:0,100000'],
            'page_prices.color' => ['required', 'numeric', 'between:0,100000'],
            'max_copies' => ['required', 'integer', 'between:1,1000'],
        ];

        $groups = $this->route('type') === 'thesis'
            ? ['bindings', 'covers', 'universities', 'cover_titles', 'imprint_colors', 'cover_colors']
            : ['finishes'];

        foreach ($groups as $group) {
            $rules[$group] = ['required', 'array', 'min:1', 'max:100'];
            $rules[$group.'.*'] = ['array:key,label,price,hint,hex'];
            $rules[$group.'.*.key'] = ['required', 'alpha_dash:ascii', 'max:80', 'distinct:strict'];
            $rules[$group.'.*.label'] = ['required', 'string', 'max:255'];
            if (in_array($group, ['bindings', 'covers', 'finishes'], true)) {
                $rules[$group.'.*.price'] = ['required', 'numeric', 'between:0,100000'];
                $rules[$group.'.*.hint'] = ['nullable', 'string', 'max:255'];
            }
            if (in_array($group, ['imprint_colors', 'cover_colors'], true)) {
                $rules[$group.'.*.hex'] = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
            }
        }

        if ($this->route('type') === 'thesis') {
            $rules['covers.*.key'][] = Rule::in(['none', 'standard', 'custom']);
            foreach (['cd', 'spine_engraving'] as $extra) {
                $rules[$extra] = ['required', 'array:label,price'];
                $rules[$extra.'.label'] = ['required', 'string', 'max:255'];
                $rules[$extra.'.price'] = ['required', 'numeric', 'between:0,100000'];
            }
        }

        return $rules;
    }
}
