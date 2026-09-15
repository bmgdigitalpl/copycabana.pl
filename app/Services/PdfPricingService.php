<?php

namespace App\Services;

use App\Models\OrderFile;
use Illuminate\Validation\ValidationException;

class PdfPricingService
{
    public function __construct(private readonly ConfiguratorSettings $settings) {}

    /**
     * @param  array{color_mode: string, sided: string, finish: string, copies: int|string, shipping_method: string}  $data
     * @return array{pages: int, print_total: float, finish_total: float, unit_price: float, subtotal: float, shipping_total: float, total: float, configuration: array<string, mixed>}
     */
    public function calculate(OrderFile $file, array $data): array
    {
        $finish = (string) $data['finish'];
        $settings = $this->settings->printing('pdf');
        $finishes = $settings['finishes'];

        if (! isset($finishes[$finish])) {
            throw ValidationException::withMessages(['finish' => 'Wybrano nieprawidłowe wykończenie dokumentu.']);
        }

        $copies = (int) $data['copies'];
        $bwPagePrice = (float) $settings['page_prices']['bw'];
        $colorPagePrice = (float) $settings['page_prices']['color'];
        $unitPrintTotal = round($data['color_mode'] === 'mixed'
            ? ($file->bw_pages * $bwPagePrice) + ($file->color_pages * $colorPagePrice)
            : $file->pages * $bwPagePrice, 2);
        $printTotal = round($unitPrintTotal * $copies, 2);
        $finishTotal = round((float) $finishes[$finish]['price'] * $copies, 2);
        $subtotal = round($printTotal + $finishTotal, 2);
        $shippingTotal = (float) config("business.shipping.{$data['shipping_method']}");

        return [
            'pages' => $file->pages,
            'color_pages' => $file->color_pages,
            'bw_pages' => $file->bw_pages,
            'print_total' => $printTotal,
            'finish_total' => $finishTotal,
            'unit_price' => round($unitPrintTotal + (float) $finishes[$finish]['price'], 2),
            'subtotal' => $subtotal,
            'shipping_total' => $shippingTotal,
            'total' => round($subtotal + $shippingTotal, 2),
            'configuration' => [
                'color_mode' => $data['color_mode'],
                'sided' => $data['sided'],
                'finish' => $finish,
                'finish_label' => $finishes[$finish]['label'],
                'copies' => $copies,
            ],
        ];
    }
}
