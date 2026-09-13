<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class B2bUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,ai,cdr,png,jpg,jpeg', 'max:20480'],
        ];
    }
}
