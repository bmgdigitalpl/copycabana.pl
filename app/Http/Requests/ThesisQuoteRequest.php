<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThesisQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'upload_token' => ['required', 'string', 'size:64'],
            'color_mode' => ['required', 'string', 'in:bw,color'],
            'sided' => ['required', 'string', 'in:simplex,duplex'],
            'binding' => ['required', 'string', 'in:soft,channel,hard'],
            'cover' => ['required', 'string', 'in:none,standard,custom'],
            'cover_text' => ['nullable', 'required_if:cover,custom', 'string', 'max:200'],
            'cover_color' => ['nullable', 'string', 'in:granat,magenta,zolty,zielony,niebieski'],
            'copies' => ['required', 'integer', 'min:1', 'max:10'],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel'],
        ];
    }
}
