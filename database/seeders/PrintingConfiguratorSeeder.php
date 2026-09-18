<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class PrintingConfiguratorSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'druk' => [
                'page_prices' => ['bw' => 0.20, 'color' => 0.50],
                'finishes' => [
                    'none' => ['label' => 'Bez wykończenia', 'price' => 0],
                    'staples' => ['label' => 'Spinanie zeszytowe', 'price' => 4],
                    'folder' => ['label' => 'Teczka', 'price' => 8],
                    'channel' => ['label' => 'Oprawa kanałowa', 'price' => 18],
                ],
                'max_copies' => 50,
            ],
            'praca-dyplomowa' => [
                'page_prices' => ['bw' => 0.20, 'color' => 0.50],
                'bindings' => [
                    'soft' => ['label' => 'Oprawa miękka', 'price' => 0, 'hint' => 'Klasyczna broszura. Częsty standard wydziałów.'],
                    'channel' => ['label' => 'Oprawa kanałowa', 'price' => 25, 'hint' => 'Klejony blok, równy grzbiet.'],
                    'hard' => ['label' => 'Oprawa twarda', 'price' => 50, 'hint' => 'Sztywna oprawa. Premium w obronie.'],
                ],
                'covers' => [
                    'none' => ['label' => 'Bez napisu', 'price' => 0],
                    'standard' => ['label' => 'Standardowy napis', 'price' => 15],
                    'custom' => ['label' => 'Własny napis', 'price' => 20],
                ],
                'universities' => [
                    'us' => 'Uniwersytet Śląski w Katowicach',
                    'ue' => 'Uniwersytet Ekonomiczny w Katowicach',
                    'polsl' => 'Politechnika Śląska',
                    'awf' => 'Akademia Wychowania Fizycznego w Katowicach',
                    'asp' => 'Akademia Sztuk Pięknych w Katowicach',
                    'am' => 'Akademia Muzyczna im. Karola Szymanowskiego w Katowicach',
                    'sum' => 'Śląski Uniwersytet Medyczny w Katowicach',
                    'wszop' => 'Wyższa Szkoła Zarządzania Ochroną Pracy w Katowicach',
                    'wsb' => 'Uniwersytet WSB Merito Chorzów',
                ],
                'cover_titles' => [
                    'licencjacka' => 'Praca Licencjacka', 'magisterska' => 'Praca Magisterska',
                    'inzynierska' => 'Praca Inżynierska', 'dyplomowa' => 'Praca Dyplomowa',
                    'doktorska' => 'Praca Doktorska', 'projekt_inzynierski' => 'Projekt Inżynierski',
                    'rozprawa_doktorska' => 'Rozprawa Doktorska', 'master_thesis' => 'Master Thesis',
                    'bachelor_thesis' => 'Bachelor Thesis',
                ],
                'imprint_colors' => [
                    'gold' => ['label' => 'Złoty', 'hex' => '#D4AF37'],
                    'silver' => ['label' => 'Srebrny', 'hex' => '#C0C0C0'],
                    'white' => ['label' => 'Biały', 'hex' => '#FFFFFF'],
                    'black' => ['label' => 'Czarny', 'hex' => '#111827'],
                    'ruby' => ['label' => 'Rubinowy', 'hex' => '#9B111E'],
                ],
                'cover_colors' => [
                    'granat' => ['label' => 'Granat', 'hex' => '#063A60'],
                    'magenta' => ['label' => 'Magenta', 'hex' => '#D51A70'],
                    'zolty' => ['label' => 'Żółty', 'hex' => '#FFED00'],
                    'zielony' => ['label' => 'Zielony', 'hex' => '#7FBF45'],
                    'niebieski' => ['label' => 'Niebieski', 'hex' => '#00456F'],
                ],
                'cd' => ['label' => 'Nagranie pracy na CD', 'price' => 20],
                'spine_engraving' => ['label' => 'Grawerowanie imienia i nazwiska na grzbiecie', 'price' => 30],
                'max_copies' => 10,
            ],
        ];

        foreach ($settings as $slug => $printing) {
            $product = Product::query()->where('slug', $slug)->firstOrFail();
            $configuration = $product->configuration ?? [];
            if (! array_key_exists('printing', $configuration)) {
                $product->update(['configuration' => [...$configuration, 'printing' => $printing]]);
            }
        }
    }
}
