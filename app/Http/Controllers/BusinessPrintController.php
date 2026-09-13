<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class BusinessPrintController extends Controller
{
    /**
     * @var array<string, array{slug: string, fallback: string}>
     */
    private const B2B_PRODUCTS = [
        'visits' => ['slug' => 'wizytowki', 'fallback' => "images/produkty/wizytowki'.png"],
        'leaflets' => ['slug' => 'ulotki', 'fallback' => 'images/produkty/ulotki.png'],
        'posters' => ['slug' => 'plakaty', 'fallback' => 'images/produkty/plakaty.png'],
        'banners' => ['slug' => 'banery', 'fallback' => 'images/produkty/banery.png'],
        'rollups' => ['slug' => 'rollupy', 'fallback' => 'images/produkty/rollupy.png'],
        'documents' => ['slug' => 'druk', 'fallback' => 'images/produkty/druk.png'],
        'billboards' => ['slug' => 'billboardy', 'fallback' => 'images/produkty/billboardy.png'],
        'canvases' => ['slug' => 'fotoobrazy', 'fallback' => 'images/produkty/fotoobrazy.png'],
        'wallpapers' => ['slug' => 'fototapety', 'fallback' => 'images/produkty/fototapety.png'],
        'calendars' => ['slug' => 'kalendarze', 'fallback' => 'images/produkty/kalendarze-spiralowane.png'],
        'stickers' => ['slug' => 'naklejki', 'fallback' => 'images/produkty/naklejki.png'],
        'plaques' => ['slug' => 'tabliczki', 'fallback' => 'images/produkty/tabliczki-grawerowane.png'],
        'cad' => ['slug' => 'rysunki-cad', 'fallback' => 'images/produkty/rysunki-plany-mapycad.png'],
        'copies' => ['slug' => 'ksero', 'fallback' => 'images/produkty/ksero.png'],
        'scans' => ['slug' => 'skanowanie', 'fallback' => 'images/produkty/skanowanie.png'],
        'id-photos' => ['slug' => 'zdjecia-dokumenty', 'fallback' => 'images/produkty/zdjecia-do-dokumentow.png'],
        'stamps' => ['slug' => 'pieczatki', 'fallback' => 'images/produkty/pieczatki.png'],
        'design' => ['slug' => 'projektowanie', 'fallback' => 'images/produkty/projektowanie-graficzne.png'],
        'extras' => ['slug' => 'uslugi-dodatkowe', 'fallback' => 'images/produkty/druk.png'],
        'binding' => ['slug' => 'oprawa-prac', 'fallback' => 'images/produkty/oprawa-prac-i-bindowanie.png'],
    ];

    public function __invoke(): View
    {
        $products = Product::query()
            ->whereIn('slug', collect(self::B2B_PRODUCTS)->pluck('slug'))
            ->get(['slug', 'image_path'])
            ->keyBy('slug');

        $images = collect(self::B2B_PRODUCTS)
            ->mapWithKeys(function (array $definition, string $cardId) use ($products): array {
                $product = $products->get($definition['slug']);

                return [$cardId => $product?->imageUrl() ?? asset($definition['fallback'])];
            })
            ->all();

        return view('druk-dla-firm', ['b2bProductImages' => $images]);
    }
}
