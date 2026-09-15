<?php

namespace App\Services;

use App\Models\OrderFile;
use Illuminate\Validation\ValidationException;

class ThesisPricingService
{
    public function __construct(private readonly ConfiguratorSettings $settings) {}

    /**
     * @param  array{color_mode: string, sided: string, binding: string, cover: string, cover_text?: string|null, cover_color?: string|null, cover_title?: string|null, imprint_color?: string|null, university?: string|null, burn_cd: bool, spine_engraving: bool, spine_engraving_name?: string|null, copies: int|string, shipping_method: string}  $data
     * @return array{pages: int, print_total: float, binding_total: float, cover_total: float, spine_engraving_total: float, cd_total: float, unit_price: float, subtotal: float, shipping_total: float, total: float, configuration: array<string, mixed>}
     */
    public function calculate(OrderFile $file, array $data): array
    {
        $colorMode = (string) $data['color_mode'];
        $binding = (string) $data['binding'];
        $cover = (string) $data['cover'];
        $copies = (int) $data['copies'];
        $settings = $this->settings->printing('thesis');
        $bindings = $settings['bindings'];
        $covers = $settings['covers'];
        $coverTitles = $settings['cover_titles'];
        $imprintColors = $settings['imprint_colors'];
        $universities = $settings['universities'];
        $coverText = $cover === 'custom' ? ($data['cover_text'] ?? null) : null;
        $coverTitle = $cover === 'standard' ? ($data['cover_title'] ?? null) : null;
        $imprintColor = $cover !== 'none' ? ($data['imprint_color'] ?? null) : null;
        $university = $cover === 'standard' ? ($data['university'] ?? null) : null;
        $burnCd = (bool) $data['burn_cd'];
        $spineEngraving = (bool) ($data['spine_engraving'] ?? false);
        $spineEngravingName = $spineEngraving ? ($data['spine_engraving_name'] ?? null) : null;
        $cdPrice = $burnCd ? (float) $settings['cd']['price'] : 0.0;
        $spineEngravingPrice = $spineEngraving ? (float) $settings['spine_engraving']['price'] : 0.0;

        if (! isset($bindings[$binding], $covers[$cover])) {
            throw ValidationException::withMessages(['configuration' => 'Wybrano nieprawidłową konfigurację oprawy.']);
        }

        $bwPagePrice = (float) $settings['page_prices']['bw'];
        $colorPagePrice = (float) $settings['page_prices']['color'];
        $unitPrintTotal = $colorMode === 'mixed'
            ? ($file->bw_pages * $bwPagePrice) + ($file->color_pages * $colorPagePrice)
            : $file->pages * $bwPagePrice;
        $printTotal = round($unitPrintTotal * $copies, 2);
        $bindingTotal = round((float) $bindings[$binding]['price'] * $copies, 2);
        $coverTotal = round((float) $covers[$cover]['price'] * $copies, 2);
        $spineEngravingTotal = round($spineEngravingPrice * $copies, 2);
        $cdTotal = round($cdPrice, 2);
        $unitPrice = $unitPrintTotal + (float) $bindings[$binding]['price'] + (float) $covers[$cover]['price'] + $spineEngravingPrice;
        $subtotal = round($unitPrice * $copies, 2);
        $shippingTotal = (float) config("business.shipping.{$data['shipping_method']}");

        return [
            'pages' => $file->pages,
            'color_pages' => $file->color_pages,
            'bw_pages' => $file->bw_pages,
            'print_total' => $printTotal,
            'binding_total' => $bindingTotal,
            'cover_total' => $coverTotal,
            'spine_engraving_total' => $spineEngravingTotal,
            'cd_total' => $cdTotal,
            'unit_price' => round($unitPrice, 2),
            'subtotal' => round($subtotal + $cdTotal, 2),
            'shipping_total' => $shippingTotal,
            'total' => round($subtotal + $cdTotal + $shippingTotal, 2),
            'configuration' => [
                'color_mode' => $colorMode,
                'sided' => $data['sided'],
                'binding' => $binding,
                'binding_label' => $bindings[$binding]['label'],
                'cover' => $cover,
                'cover_label' => $covers[$cover]['label'],
                'cover_text' => $coverText,
                'cover_color' => $data['cover_color'] ?? null,
                'cover_title' => $coverTitle,
                'cover_title_label' => $coverTitle ? ($coverTitles[$coverTitle] ?? $coverTitle) : null,
                'imprint_color' => $imprintColor,
                'imprint_color_label' => $imprintColor ? ($imprintColors[$imprintColor]['label'] ?? $imprintColor) : null,
                'university' => $university,
                'university_label' => $university ? ($universities[$university] ?? $university) : null,
                'burn_cd' => $burnCd,
                'burn_cd_label' => $burnCd ? $settings['cd']['label'] : 'Bez nagrania na CD',
                'spine_engraving' => $spineEngraving,
                'spine_engraving_label' => $spineEngraving ? $settings['spine_engraving']['label'] : 'Bez grawerowania na grzbiecie',
                'spine_engraving_name' => $spineEngravingName,
                'spine_engraving_total' => $spineEngravingTotal,
                'cd_total' => $cdTotal,
                'copies' => $copies,
            ],
        ];
    }
}
