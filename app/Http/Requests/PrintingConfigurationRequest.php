<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

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
            $allowedKeys = ['key', 'label'];
            if (in_array($group, ['bindings', 'covers', 'finishes'], true)) {
                $allowedKeys = [...$allowedKeys, 'price', 'hint'];
            }
            if (in_array($group, ['imprint_colors', 'cover_colors'], true)) {
                $allowedKeys[] = 'hex';
            }
            if ($group === 'cover_colors') {
                $allowedKeys = [...$allowedKeys, 'photo', 'existing_photo'];
            }

            $rules[$group] = ['required', 'array', 'min:1', 'max:100'];
            $rules[$group.'.*'] = ['array:'.implode(',', $allowedKeys)];
            $rules[$group.'.*.key'] = ['required', 'alpha_dash:ascii', 'max:80', 'distinct:strict'];
            $rules[$group.'.*.label'] = ['required', 'string', 'max:255'];
            if (in_array($group, ['bindings', 'covers', 'finishes'], true)) {
                $rules[$group.'.*.price'] = ['required', 'numeric', 'between:0,100000'];
                $rules[$group.'.*.hint'] = ['nullable', 'string', 'max:255'];
            }
            if (in_array($group, ['imprint_colors', 'cover_colors'], true)) {
                $rules[$group.'.*.hex'] = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
            }
            if ($group === 'cover_colors') {
                // Real photo of the blank cover for this color. Uploading one replaces the
                // code-generated preview mockup on /prace-dyplomowe with the actual photo.
                $rules[$group.'.*.photo'] = ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('5mb')];
                $rules[$group.'.*.existing_photo'] = ['nullable', 'string', 'max:255'];
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
