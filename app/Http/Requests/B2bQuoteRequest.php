<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class B2bQuoteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $productMap = (array) config('business.b2b_products', []);
        $items = collect($this->input('items', []))->map(function (array $item) use ($productMap): array {
            $productKey = $item['product_key'] ?? $item['product'] ?? null;
            $configuration = $item['configuration'] ?? $item['params'] ?? [];
            $configuration = is_array($configuration)
                ? array_map(fn (mixed $value): ?string => is_scalar($value) ? (string) $value : null, $configuration)
                : [];

            return [
                'product_key' => $productKey,
                'product_slug' => is_string($productKey) ? ($productMap[$productKey] ?? null) : null,
                'configuration' => is_array($configuration) ? $configuration : [],
                'quantity' => $item['quantity'] ?? (is_array($configuration) ? ($configuration['qty'] ?? 1) : 1),
                'help_wanted' => $item['help_wanted'] ?? $item['helpWanted'] ?? false,
                'upload_token' => $item['upload_token'] ?? $item['uploadToken'] ?? null,
            ];
        })->values()->all();

        $this->merge(['items' => $items]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'customer.name' => ['required', 'string', 'max:150'],
            'customer.email' => ['required', 'email:rfc', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:40'],
            'customer.company' => ['required', 'string', 'max:150'],
            'customer.nip' => ['required_if:invoice_required,true', 'nullable', 'string', 'max:20', function (string $attribute, mixed $value, Closure $fail): void {
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
            'privacy_policy_accepted' => ['accepted'],
            'marketing_consent' => ['sometimes', 'boolean'],
            'invoice_required' => ['sometimes', 'boolean'],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel,courier'],
            'shipping_address' => ['required_unless:shipping_method,pickup', 'array', 'max:10'],
            'shipping_address.point_code' => ['required_if:shipping_method,parcel', 'string', 'max:30'],
            'shipping_address.name' => ['required_if:shipping_method,parcel', 'string', 'max:100'],
            'shipping_address.address' => ['required_unless:shipping_method,pickup', 'string', 'max:255'],
            'shipping_address.city' => ['required_unless:shipping_method,pickup', 'string', 'max:100'],
            'shipping_address.post_code' => ['required_unless:shipping_method,pickup', 'string', 'max:20'],
            'requested_by_date' => ['nullable', 'date', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_key' => ['required', Rule::in(array_keys((array) config('business.b2b_products', [])))],
            'items.*.product_slug' => ['required', Rule::in(array_values((array) config('business.b2b_products', [])))],
            'items.*.configuration' => ['nullable', 'array', 'max:30'],
            'items.*.configuration.*' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.help_wanted' => ['sometimes', 'boolean'],
            'items.*.upload_token' => ['nullable', 'string', 'size:64'],
            'brief' => ['nullable', 'array', 'max:10'],
            'brief.type' => ['nullable', 'string', 'max:100'],
            'brief.description' => ['nullable', 'string', 'max:5000'],
            'brief.quantity' => ['nullable', 'string', 'max:100'],
            'brief.requested_by_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
