<?php

namespace App\Services;

use App\Models\CmsSection;
use Illuminate\Support\Facades\Route;

class CmsContent
{
    /** @return array<string, string> */
    public function sections(): array
    {
        return [
            'hero' => 'Hero',
            'hero-cards' => 'Kafelki hero',
            'why' => 'Dlaczego CopyCabana',
            'process' => 'Kroki',
            'services' => 'Usługi',
            'faq' => 'FAQ',
            'contact' => 'Kontakt',
        ];
    }

    public function label(string $section): string
    {
        return $this->sections()[$section] ?? $section;
    }

    /** @return array<string, mixed> */
    public function section(string $section): array
    {
        $payload = CmsSection::query()->where('key', $section)->value('payload');

        return array_replace_recursive(
            $this->defaults()[$section] ?? [],
            is_array($payload) ? $payload : [],
        );
    }

    /** @return array<string, array<string, mixed>> */
    public function home(): array
    {
        return collect(array_keys($this->sections()))
            ->mapWithKeys(fn (string $section): array => [$section => $this->section($section)])
            ->all();
    }

    public function url(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        if (str_starts_with($value, '#') || str_starts_with($value, '/') || preg_match('/^(https?:|mailto:|tel:)/', $value) === 1) {
            return $value;
        }

        if (str_starts_with($value, 'services.business?')) {
            $query = parse_url($value, PHP_URL_QUERY) ?: '';
            parse_str($query, $parameters);

            return route('services.business', $parameters).'#produkty';
        }

        return Route::has($value) ? route($value) : $value;
    }

