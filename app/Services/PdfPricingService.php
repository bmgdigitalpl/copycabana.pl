<?php

namespace App\Services;

use App\Models\OrderFile;
use Illuminate\Validation\ValidationException;

class PdfPricingService
{
    /**
     * @param  array{color_mode: string, sided: string, finish: string, copies: int|string, shipping_method: string}  $data
     * @return array{pages: int, print_total: float, finish_total: float, unit_price: float, subtotal: float, shipping_total: float, total: float, configuration: array<string, mixed>}
     */
    public function calculate(OrderFile $file, array $data): array
    {
        $finish = (string) $data['finish'];
        $finishes = config('business.pdf.finishes', []);

        if (! isset($finishes[$finish])) {
            throw ValidationException::withMessages(['finish' => 'Wybrano nieprawidłowe wykończenie dokumentu.']);
        }

        $copies = (int) $data['copies'];
        $pagePrice = (float) config("business.pdf.page_prices.{$data['color_mode']}");
        $unitPrintTotal = round($file->pages * $pagePrice, 2);
        $printTotal = round($unitPrintTotal * $copies, 2);
        $finishTotal = round((float) $finishes[$finish]['price'] * $copies, 2);
        $subtotal = round($printTotal + $finishTotal, 2);
        $shippingTotal = (float) config("business.shipping.{$data['shipping_method']}");

        return [
            'pages' => $file->pages,
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
