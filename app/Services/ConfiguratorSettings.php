<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ConfiguratorSettings
{
    /** @return array<string, mixed> */
    public function printing(string $type): array
    {
        $slug = $type === 'thesis' ? 'praca-dyplomowa' : 'druk';
        $product = Product::query()->active()->where('slug', $slug)->first();
        $settings = $product?->configuration['printing'] ?? null;

        if (! is_array($settings)) {
            throw ValidationException::withMessages(['configuration' => 'Konfigurator nie jest obecnie dostępny. Skontaktuj się z pracownią.']);
        }

        return $settings;
    }
}