    /** @return array<string, array<string, mixed>> */
    public function defaults(): array
    {
        return [
            'hero' => [
                'title_before' => 'Od pliku do',
                'title_emphasis' => 'gotowego wydruku.',
                'primary_label' => 'Skonfiguruj swoją pracę',
                'primary_link' => 'services.diploma',
                'primary_icon' => 'fa-graduation-cap',
                'secondary_label' => 'Druk dla firm',
                'secondary_link' => 'services.business',
                'secondary_icon' => 'fa-building',
                'semantic_intro' => 'W CopyCabana szybko zamówisz druk i oprawę pracy dyplomowej albo materiały firmowe: wizytówki, ulotki, plakaty, banery i rollupy z odbiorem w Katowicach lub wysyłką.',
            ],
            'hero-cards' => [
                'items' => [
                    ['title' => '24h', 'body' => 'Druk i oprawa prac dyplomowych. Dostępność terminu potwierdzamy dla Twojego pliku i wybranej oprawy.', 'variant' => 'cyan', 'link' => 'services.diploma'],
                    ['title' => 'Druk', 'body' => 'Prześlij plik mailem. Podaj liczbę egzemplarzy oraz druk kolorowy lub czarno-biały.', 'variant' => 'magenta', 'link' => 'services.business?product=druk#produkty'],
                    ['title' => 'Katowice', 'body' => 'Odbiór w centrum miasta przy ul. Bankowej 11.', 'variant' => 'yellow', 'link' => 'contact'],
                    ['title' => 'Dla firm', 'body' => 'Wizytówki, ulotki, plakaty, rollupy, banery i więcej.', 'variant' => 'black', 'link' => 'services.business'],
                ],
            ],
            'why' => [
                'heading' => 'Dlaczego CopyCabana.',
                'lead_1' => 'To, co zaczęło się od pojedynczych punktów ksero, rozrosło się w jedną z najlepszych drukarni w Katowicach.',
                'lead_2' => 'Dziś obsługujemy studentów, klientów indywidualnych, firmy i agencje z całej Polski, łącząc lokalne podejście z produkcją gotową na większą skalę. Dbamy o jasną komunikację, realne terminy i efekt, który możesz odebrać albo wysłać dalej bez poprawek.',
                'cta_primary_label' => 'Skonfiguruj pracę dyplomową',
                'cta_primary_link' => 'services.diploma',
                'cta_secondary_label' => 'Wyceń druk dla firmy',
                'cta_secondary_link' => 'services.business',
                'items' => [
                    ['title' => '22 lata doświadczenia', 'body' => 'Od 2002 roku pomagamy drukować, oprawiać i przygotowywać materiały, które muszą wyglądać profesjonalnie.', 'icon' => 'fa-calendar-check', 'variant' => ''],
                    ['title' => 'Tysiące zadowolonych klientów', 'body' => 'Realizujemy druk cyfrowy, offsetowy i wielkoformatowy: od dokumentów po materiały reklamowe.', 'icon' => 'fa-users', 'variant' => 'yellow'],
                    ['title' => 'Drukarnia na miejscu', 'body' => 'Znajdziesz nas przy ul. Bankowej 11 w Katowicach. Odbierz zamówienie osobiście albo wybierz wysyłkę.', 'icon' => 'fa-location-dot', 'variant' => 'blue'],
                ],
            ],
            'process' => [
                'heading' => 'Jak zamienić plik w gotowy wydruk.',
                'items' => [
                    ['number' => '01', 'title' => 'Wybierasz usługę', 'body' => 'Zaczynasz od pracy dyplomowej, dokumentu PDF albo materiałów firmowych i ustawiasz najważniejsze parametry.'],
                    ['number' => '02', 'title' => 'Dodajesz plik i termin', 'body' => 'Przesyłasz projekt, wybierasz odbiór lub dostawę i widzisz, co jest potrzebne do realizacji.'],
                    ['number' => '03', 'title' => 'Odbierasz gotowy druk', 'body' => 'Drukujemy, oprawiamy i przygotowujemy zamówienie do odbioru w Katowicach albo do wysyłki.'],
                ],
            ],
            'services' => [
                'heading' => 'USŁUGI',
                'items' => $this->defaultServices(),
            ],
            'faq' => [
                'label' => 'FAQ',
                'heading' => 'Zanim wyślesz plik.',
                'lead' => 'Najczęstsze pytania przed drukiem. Odpowiadamy prosto, tak jak przy ladzie w drukarni.',
                'items' => [
                    ['q' => 'W jakim formacie wysłać plik?', 'a' => 'Najbezpieczniej kompletny PDF. Przed zamówieniem sprawdź stronnicowanie, kolejność i numerację stron.'],
                    ['q' => 'Czy mogę wydrukować dokument dwustronnie?', 'a' => 'Tak. W konfiguratorze wybierzesz druk jednostronny lub dwustronny z krótkim opisem, co to oznacza w praktyce.'],
                    ['q' => 'Jak szybko będzie gotowe zamówienie?', 'a' => 'Standardowo rozmawiamy o realizacji w 24h. Konkretny termin pokażemy po wyborze pliku, oprawy i metody dostawy.'],
                    ['q' => 'Czy mogę odebrać je w Katowicach?', 'a' => 'Tak. Odbiór osobisty działa w punkcie przy ul. Bankowej 11.'],
                    ['q' => 'Czy wysyłacie zamówienia?', 'a' => 'Tak, możesz wybrać paczkomat albo kuriera. Koszty i termin potwierdzamy przed produkcją.'],
                    ['q' => 'Czy zobaczę cenę przed złożeniem zamówienia?', 'a' => 'Tak, cena i termin są widoczne w podsumowaniu zanim zatwierdzisz zamówienie.'],
                    ['q' => 'Co jeśli mój PDF ma błąd?', 'a' => 'Pokażemy przyczynę i następny możliwy krok. Kontrola techniczna nie obejmuje treści pracy ani wymagań wydziału.'],
                ],
            ],
            'contact' => [
                'overline' => 'Kontakt',
                'title_before' => 'Jesteśmy w Katowicach.',
                'title_emphasis' => 'Napisz albo zadzwoń.',
                'body' => 'Masz plik, pytanie o termin albo niestandardowe zlecenie? Odezwij się do drukarni przy ul. Bankowej 11.',
                'phone_label' => '502 293 849',
                'phone_link' => 'tel:502293849',
                'email_label' => 'biuro@copycabana.pl',
                'email_link' => 'mailto:biuro@copycabana.pl',
            ],
        ];
    }

