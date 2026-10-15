<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Headline keys of the CMS sections that no longer end with a full stop. */
    private const TITLE_KEYS = ['heading', 'title_before', 'title_emphasis'];

    /**
     * The saved CMS content wins over the defaults, so the new title style, the "Oferta dla firm" button, the "Druk" hero tile that still talked about e-mailing the file
     * and the experience counter ("22 lata" was stale, the printshop opened in 2002) are applied
     * to it as well.
     */
    public function up(): void
    {
        DB::table('cms_sections')->get(['id', 'payload'])->each(function (object $section): void {
            $payload = json_decode((string) $section->payload, true);

            if (! is_array($payload)) {
                return;
            }

            foreach (self::TITLE_KEYS as $key) {
                if (isset($payload[$key]) && is_string($payload[$key])) {
                    $payload[$key] = rtrim($payload[$key], '.');
                }
            }

            foreach (['secondary_label', 'cta_secondary_label'] as $key) {
                if (in_array($payload[$key] ?? null, ['Wyceń druk dla firmy', 'Dla firm'], true)) {
                    $payload[$key] = 'Oferta dla firm';
                }
            }

            foreach ($payload['items'] ?? [] as $index => $item) {
                if (($item['body'] ?? null) === 'Realizujemy druk cyfrowy, offsetowy i wielkoformatowy: od dokumentów po materiały reklamowe.') {
                    $payload['items'][$index]['body'] = 'Realizujemy druk cyfrowy, offsetowy i wielkoformatowy: od dokumentów po materiały reklamowe na terenie całej Polski.';
                }

                if (($item['body'] ?? null) === 'Prześlij plik mailem. Podaj liczbę egzemplarzy oraz druk kolorowy lub czarno-biały.') {
                    $payload['items'][$index]['body'] = 'Dodaj plik w konfiguratorze, wybierz liczbę egzemplarzy oraz druk kolorowy lub czarno-biały.';
                }

                if (isset($item['title']) && preg_match('/^\d+ lat(a)? doświadczenia$/u', $item['title']) === 1) {
                    $payload['items'][$index]['title'] = '{years_of_experience} doświadczenia';
                }
            }

            DB::table('cms_sections')->where('id', $section->id)->update([
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        //
    }
};
