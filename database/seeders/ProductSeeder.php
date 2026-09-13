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
            ['wizytowki', 'Wizytówki', 'druk', 'Profesjonalne wizytówki w różnych formatach i wykończeniach.', "wizytowki'.png", 'options', 25],
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
                    'configuration' => ['starting_price' => $startingPrice],
                    'sort_order' => $sortOrder + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