    /** @return array<int, array<string, string|array<int, string>>> */
    private function defaultServices(): array
    {
        $business = static fn (string $product): string => 'services.business?product='.$product.'#produkty';

        return [
            ['title' => 'Praca dyplomowa', 'accusative' => 'pracę dyplomową', 'alt' => 'Oprawiona praca dyplomowa', 'image' => 'images/produkty/oprawa-prac-i-bindowanie.png', 'href' => 'services.diploma', 'icon' => 'fa-graduation-cap', 'questions' => ['Jakie rodzaje oprawy prac dyplomowych oferujecie?', 'Ile trwa oprawa pracy dyplomowej?']],
            ['title' => 'Druk', 'accusative' => 'druk', 'alt' => 'Druk dokumentów', 'image' => 'images/produkty/druk.png', 'href' => $business('druk'), 'icon' => 'fa-file-lines', 'questions' => ['Jakie formaty i gramatury papieru są dostępne?', 'Ile kosztuje druk kolorowy w większym nakładzie?']],
            ['title' => 'Wizytówki', 'accusative' => 'wizytówki', 'alt' => 'Wizytówki', 'image' => 'images/produkty/wizytowki.png', 'href' => $business('wizytowki'), 'icon' => 'fa-id-card', 'questions' => ['Jaki jest minimalny nakład wizytówek?', 'Czy mogę zamówić wizytówki z własnym projektem?']],
            ['title' => 'Ulotki', 'accusative' => 'ulotki', 'alt' => 'Ulotki', 'image' => 'images/produkty/ulotki.png', 'href' => $business('ulotki'), 'icon' => 'fa-folder-open', 'questions' => ['Jakie formaty ulotek są dostępne?', 'Ile trwa realizacja zamówienia na ulotki?']],
            ['title' => 'Plakaty', 'accusative' => 'plakaty', 'alt' => 'Plakaty', 'image' => 'images/produkty/plakaty.png', 'href' => $business('plakaty'), 'icon' => 'fa-image', 'questions' => ['Jaki jest maksymalny format plakatu?', 'Na jakim papierze drukujecie plakaty?']],
            ['title' => 'Rollupy', 'accusative' => 'rollupy', 'alt' => 'Rollupy', 'image' => 'images/produkty/rollupy.png', 'href' => $business('rollupy'), 'icon' => 'fa-user-tie', 'questions' => ['Jakie wymiary rollupów oferujecie?', 'Czy w cenie rollupu jest torba transportowa?']],
            ['title' => 'Banery', 'accusative' => 'banery', 'alt' => 'Banery', 'image' => 'images/produkty/banery.png', 'href' => $business('banery'), 'icon' => 'fa-flag', 'questions' => ['Czy banery mają oczka do montażu?', 'Jaki materiał jest używany do banerów zewnętrznych?']],
            ['title' => 'Billboardy', 'accusative' => 'billboardy', 'alt' => 'Billboardy', 'image' => 'images/produkty/billboardy.png', 'href' => $business('billboardy'), 'icon' => 'fa-rectangle-ad', 'questions' => ['Jakie formaty billboardów są dostępne?', 'Czy oferujecie montaż billboardu?']],
            ['title' => 'Fotoobrazy', 'accusative' => 'fotoobrazy', 'alt' => 'Fotoobrazy', 'image' => 'images/produkty/fotoobrazy.png', 'href' => $business('fotoobrazy'), 'icon' => 'fa-image', 'questions' => ['Na jakim podłożu drukujecie fotoobrazy?', 'Czy mogę zamówić fotoobraz w niestandardowym rozmiarze?']],
            ['title' => 'Fototapety', 'accusative' => 'fototapety', 'alt' => 'Fototapety', 'image' => 'images/produkty/fototapety.png', 'href' => $business('fototapety'), 'icon' => 'fa-expand', 'questions' => ['Jak dobrać wymiary fototapety do ściany?', 'Jaki materiał fototapety polecacie do łazienki?']],
            ['title' => 'Kalendarze spiralowane', 'accusative' => 'kalendarze spiralowane', 'alt' => 'Kalendarze spiralowane', 'image' => 'images/produkty/kalendarze-spiralowane.png', 'href' => $business('kalendarze'), 'icon' => 'fa-calendar-days', 'questions' => ['Czy mogę dodać własne zdjęcia do kalendarza?', 'Jaki jest minimalny nakład kalendarzy spiralowanych?']],
            ['title' => 'Naklejki', 'accusative' => 'naklejki', 'alt' => 'Naklejki', 'image' => 'images/produkty/naklejki.png', 'href' => $business('naklejki'), 'icon' => 'fa-note-sticky', 'questions' => ['Jakie kształty naklejek jest możliwe wyciąć?', 'Czy naklejki są odporne na wodę?']],
            ['title' => 'Tabliczki grawerowane', 'accusative' => 'tabliczki grawerowane', 'alt' => 'Tabliczki grawerowane', 'image' => 'images/produkty/tabliczki-grawerowane.png', 'href' => $business('tabliczki'), 'icon' => 'fa-sign', 'questions' => ['Z jakich materiałów wykonujecie tabliczki grawerowane?', 'Ile trwa wygrawerowanie tabliczki?']],
            ['title' => 'Rysunki, plany, mapy', 'accusative' => 'rysunki, plany, mapy', 'alt' => 'Rysunki, plany, mapy', 'image' => 'images/produkty/rysunki-plany-mapycad.png', 'href' => $business('rysunki-cad'), 'icon' => 'fa-compass-drafting', 'questions' => ['Jakie formaty plików CAD przyjmujecie?', 'Do jakiego formatu (np. A0) mogę wydrukować plan?']],
            ['title' => 'Ksero', 'accusative' => 'ksero', 'alt' => 'Ksero', 'image' => 'images/produkty/ksero.png', 'href' => $business('ksero'), 'icon' => 'fa-copy', 'questions' => ['Ile kosztuje kserokopia czarno-biała A4?', 'Czy robicie ksero w kolorze i dwustronnie?']],
            ['title' => 'Skanowanie', 'accusative' => 'skanowanie', 'alt' => 'Skanowanie', 'image' => 'images/produkty/skanowanie.png', 'href' => $business('skanowanie'), 'icon' => 'fa-file-arrow-up', 'questions' => ['W jakiej rozdzielczości skanujecie dokumenty?', 'Czy mogę zeskanować dokumenty do jednego pliku PDF?']],
            ['title' => 'Zdjęcia do dokumentów', 'accusative' => 'zdjęcia do dokumentów', 'alt' => 'Zdjęcia do dokumentów', 'image' => 'images/produkty/zdjecia-do-dokumentow.png', 'href' => $business('zdjecia-dokumenty'), 'icon' => 'fa-id-card', 'questions' => ['Ile czeka się na zdjęcie do dokumentów?', 'Czy zdjęcie spełnia wymogi na paszport i dowód?']],
            ['title' => 'Pieczątki', 'accusative' => 'pieczątki', 'alt' => 'Pieczątki', 'image' => 'images/produkty/pieczatki.png', 'href' => $business('pieczatki'), 'icon' => 'fa-stamp', 'questions' => ['Ile trwa wykonanie pieczątki?', 'Czy mogę zaprojektować własny wygląd pieczątki?']],
            ['title' => 'Projektowanie graficzne', 'accusative' => 'projektowanie graficzne', 'alt' => 'Projektowanie graficzne', 'image' => 'images/produkty/projektowanie-graficzne.png', 'href' => $business('projektowanie'), 'icon' => 'fa-pen-ruler', 'questions' => ['Co obejmuje usługa projektowania graficznego?', 'Ile poprawek do projektu jest wliczone w cenę?']],
        ];
    }
}
