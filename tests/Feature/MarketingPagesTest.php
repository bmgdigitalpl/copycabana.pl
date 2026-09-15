<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_configurator_and_cart_load_the_shared_interactions(): void
    {
        foreach (['services.business', 'cart'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('<script src="'.asset('js/app.js').'"></script>', false);
        }
    }

    public function test_public_entry_points_link_directly_to_the_business_configurator(): void
    {
        foreach (['home', 'cart', 'about'] as $routeName) {
            $this->get(route($routeName))
                ->assertSee('href="'.route('services.business').'"', false)
                ->assertDontSee('href="'.url('/produkty').'"', false)
                ->assertDontSee('href="produkty.html"', false);
        }
    }

    public function test_brand_playground_renders_the_visual_language_showcase(): void
    {
        $this->get(route('brand'))
            ->assertOk()
            ->assertSee('CopyCabana Digital Playground')
            ->assertSee('Typography')
            ->assertSee('Color System')
            ->assertSee('Buttons & interactions', false)
            ->assertSee('Upload states')
            ->assertSee('Order stepper')
            ->assertSee('Trust architecture')
            ->assertSee('Masz PDF? Resztą zajmiemy się my.')
            ->assertSee('class="brand-word-static"', false)
            ->assertDontSee('data-brand-word', false)
            ->assertDontSee('data-words', false)
            ->assertSee('data-brand-slider', false)
            ->assertSee('PDF do druku')
            ->assertSee('brand-card-showcase', false)
            ->assertSee('data-brand-playground', false)
            ->assertSee('data-brand-stepper', false)
            ->assertSee('data-brand-upload', false);
    }

    public function test_design_system_path_redirects_to_the_brand_playground(): void
    {
        $this->get(route('brand.design-system'))
            ->assertRedirect(route('brand'));
    }

    public function test_font_pairing_comparison_page_renders_all_specimens(): void
    {
        $this->get(route('test.fonts'))
            ->assertOk()
            ->assertSee('Jeden hero.')
            ->assertSee('Archivo + Inter')
            ->assertSee('Roboto Slab + Roboto')
            ->assertSee('Playfair Display + Montserrat')
            ->assertSee('Cormorant + Cormorant Garamond')
            ->assertSee('Lora + Libre Baskerville')
            ->assertSee('Source Sans 3 + Merriweather')
            ->assertSee('font-test.css', false);
    }

    public function test_homepage_uses_the_main_layout_and_exposes_the_three_order_paths(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Od pliku do')
            ->assertSee('gotowego wydruku.')
            ->assertDontSee('CopyCabana · Drukarnia w Katowicach')
            ->assertDontSee('Prace dyplomowe, dokumenty PDF oraz materiały dla firm i agencji.')
            ->assertSee('Dlaczego CopyCabana.')
            ->assertDontSee('Dlaczego my?')
            ->assertDontSee('Możesz nam zaufać.')
            ->assertDontSee('Doświadczenie, zaufanie i lokalna drukarnia, do której możesz przyjść osobiście.')
            ->assertSee('22 lata doświadczenia')
            ->assertSee('Tysiące zadowolonych klientów')
            ->assertSee('Drukarnia na miejscu')
            ->assertSee('cc-need-grid', false)
            ->assertSee('Skonfiguruj druk')
            ->assertSee('Druk dla firm')
            ->assertSee('Od pliku do gotowego wydruku.')
            ->assertDontSee('Jak to działa')
            ->assertDontSee('Od pliku do gotowego wydruku. Wybierasz, my dbamy o resztę.')
            ->assertSee('cc-section-head--left', false)
            ->assertDontSee('cc-section-head--center', false)
            ->assertSee('Konfigurujesz pracę')
            ->assertDontSee('<h3>Dodajesz plik</h3>', false)
            ->assertDontSee('<h3>Wybierasz ustawienia</h3>', false)
            ->assertDontSee('<h3>Sprawdzasz cenę</h3>', false)
            ->assertSee('USŁUGI')
            ->assertDontSee('<p class="cc-section-label">USŁUGI</p>', false)
            ->assertDontSee('Wybrane realizacje z drukarni w Katowicach: od prac dyplomowych po materiały reklamowe.')
            ->assertSee('cc-gallery', false)
            ->assertSee('cc-marquee-group', false)
            ->assertDontSee('Chcę wydrukować dokumenty PDF')
            ->assertDontSee('Cena i kontrola')
            ->assertDontSee('Jakość')
            ->assertSee(route('services.diploma'), false)
            ->assertSee(route('services.business'), false)
            ->assertSee(route('druk-pdf'), false)
            ->assertSee(route('services.business', ['product' => 'banery']).'#produkty', false)
            ->assertSee(route('services.business', ['product' => 'rollupy']).'#produkty', false);

        $this->assertSame(3, substr_count($response->getContent(), 'class="cc-need-icon"'));
        $this->assertSame(3, substr_count($response->getContent(), 'class="cc-process-step"'));
    }

    public function test_homepage_lists_all_configurator_services_with_order_actions(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->get(route('home'));

        $response
            ->assertSee('href="'.route('services.diploma').'"', false)
            ->assertSee('href="'.route('druk-pdf').'"', false);

        foreach ([
            'wizytowki' => 'Wizytówki',
            'ulotki' => 'Ulotki',
            'plakaty' => 'Plakaty',
            'rollupy' => 'Rollupy',
            'banery' => 'Banery',
            'billboardy' => 'Billboardy',
            'fotoobrazy' => 'Fotoobrazy',
            'fototapety' => 'Fototapety',
            'kalendarze' => 'Kalendarze spiralowane',
            'naklejki' => 'Naklejki',
            'tabliczki' => 'Tabliczki grawerowane',
            'rysunki-cad' => 'Rysunki, plany, mapy',
            'ksero' => 'Ksero',
            'druk' => 'Druk',
            'skanowanie' => 'Skanowanie',
            'zdjecia-dokumenty' => 'Zdjęcia do dokumentów',
            'pieczatki' => 'Pieczątki',
            'projektowanie' => 'Projektowanie graficzne',
        ] as $product => $name) {
            $response
                ->assertSee($name)
                ->assertSee('href="'.route('services.business', ['product' => $product]).'#produkty"', false);
        }

        $content = $response->getContent();

        $this->assertSame(20, substr_count($content, 'class="cc-gallery-card reveal"'));
        $this->assertSame(20, substr_count($content, '>Zamów '));
        $this->assertStringContainsString(asset('images/produkty/oprawa-prac-i-bindowanie.png'), $content);
        $this->assertStringContainsString(asset('images/produkty/druk.png'), $content);
        $this->assertStringNotContainsString('Usługi dodatkowe', $content);
        $this->assertStringNotContainsString('Oprawa prac i bindowanie', $content);
    }

    public function test_homepage_hero_renders_static_information_cards(): void
    {
        $content = $this->get(route('home'))->getContent();

        $this->assertSame(1, substr_count($content, 'class="hero-stack"'));
        $this->assertStringContainsString('stack-card stack-card--cyan', $content);
        $this->assertStringContainsString('stack-card stack-card--magenta', $content);
        $this->assertStringContainsString('stack-card stack-card--yellow', $content);
        $this->assertStringContainsString('stack-card stack-card--black', $content);
        $this->assertStringNotContainsString('images/hero-new.webp', $content);
        $this->assertStringNotContainsString('data-cc-hero-slider', $content);
        $this->assertStringNotContainsString('cc-hero-slider-track', $content);
        $this->assertStringNotContainsString('cc-hero-slide', $content);

        $heroCss = file_get_contents(public_path('css/dynamic-local-service.css'));
        $conceptCss = file_get_contents(public_path('css/concept.css'));

        $this->assertStringContainsString('.hero-stack', $heroCss);
        $this->assertStringContainsString('.stack-card--cyan', $heroCss);
        $this->assertStringContainsString('.stack-card--magenta', $heroCss);
        $this->assertStringContainsString('.stack-card--yellow', $heroCss);
        $this->assertStringContainsString('.stack-card--black', $heroCss);
        $this->assertStringNotContainsString('.cc-hero-slider', $conceptCss);
    }

    public function test_homepage_renders_the_contact_hero(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertSee('Kontakt')
            ->assertSee('Jesteśmy w Katowicach.')
            ->assertSee('Napisz albo zadzwoń.')
            ->assertSee('Masz plik, pytanie o termin albo niestandardowe zlecenie?')
            ->assertSee('href="tel:502293849"', false)
            ->assertSee('502 293 849')
            ->assertSee('href="mailto:biuro@copycabana.pl"', false)
            ->assertSee('biuro@copycabana.pl')
            ->assertSee('https://www.google.com/maps/embed?pb=', false)
            ->assertSee('Mapa dojazdu do CopyCabana przy ul. Bankowej 11 w Katowicach');
    }

    public function test_header_uses_cart_and_account_icons_without_a_contact_link(): void
    {
        $this->get(route('home'))
            ->assertSee('href="'.route('cart').'" class="cc-header-icon"', false)
            ->assertSee('fa-cart-shopping', false)
            ->assertSee('class="cart-badge cc-cart-badge"', false)
            ->assertSee('href="'.route('customer.login').'" class="cc-header-icon"', false)
            ->assertSee('fa-user', false)
            ->assertDontSee('href="'.route('contact').'"', false);
    }

    public function test_marketing_heroes_use_the_single_card_layout(): void
    {
        $this->seed(ProductSeeder::class);
        foreach (['services.diploma', 'druk-pdf', 'services.business'] as $routeName) {
            $content = $this->get(route($routeName))->getContent();

            $this->assertSame(1, substr_count($content, 'class="cc-hero-card cc-hero-card--single"'));
            $this->assertStringNotContainsString('cc-hero-card--a', $content);
            $this->assertStringNotContainsString('cc-hero-card--b', $content);
            $this->assertStringNotContainsString('cc-hero-card--tag', $content);
        }

        $this->get(route('druk-pdf'))
            ->assertSee('Zazwyczaj realizujemy dokumenty w 24h.')
            ->assertSee('Wydruk bez zbędnych kroków.')
            ->assertSee('cc-hero-hint', false);
    }

    public function test_public_typography_uses_the_selected_roboto_pairing(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Roboto+Slab', false)
            ->assertSee('Roboto:wght@400;500;700', false);
    }

    public function test_homepage_does_not_render_playground_markers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Koncepcja CopyCabana', false)
            ->assertDontSee('noindex', false)
            ->assertDontSee('strona-koncepcja', false);
    }

    public function test_marketing_pages_render_successfully(): void
    {
        $this->seed(ProductSeeder::class);
        $pages = [
            'services.diploma' => 'Praca napisana.',
            'services.business' => 'Zamów taki druk,',
            'druk-pdf' => 'Dokument.',
            'contact' => 'Jesteśmy w Katowicach.',
            'portfolio' => 'Realizacje',
            'faq' => 'Najczęstsze pytania',
            'delivery' => 'Dostawa i odbiór',
        ];

        foreach ($pages as $routeName => $heading) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_active_marketing_pages_use_verified_legacy_copy(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get(route('home'))
            ->assertSee('druk cyfrowy, offsetowy i wielkoformatowy')
            ->assertSee('firmy i agencje');

        $this->get(route('services.diploma'))
            ->assertSee('oprawę twardą, miękką lub kanałową');

        $this->get(route('druk-pdf'))
            ->assertSee('Wrzucasz kompletny PDF');

        $this->get(route('services.business'))
            ->assertSee('dla firm oraz agencji')
            ->assertSee('Układasz wiele pozycji w jednym zapytaniu');

        $this->get(route('portfolio'))
            ->assertSee('Materiały reklamowe dla firm muszą wyglądać profesjonalnie');

        $this->get(route('faq'))
            ->assertSee('Czy realizujecie małe nakłady?')
            ->assertSee('Czy obsługujecie klientów spoza Katowic?');

        $this->get(route('contact'))
            ->assertSee('LITEKST Jarosław Lipiec')
            ->assertSee('NIP 6342412192');
    }

    public function test_configurator_pages_use_the_main_layout(): void
    {
        $this->seed(ProductSeeder::class);
        foreach (['services.diploma', 'druk-pdf', 'services.business'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('concept.css', false)
                ->assertSee('cc-container--wide cc-hero-grid', false)
                ->assertSee('cc-container--wide cc-config', false)
                ->assertDontSee('noindex', false);
        }
    }

    public function test_contact_page_uses_the_production_layout(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('<header class="cc-header">', false)
            ->assertSee('concept.css', false)
            ->assertSee('cc-local-card', false)
            ->assertSee('cc-field', false)
            ->assertSee(route('home'), false)
            ->assertDontSee('index.html', false)
            ->assertDontSee('Montserrat', false);
    }

    public function test_business_configurator_sidebar_uses_shared_summary_action_spacing(): void
    {
        $this->seed(ProductSeeder::class);
        $this->get(route('services.business'))
            ->assertOk()
            ->assertSee('<div class="cc-summary-actions">', false)
            ->assertSee('id="b2b-imie"', false)
            ->assertSee('id="b2b-firma"', false)
            ->assertSee('id="b2b-email"', false)
            ->assertSee('id="b2b-telefon"', false)
            ->assertDontSee('Dokąd wysłać wycenę?')
            ->assertSee('Wersja demonstracyjna — wycena po kontakcie.');
    }

    public function test_business_configurator_uses_the_editable_product_images(): void
    {
        $this->seed(ProductSeeder::class);
        Product::query()->where('slug', 'wizytowki')->update(['image_path' => 'images/produkty/druk.png']);

        $this->get(route('services.business'))
            ->assertSee('copyCabanaB2bCatalog', false)
            ->assertSee('druk.png')
            ->assertSee('ulotki.png')
            ->assertSee('plakaty.png')
            ->assertSee('banery.png')
            ->assertSee('rollupy.png')
            ->assertSee('billboardy.png')
            ->assertSee('fotoobrazy.png')
            ->assertSee('fototapety.png')
            ->assertSee('kalendarze-spiralowane.png')
            ->assertSee('naklejki.png')
            ->assertSee('tabliczki-grawerowane.png')
            ->assertSee('rysunki-plany-mapycad.png')
            ->assertSee('ksero.png')
            ->assertSee('skanowanie.png')
            ->assertSee('zdjecia-do-dokumentow.png')
            ->assertSee('pieczatki.png')
            ->assertSee('projektowanie-graficzne.png')
            ->assertDontSee('oprawa-prac-i-bindowanie.png');
    }

    public function test_business_configurator_uses_the_configured_shipping_prices(): void
    {
        config(['business.shipping.parcel' => 19.5]);
        $this->seed(ProductSeeder::class);

        $this->get(route('services.business'))
            ->assertOk()
            ->assertSee('19,50 zł');
    }

    public function test_pdf_configurator_uses_the_hands_hero_image(): void
    {
        $this->get(route('druk-pdf'))
            ->assertSee('images/produkty/hands.png', false)
            ->assertSee('cc-hero-card--single', false)
            ->assertDontSee('cc-hero-mini-report', false)
            ->assertDontSee('cc-hero-card--tag', false);
    }

    public function test_business_configurator_uses_the_new_hero_image(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get(route('services.business'))
            ->assertSee('images/hero-new.webp', false)
            ->assertDontSee('Wybierz produkty, dołącz pliki i wyślij zapytanie.');
    }

    public function test_business_product_cards_keep_their_full_image_area(): void
    {
        $css = file_get_contents(public_path('css/concept.css'));

        $this->assertSame(true, str_contains($css, '.cc-b2b-card-image img'));
        $this->assertSame(true, str_contains($css, 'object-fit: contain;'));
        $this->assertSame(true, str_contains($css, 'aspect-ratio: 828 / 710;'));
    }

    public function test_business_configurator_uses_four_product_columns_on_wide_screens(): void
    {
        $css = file_get_contents(public_path('css/concept.css'));

        $this->get(route('services.business'))
            ->assertSee('cc-container--wide cc-hero-grid', false)
            ->assertSee('cc-container--wide cc-config', false);

        $this->assertStringContainsString('@media (min-width: 1536px)', $css);
        $this->assertStringContainsString('.cc-container--wide { max-width: 100rem; }', $css);
        $this->assertStringContainsString('.cc-b2b-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }', $css);
    }

    public function test_production_cta_styles_use_the_loaded_body_font(): void
    {
        $css = file_get_contents(public_path('css/concept.css'));

        $this->assertSame(true, str_contains($css, '.concept-site :is(.btn-magenta, .btn-geel, .btn-outline-light, .brand-upload-button)'));
        $this->assertSame(true, str_contains($css, "font-family: 'Inter', sans-serif;"));
        $this->assertSame(true, str_contains($css, '.cc-field .cc-toggle'));
        $this->assertSame(true, str_contains($css, 'text-transform: none;'));
    }

    public function test_configurators_render_the_inpost_point_picker(): void
    {
        $this->seed(ProductSeeder::class);
        foreach (['services.diploma', 'druk-pdf', 'services.business'] as $routeName) {
            $this->get(route($routeName))
                ->assertSee('cc-inpost-picker', false)
                ->assertSee('Wybierz punkt z listy InPost');
        }
    }

    public function test_configurator_option_cards_keep_the_selection_indicator_compact(): void
    {
        $this->seed(ProductSeeder::class);
        foreach (['services.diploma', 'druk-pdf', 'services.business'] as $routeName) {
            $this->get(route($routeName))
                ->assertDontSee('>Wybrano</em>', false)
                ->assertSee('cc-option-check', false);
        }

        $css = file_get_contents(public_path('css/concept.css'));

        $this->assertStringNotContainsString('width: 4.5rem;', $css);
    }

    public function test_configurator_option_grids_use_two_desktop_columns(): void
    {
        $css = file_get_contents(public_path('css/concept.css'));

        $this->assertStringContainsString('.cc-step-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }', $css);
        $this->assertStringNotContainsString('.cc-step-grid--3', $css);
    }

    public function test_diploma_configurator_places_copy_count_in_the_binding_step(): void
    {
        $this->seed(ProductSeeder::class);
        $content = $this->get(route('services.diploma'))->getContent();
        $stepTwoStart = strpos($content, '<section class="cc-step" id="druk">');
        $stepThreeStart = strpos($content, '<section class="cc-step" id="oprawa">');
        $stepFourStart = strpos($content, '<section class="cc-step" id="okladka">');
        $copyCountPosition = strpos($content, '<div class="cc-summary-copies">');

        $this->assertIsInt($stepTwoStart);
        $this->assertIsInt($stepThreeStart);
        $this->assertIsInt($stepFourStart);
        $this->assertIsInt($copyCountPosition);
        $this->assertStringNotContainsString('Liczba egzemplarzy', substr($content, $stepTwoStart, $stepThreeStart - $stepTwoStart));
        $this->assertGreaterThan($stepThreeStart, $copyCountPosition);
        $this->assertLessThan($stepFourStart, $copyCountPosition);
        $this->assertStringContainsString('value="mixed"', $content);
        $this->assertStringContainsString('kolorowe jako kolorowe', $content);
    }

    public function test_configurators_expose_actions_from_their_side_summaries_without_a_review_step(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get(route('services.diploma'))
            ->assertDontSee('id="podsumowanie"', false)
            ->assertSee('@click="addToCart()"', false);

        $this->get(route('druk-pdf'))
            ->assertDontSee('id="podsumowanie"', false)
            ->assertSee('@click="addToCart()"', false);

        $this->get(route('services.business'))
            ->assertDontSee('id="podsumowanie"', false)
            ->assertDontSee('Nie widzisz swojego druku?')
            ->assertSee('href="'.route('contact').'"', false)
            ->assertSee('@click="submitQuoteRequest()"', false);
    }

    public function test_pdf_and_thesis_configurators_add_configured_orders_to_the_cart(): void
    {
        $this->seed(ProductSeeder::class);
        foreach (['services.diploma', 'druk-pdf'] as $routeName) {
            $this->get(route($routeName))
                ->assertSee('js/cart.js', false)
                ->assertSee('@click="addToCart()"', false)
                ->assertSee('Dodaj do koszyka')
                ->assertSee('fa-shopping-cart', false)
                ->assertDontSee('id="dane"', false)
                ->assertDontSee('Podaj dane do zamówienia.');
        }

        $this->get(route('cart'))
            ->assertSee('submitConfiguredOrder', false)
            ->assertSee('Konfiguracja zapisana')
            ->assertSee('/api/v1/thesis/orders', false)
            ->assertSee('/api/v1/pdf/orders', false);

        $conceptJavaScript = file_get_contents(public_path('js/concept.js'));

        $this->assertStringContainsString('Cart.addItem(item)', $conceptJavaScript);
        $this->assertStringContainsString("orderType: 'thesis'", $conceptJavaScript);
        $this->assertStringContainsString("orderType: 'pdf'", $conceptJavaScript);
    }

    public function test_diploma_configurator_renders_the_live_thesis_binding_preview(): void
    {
        $this->seed(ProductSeeder::class);
        $this->get(route('services.diploma'))
            ->assertOk()
            ->assertSee('cc-thesis-preview', false)
            ->assertSee('cc-swatch', false)
            ->assertSee('cc-title-chip', false)
            ->assertSee('Kolor okładki', false)
            ->assertSee('Przykładowe napisy', false)
            ->assertSee('Uczelnia', false)
            ->assertSee('Uniwersytet Śląski w Katowicach', false)
            ->assertSee('Napis na okładce', false)
            ->assertSee('Praca Licencjacka', false)
            ->assertSee('Kolor napisu', false)
            ->assertSee('Rubinowy', false)
            ->assertSee('Nagranie pracy na CD', false)
            ->assertSee('20,00 zł', false)
            ->assertSee('Grawerowanie na grzbiecie', false)
            ->assertSee('Imię i nazwisko na grzbiecie', false)
            ->assertSee('30,00 zł', false)
            ->assertSee('spineEngravingName', false)
            ->assertSee('coverHeading()', false)
            ->assertSee('images/produkty/graduation.png', false)
            ->assertSee('cc-hero-card--single', false)
            ->assertDontSee('cc-hero-mini-report', false)
            ->assertDontSee('cc-hero-card--tag', false);
    }

    public function test_diploma_binding_options_render_image_placeholders(): void
    {
        $this->seed(ProductSeeder::class);
        $content = $this->get(route('services.diploma'))->getContent();

        $this->assertSame(3, substr_count($content, 'class="cc-binding-image-placeholder"'));
        $this->assertStringContainsString('Miejsce na zdjęcie', $content);
        $this->assertStringNotContainsString('cc-book', $content);
    }

    public function test_archived_pages_are_reachable_and_linked_from_the_archive_index(): void
    {
        $this->get(route('archive'))
            ->assertOk()
            ->assertSee('Archiwum')
            ->assertSee(route('archive.home'), false)
            ->assertSee(route('archive.diploma'), false)
            ->assertSee(route('archive.business'), false);

        foreach (['archive.home', 'archive.diploma', 'archive.business'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_old_duplicated_urls_redirect_to_the_new_slugs(): void
    {
        $this->get('/oprawa-prac-dyplomowych-katowice')->assertRedirect(route('services.diploma'));
        $this->get('/dla-firm')->assertRedirect(route('services.business'));
    }

    public function test_concept_playground_paths_redirect_to_production(): void
    {
        $this->get('/concept/strona')->assertRedirect('/');
        $this->get('/concept/strona/prace-dyplomowe')->assertRedirect('/prace-dyplomowe');
        $this->get('/concept/strona/druk-pdf')->assertRedirect('/druk-pdf');
        $this->get('/concept/strona/druk-dla-firm')->assertRedirect('/druk-dla-firm');
    }
}
