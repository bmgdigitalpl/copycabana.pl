<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $images = [
            'wizytowki' => "images/produkty/wizytowki'.png",
            'ulotki' => 'images/produkty/ulotki.png',
            'plakaty' => 'images/produkty/plakaty.png',
            'rollupy' => 'images/produkty/rollupy.png',
            'banery' => 'images/produkty/banery.png',
            'billboardy' => 'images/produkty/billboardy.png',
            'fotoobrazy' => 'images/produkty/fotoobrazy.png',
            'fototapety' => 'images/produkty/fototapety.png',
            'kalendarze' => 'images/produkty/kalendarze-spiralowane.png',
            'naklejki' => 'images/produkty/naklejki.png',
            'tabliczki' => 'images/produkty/tabliczki-grawerowane.png',
            'rysunki-cad' => 'images/produkty/rysunki-plany-mapycad.png',
            'ksero' => 'images/produkty/ksero.png',
            'druk' => 'images/produkty/druk.png',
            'skanowanie' => 'images/produkty/skanowanie.png',
            'zdjecia-dokumenty' => 'images/produkty/zdjecia-do-dokumentow.png',
            'pieczatki' => 'images/produkty/pieczatki.png',
            'projektowanie' => 'images/produkty/projektowanie-graficzne.png',
            'uslugi-dodatkowe' => 'images/produkty/druk.png',
            'oprawa-prac' => 'images/produkty/oprawa-prac-i-bindowanie.png',
        ];

        foreach ($images as $slug => $imagePath) {
            DB::table('products')->where('slug', $slug)->update(['image_path' => $imagePath]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Image paths are application data and should not be reverted to deleted files.
    }
};
