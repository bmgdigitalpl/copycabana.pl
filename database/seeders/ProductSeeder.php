<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chips = static fn (array $values): array => array_map(
            static fn (string $value): array => ['value' => $value, 'label' => $value],
            $values,
        );
        $quantity = static fn (string $label = 'Liczba sztuk', int $max = 1000): array => [
            'key' => 'qty',
            'label' => $label,
            'type' => 'number',
            'required' => true,
            'min' => 1,
            'max' => $max,
        ];
        $generic = static fn (string $icon): array => [
            'icon' => $icon,
            'fields' => [$quantity()],
        ];
        $businessConfigurators = [
            'wizytowki' => [
                'icon' => 'fa-id-card',
                'fields' => [
                    ['key' => 'qty', 'label' => 'Nakład', 'type' => 'chips', 'required' => true, 'values' => $chips(['100', '250', '500', '1000', '2000'])],
                    ['key' => 'format', 'label' => 'Format', 'type' => 'chips', 'required' => true, 'values' => $chips(['90×50 mm', '54×85 mm'])],
                    ['key' => 'printing', 'label' => 'Zadruk', 'type' => 'chips', 'required' => true, 'values' => $chips(['jednostronnie', 'dwustronnie'])],
                    ['key' => 'paper', 'label' => 'Papier', 'type' => 'chips', 'required' => true, 'values' => $chips(['300 g jedwab', '350 g biały', '350 g tworzywo'])],
                    ['key' => 'finish', 'label' => 'Wykończenie', 'type' => 'chips', 'required' => true, 'values' => $chips(['bez', 'lakier UV', 'folia matowa'])],
                    ['key' => 'designs', 'label' => 'Liczba projektów', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 10],
                ],
            ],
            'ulotki' => [
                'icon' => 'fa-folder-open',
                'fields' => [
                    ['key' => 'qty', 'label' => 'Nakład', 'type' => 'chips', 'required' => true, 'values' => $chips(['250', '500', '1000', '2500', '5000'])],
                    ['key' => 'format', 'label' => 'Format', 'type' => 'chips', 'required' => true, 'values' => $chips(['A6', 'A5', 'A4'])],
                    ['key' => 'paper', 'label' => 'Papier', 'type' => 'chips', 'required' => true, 'values' => $chips(['170 g', '250 g kreda', '350 g'])],
                    ['key' => 'printing', 'label' => 'Zadruk', 'type' => 'chips', 'required' => true, 'values' => $chips(['1/1 czarno-biały', '4/4 kolor'])],
                    ['key' => 'fold', 'label' => 'Składanie', 'type' => 'chips', 'required' => true, 'values' => $chips(['bez składania', 'pół na pół', 'łamane na 3'])],
                ],
            ],
            'plakaty' => [
                'icon' => 'fa-image',
                'fields' => [
                    ['key' => 'qty', 'label' => 'Liczba sztuk', 'type' => 'chips', 'required' => true, 'values' => $chips(['1', '10', '25', '50', '100'])],
                    ['key' => 'format', 'label' => 'Format', 'type' => 'chips', 'required' => true, 'values' => $chips(['A3', 'A2', 'A1', 'B2'])],
                    ['key' => 'substrate', 'label' => 'Podłoże', 'type' => 'chips', 'required' => true, 'values' => $chips(['papier 170 g', 'papier 250 g', 'karton 300 g'])],
                ],
            ],
            'banery' => [
                'icon' => 'fa-flag',
                'fields' => [
                    ['key' => 'width', 'label' => 'Szerokość (cm)', 'type' => 'number', 'required' => true, 'min' => 50, 'max' => 500],
                    ['key' => 'height', 'label' => 'Wysokość (cm)', 'type' => 'number', 'required' => true, 'min' => 50, 'max' => 300],
                    ['key' => 'material', 'label' => 'Materiał', 'type' => 'chips', 'required' => true, 'values' => $chips(['siateczka', 'baner 510 g', 'baner 440 g'])],
                    ['key' => 'finish', 'label' => 'Wykończenie', 'type' => 'chips', 'required' => true, 'values' => $chips(['szwy z oczkami', 'taśma 4 cm', 'bez wykończenia'])],
                    ['key' => 'qty', 'label' => 'Liczba sztuk', 'type' => 'chips', 'required' => true, 'values' => $chips(['1', '2', '5', '10'])],
                ],
            ],
            'rollupy' => [
                'icon' => 'fa-user-tie',
                'fields' => [
                    ['key' => 'size', 'label' => 'Rozmiar', 'type' => 'chips', 'required' => true, 'values' => $chips(['85×200 cm', '100×200 cm'])],
                    ['key' => 'package', 'label' => 'Zakres', 'type' => 'chips', 'required' => true, 'values' => $chips(['konstrukcja + grafika', 'sama grafika'])],
                    ['key' => 'qty', 'label' => 'Liczba sztuk', 'type' => 'chips', 'required' => true, 'values' => $chips(['1', '2', '5'])],
                ],
            ],
            'druk' => [
                'icon' => 'fa-file-lines',
                'fields' => [
                    ['key' => 'color', 'label' => 'Kolor', 'type' => 'chips', 'required' => true, 'values' => $chips(['czarno-biały', 'kolor'])],
                    ['key' => 'sided', 'label' => 'Strony kartki', 'type' => 'chips', 'required' => true, 'values' => $chips(['jednostronnie', 'dwustronnie'])],
                    $quantity('Egzemplarze', 50),
                    ['key' => 'finish', 'label' => 'Wykończenie', 'type' => 'chips', 'required' => true, 'values' => $chips(['bez', 'spinanie', 'oprawa kanałowa', 'teczka'])],
                ],
            ],
            'billboardy' => $generic('fa-rectangle-ad'),
            'fotoobrazy' => $generic('fa-image'),
            'fototapety' => $generic('fa-expand'),
            'kalendarze' => $generic('fa-calendar-days'),
            'naklejki' => $generic('fa-note-sticky'),
            'tabliczki' => $generic('fa-sign'),
            'rysunki-cad' => $generic('fa-compass-drafting'),
            'ksero' => $generic('fa-copy'),
            'skanowanie' => $generic('fa-file-arrow-up'),
            'zdjecia-dokumenty' => $generic('fa-id-card'),
            'pieczatki' => $generic('fa-stamp'),
            'projektowanie' => $generic('fa-pen-ruler'),
            'uslugi-dodatkowe' => $generic('fa-layer-group'),
            'oprawa-prac' => $generic('fa-book-open'),
        ];
        $products = [
            ['wizytowki', 'Wizytówki', 'druk', 'Profesjonalne wizytówki w różnych formatach i wykończeniach.', 'wizytowki.png', 'options', 25],
            ['ulotki', 'Ulotki', 'druk', 'Ulotki reklamowe w różnych formatach i gramaturach papieru.', 'ulotki.png', 'options', 30],
            ['plakaty', 'Plakaty', 'reklama', 'Plakaty w formatach od A3 do B1 na papierze błysk i mat.', 'plakaty.png', 'options', 8],
            ['rollupy', 'Rollupy', 'reklama', 'Systemy wystawiennicze z grafiką na wymiar.', 'rollupy.png', 'options', null],
            ['banery', 'Banery', 'reklama', 'Banery wielkoformatowe z oczkami i wykończeniem.', 'banery.png', 'area', 30],
            ['billboardy', 'Billboardy', 'reklama', 'Reklama zewnętrzna w dużym formacie na Śląsku.', 'billboardy.png', 'area', 50],
            ['fotoobrazy', 'Fotoobrazy', 'foto', 'Fotoobrazy na płótnie i w ramach.', 'fotoobrazy.png', 'options', 89],
            ['fototapety', 'Fototapety', 'foto', 'Fototapety na wymiar do wnętrz i biur.', 'fototapety.png', 'area', 40],
            ['kalendarze', 'Kalendarze spiralowane', 'druk', 'Kalendarze ścienne i biurkowe z indywidualnym projektem.', 'kalendarze-spiralowane.png', 'options', 15],
            ['naklejki', 'Naklejki', 'druk', 'Naklejki w dowolnych kształtach i rozmiarach.', 'naklejki.png', 'options', 25],
            ['tabliczki', 'Tabliczki grawerowane', 'uslugi', 'Tabliczki informacyjne i grawerowane laserowo.', 'tabliczki-grawerowane.png', 'options', 30],
            ['rysunki-cad', 'Rysunki, plany, mapy', 'uslugi', 'Wydruki CAD w formatach A0–A4.', 'rysunki-plany-mapycad.png', 'unit', 0.50],
            ['ksero', 'Ksero', 'uslugi', 'Kserokopie w czerni-bieli i kolorze.', 'ksero.png', 'unit', 0.35],
            ['druk', 'Druk', 'druk', 'Druk cyfrowy i offsetowy na różnych nośnikach.', 'druk.png', 'options', 1],
            ['skanowanie', 'Skanowanie', 'uslugi', 'Skanowanie dokumentów i zdjęć w wysokiej rozdzielczości.', 'skanowanie.png', 'unit', 1],
            ['zdjecia-dokumenty', 'Zdjęcia do dokumentów', 'uslugi', 'Fotoset do paszportu, dowodu i wizy.', 'zdjecia-do-dokumentow.png', 'fixed', 20],
            ['pieczatki', 'Pieczątki', 'uslugi', 'Pieczątki, stemple i datowniki.', 'pieczatki.png', 'fixed', 45],
            ['projektowanie', 'Projektowanie graficzne', 'reklama', 'Projekty graficzne materiałów reklamowych.', 'projektowanie-graficzne.png', 'fixed', 80],
            ['uslugi-dodatkowe', 'Usługi dodatkowe', 'uslugi', 'Laminowanie, oprawianie i personalizacja.', 'druk.png', 'fixed', 2],
            ['oprawa-prac', 'Oprawa prac i bindowanie', 'druk', 'Oprawa twarda i miękka prac dyplomowych w 24h.', 'oprawa-prac-i-bindowanie.png', 'fixed', 8],
            ['praca-dyplomowa', 'Praca dyplomowa', 'druk', 'Druk i oprawa pracy dyplomowej z dostawą lub odbiorem osobistym.', 'graduation.png', 'thesis', 0],
        ];

        foreach ($products as $sortOrder => [$slug, $name, $category, $description, $image, $calculatorType, $startingPrice]) {
            Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category' => $category,
                    'description' => $description,
                    'image_path' => 'images/produkty/'.$image,
                    'calculator_type' => $calculatorType,
                    'starting_price' => $startingPrice,
                    'configuration' => array_filter([
                        'starting_price' => $startingPrice,
                        'configurator' => $businessConfigurators[$slug] ?? null,
                    ], static fn (mixed $value): bool => $value !== null),
                    'sort_order' => $sortOrder + 1,
                    'is_active' => true,
                    'is_business_configurator' => isset($businessConfigurators[$slug]),
                ],
            );
        }
    }
}
