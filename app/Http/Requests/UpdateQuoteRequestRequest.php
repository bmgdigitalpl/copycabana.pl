<?php

namespace App\Http\Requests;

use App\Enums\QuoteRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(QuoteRequestStatus::class)],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
