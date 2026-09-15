<?php

namespace App\Http\Requests;

use App\Services\ConfiguratorSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThesisOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'spine_engraving' => $this->input('spine_engraving', false),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(ConfiguratorSettings $configurator): array
    {
        $settings = $configurator->printing('thesis');
        $universities = array_keys($settings['universities']);
        $coverTitles = array_keys($settings['cover_titles']);
        $imprintColors = array_keys($settings['imprint_colors']);

        return [
            'upload_token' => ['required', 'string', 'size:64'],
            'customer.name' => ['required', 'string', 'max:150'],
            'customer.email' => ['required', 'email:rfc', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:40'],
            'customer.company' => ['nullable', 'string', 'max:150'],
            'customer.nip' => ['required_if:invoice_required,true', 'nullable', 'string', 'max:20'],
            'customer.notes' => ['nullable', 'string', 'max:5000'],
            'color_mode' => ['required', 'string', 'in:bw,mixed'],
            'sided' => ['required', 'string', 'in:simplex,duplex'],
            'binding' => ['required', 'string', Rule::in(array_keys($settings['bindings']))],
            'cover' => ['required', 'string', Rule::in(array_keys($settings['covers']))],
            'cover_text' => ['nullable', 'required_if:cover,custom', 'string', 'max:200'],
            'cover_color' => ['nullable', 'string', Rule::in(array_keys($settings['cover_colors']))],
            'cover_title' => ['nullable', 'required_if:cover,standard', 'string', Rule::in($coverTitles)],
            'imprint_color' => ['nullable', 'required_if:cover,standard,custom', 'string', Rule::in($imprintColors)],
            'university' => ['nullable', 'required_if:cover,standard', 'string', Rule::in($universities)],
            'burn_cd' => ['required', 'boolean'],
            'spine_engraving' => ['required', 'boolean'],
            'spine_engraving_name' => ['nullable', Rule::requiredIf(fn (): bool => $this->boolean('spine_engraving')), 'string', 'max:150'],
            'copies' => ['required', 'integer', 'min:1', 'max:'.$settings['max_copies']],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel'],
            'shipping_address' => ['required_if:shipping_method,parcel', 'array'],
            'shipping_address.point_code' => ['required_if:shipping_method,parcel', 'string', 'max:30'],
            'shipping_address.name' => ['required_if:shipping_method,parcel', 'string', 'max:100'],
            'shipping_address.address' => ['required_if:shipping_method,parcel', 'string', 'max:255'],
            'shipping_address.city' => ['required_if:shipping_method,parcel', 'string', 'max:100'],
            'shipping_address.post_code' => ['required_if:shipping_method,parcel', 'string', 'max:20'],
            'requested_by_date' => ['nullable', 'date', 'after_or_equal:today'],
            'invoice_required' => ['sometimes', 'boolean'],
            'marketing_consent' => ['sometimes', 'boolean'],
            'privacy_policy_accepted' => ['accepted'],
        ];
    }
}
