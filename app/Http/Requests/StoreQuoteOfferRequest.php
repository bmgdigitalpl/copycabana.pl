<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuoteOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'subtotal' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'shipping_total' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'total' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', 'string', 'size:3', Rule::in([config('business.currency')])],
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['subtotal', 'shipping_total', 'total'])) {
                return;
            }

            $expectedTotal = round((float) $this->input('subtotal') + (float) $this->input('shipping_total'), 2);
            if ($expectedTotal !== round((float) $this->input('total'), 2)) {
                $validator->errors()->add('total', 'Suma netto i dostawy musi odpowiadać kwocie oferty.');
            }
        }];
    }
}
