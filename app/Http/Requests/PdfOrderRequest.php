<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PdfOrderRequest extends FormRequest
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
            'customer.name' => ['required', 'string', 'max:150'],
            'customer.email' => ['required', 'email:rfc', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:40'],
            'customer.company' => ['nullable', 'string', 'max:150'],
            'customer.nip' => ['required_if:invoice_required,true', 'nullable', 'string', 'max:20'],
            'customer.notes' => ['nullable', 'string', 'max:5000'],
            'color_mode' => ['required', 'string', 'in:bw,color'],
            'sided' => ['required', 'string', 'in:simplex,duplex'],
            'finish' => ['required', 'string', 'in:none,staples,folder,channel'],
            'copies' => ['required', 'integer', 'min:1', 'max:50'],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel,courier'],
            'shipping_address' => ['required_unless:shipping_method,pickup', 'array', 'max:10'],
            'shipping_address.point_code' => ['required_if:shipping_method,parcel', 'string', 'max:30'],
            'shipping_address.name' => ['required_if:shipping_method,parcel', 'string', 'max:100'],
            'shipping_address.address' => ['required_unless:shipping_method,pickup', 'string', 'max:255'],
            'shipping_address.city' => ['required_unless:shipping_method,pickup', 'string', 'max:100'],
            'shipping_address.post_code' => ['required_unless:shipping_method,pickup', 'string', 'max:20'],
            'requested_by_date' => ['nullable', 'date', 'after_or_equal:today'],
            'invoice_required' => ['sometimes', 'boolean'],
            'marketing_consent' => ['sometimes', 'boolean'],
            'privacy_policy_accepted' => ['accepted'],
        ];
    }
}
