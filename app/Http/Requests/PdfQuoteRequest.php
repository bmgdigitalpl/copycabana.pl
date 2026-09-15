<?php

namespace App\Http\Requests;

use App\Services\ConfiguratorSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
    public function rules(ConfiguratorSettings $configurator): array
    {
        $settings = $configurator->printing('pdf');

        return [
            'upload_token' => ['required', 'string', 'size:64'],
            'color_mode' => ['required', 'string', 'in:bw,mixed'],
            'sided' => ['required', 'string', 'in:simplex,duplex'],
            'finish' => ['required', 'string', Rule::in(array_keys($settings['finishes']))],
            'copies' => ['required', 'integer', 'min:1', 'max:'.$settings['max_copies']],
            'shipping_method' => ['required', 'string', 'in:pickup,parcel,courier'],
        ];
    }
}
