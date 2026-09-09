<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))->map(function (array $item): array {
            return [
                'product_slug' => $item['product_slug'] ?? $item['productId'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'option_value_ids' => $item['option_value_ids'] ?? [],
                'configuration' => $item['configuration'] ?? $item['options'] ?? [],
            ];
        })->values()->all();

        $this->merge(['items' => $items]);
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'customer.name' => ['required', 'string', 'max:150'],
            'customer.email' => ['required', 'email:rfc', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:40'],
            'customer.company' => ['nullable', 'string', 'max:150'],
            'customer.nip' => ['nullable', 'string', 'max:20', function (string $attribute, mixed $value, Closure $fail): void {
                $nip = preg_replace('/[^0-9]/', '', (string) $value);
                if (strlen($nip) !== 10) {
                    $fail('Podaj prawidłowy numer NIP.');

                    return;
                }
                $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
                $sum = 0;
                for ($index = 0; $index < 9; $index++) {
                    $sum += (int) $nip[$index] * $weights[$index];
                }
                if ($sum % 11 !== (int) $nip[9]) {
                    $fail('Podaj prawidłowy numer NIP.');
                }
            }],
            'customer.notes' => ['nullable', 'string', 'max:5000'],
            'billing_address' => ['nullable', 'array'],
            'shipping_address' => ['nullable', 'array'],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel,courier'],
            'invoice_required' => ['sometimes', 'boolean'],
            'marketing_consent' => ['sometimes', 'boolean'],
            'privacy_policy_accepted' => ['accepted'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_slug' => ['required', 'string', 'exists:products,slug'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.option_value_ids' => ['nullable', 'array', 'max:30'],
            'items.*.option_value_ids.*' => ['integer', 'exists:option_values,id'],
            'items.*.configuration' => ['nullable', 'array'],
        ];
    }
}
