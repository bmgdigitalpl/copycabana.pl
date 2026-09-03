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
        $products = [
            ['wizytowki', 'Wizytówki', 'druk', 'Profesjonalne wizytówki w różnych formatach i wykończeniach.', 'product-02.png', 'options', 25],
            ['ulotki', 'Ulotki', 'druk', 'Ulotki reklamowe w różnych formatach i gramaturach papieru.', 'product-03.png', 'options', 30],
            ['plakaty', 'Plakaty', 'reklama', 'Plakaty w formatach od A3 do B1 na papierze błysk i mat.', 'product-04.png', 'options', 8],
            ['rollupy', 'Rollupy', 'reklama', 'Systemy wystawiennicze z grafiką na wymiar.', 'product-05.png', 'options', null],
            ['banery', 'Banery', 'reklama', 'Banery wielkoformatowe z oczkami i wykończeniem.', 'product-06.png', 'area', 30],
            ['billboardy', 'Billboardy', 'reklama', 'Reklama zewnętrzna w dużym formacie na Śląsku.', 'product-07.png', 'area', 50],
            ['fotoobrazy', 'Fotoobrazy', 'foto', 'Fotoobrazy na płótnie i w ramach.', 'product-08.png', 'options', 89],
            ['fototapety', 'Fototapety', 'foto', 'Fototapety na wymiar do wnętrz i biur.', 'product-09.png', 'area', 40],
            ['kalendarze', 'Kalendarze spiralowane', 'druk', 'Kalendarze ścienne i biurkowe z indywidualnym projektem.', 'product-10.png', 'options', 15],
            ['naklejki', 'Naklejki', 'druk', 'Naklejki w dowolnych kształtach i rozmiarach.', 'product-11.png', 'options', 25],
            ['tabliczki', 'Tabliczki grawerowane', 'uslugi', 'Tabliczki informacyjne i grawerowane laserowo.', 'product-12.png', 'options', 30],
            ['rysunki-cad', 'Rysunki, plany, mapy', 'uslugi', 'Wydruki CAD w formatach A0–A4.', 'product-13.png', 'unit', 0.50],
            ['ksero', 'Ksero', 'uslugi', 'Kserokopie w czerni-bieli i kolorze.', 'product-14.png', 'unit', 0.35],
            ['druk', 'Druk', 'druk', 'Druk cyfrowy i offsetowy na różnych nośnikach.', 'product-15.png', 'options', 1],
            ['skanowanie', 'Skanowanie', 'uslugi', 'Skanowanie dokumentów i zdjęć w wysokiej rozdzielczości.', 'product-16.png', 'unit', 1],
            ['zdjecia-dokumenty', 'Zdjęcia do dokumentów', 'uslugi', 'Fotoset do paszportu, dowodu i wizy.', 'product-17.png', 'fixed', 20],
            ['pieczatki', 'Pieczątki', 'uslugi', 'Pieczątki, stemple i datowniki.', 'product-18.png', 'fixed', 45],
            ['projektowanie', 'Projektowanie graficzne', 'reklama', 'Projekty graficzne materiałów reklamowych.', 'product-19.png', 'fixed', 80],
            ['uslugi-dodatkowe', 'Usługi dodatkowe', 'uslugi', 'Laminowanie, oprawianie i personalizacja.', 'product-01.png', 'fixed', 2],
            ['oprawa-prac', 'Oprawa prac i bindowanie', 'druk', 'Oprawa twarda i miękka prac dyplomowych w 24h.', 'product-02.png', 'fixed', 8],
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
                    'configuration' => ['starting_price' => $startingPrice],
                    'sort_order' => $sortOrder + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
