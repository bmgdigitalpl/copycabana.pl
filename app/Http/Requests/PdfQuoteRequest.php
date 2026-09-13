<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PdfQuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'finish' => ['required', 'string', 'in:none,staples,folder,channel'],
            'copies' => ['required', 'integer', 'min:1', 'max:50'],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel,courier'],
        ];
    }
}
