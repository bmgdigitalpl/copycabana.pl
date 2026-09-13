<?php

namespace App\Services;

use App\Models\OrderFile;
use Illuminate\Validation\ValidationException;

class ThesisPricingService
{
    /**
     * @param  array{color_mode: string, sided: string, binding: string, cover: string, cover_text?: string|null, cover_color?: string|null, copies: int|string, shipping_method: string}  $data
     * @return array{pages: int, print_total: float, binding_total: float, cover_total: float, unit_price: float, subtotal: float, shipping_total: float, total: float, configuration: array<string, mixed>}
     */
    public function calculate(OrderFile $file, array $data): array
    {
        $colorMode = (string) $data['color_mode'];
        $binding = (string) $data['binding'];
        $cover = (string) $data['cover'];
        $copies = (int) $data['copies'];
        $bindings = config('business.thesis.bindings', []);
        $covers = config('business.thesis.covers', []);

        if (! isset($bindings[$binding], $covers[$cover])) {
            throw ValidationException::withMessages(['configuration' => 'Wybrano nieprawidłową konfigurację oprawy.']);
        }

        $pagePrice = (float) config("business.thesis.page_prices.{$colorMode}");
        $printTotal = round($file->pages * $pagePrice * $copies, 2);
        $bindingTotal = round((float) $bindings[$binding]['price'] * $copies, 2);
        $coverTotal = round((float) $covers[$cover]['price'] * $copies, 2);
        $unitPrice = ($file->pages * $pagePrice) + (float) $bindings[$binding]['price'] + (float) $covers[$cover]['price'];
        $subtotal = round($unitPrice * $copies, 2);
        $shippingTotal = (float) config("business.shipping.{$data['shipping_method']}");

        return [
            'pages' => $file->pages,
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'cover_total' => $coverTotal,
            'unit_price' => round($unitPrice, 2),
            'subtotal' => $subtotal,
            'shipping_total' => $shippingTotal,
            'total' => round($subtotal + $shippingTotal, 2),
            'configuration' => [
                'color_mode' => $colorMode,
                'sided' => $data['sided'],
                'binding' => $binding,
                'binding_label' => $bindings[$binding]['label'],
                'cover' => $cover,
                'cover_label' => $covers[$cover]['label'],
                'cover_text' => $data['cover_text'] ?? null,
                'cover_color' => $data['cover_color'] ?? null,
                'copies' => $copies,
            ],
        ];
    }
}
