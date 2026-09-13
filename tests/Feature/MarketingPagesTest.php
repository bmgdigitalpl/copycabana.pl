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
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Od pliku do')
            ->assertSee('gotowego wydruku.')
            ->assertSee('Prace dyplomowe, dokumenty PDF oraz materiały dla firm i agencji.')
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
            ->assertSee('USŁUGI')
            ->assertSee('cc-gallery', false)
            ->assertSee('cc-marquee-group', false)
            ->assertDontSee('Chcę wydrukować dokumenty PDF')
            ->assertDontSee('Cena i kontrola')
            ->assertDontSee('Jakość')
            ->assertSee(route('services.diploma'), false)
            ->assertSee(route('services.business'), false)
            ->assertSee(route('druk-pdf'), false);

        $this->assertSame(3, substr_count($response->getContent(), 'class="cc-need-icon"'));
    }

    public function test_homepage_hero_renders_static_information_cards(): void
    {
        $content = $this->get(route('home'))->getContent();

        $this->assertSame(1, substr_count($content, 'class="hero-stack"'));
        $this->assertStringContainsString('stack-card-main', $content);
        $this->assertStringContainsString('24h', $content);
        $this->assertStringNotContainsString('data-cc-hero-slider', $content);
        $this->assertStringNotContainsString('cc-hero-slider-track', $content);
        $this->assertStringNotContainsString('cc-hero-slide', $content);

        $heroCss = file_get_contents(public_path('css/dynamic-local-service.css'));
        $conceptCss = file_get_contents(public_path('css/concept.css'));

        $this->assertStringContainsString('.hero-stack', $heroCss);
        $this->assertStringContainsString('.stack-card-main', $heroCss);
        $this->assertStringNotContainsString('.cc-hero-slider', $conceptCss);
    }

    public function test_marketing_heroes_use_the_single_card_layout(): void
    {
        foreach (['services.diploma', 'druk-pdf', 'services.business'] as $routeName) {
            $content = $this->get(route($routeName))->getContent();

            $this->assertSame(1, substr_count($content, 'class="cc-hero-card cc-hero-card--single"'));
            $this->assertStringNotContainsString('cc-hero-card--a', $content);
            $this->assertStringNotContainsString('cc-hero-card--b', $content);
            $this->assertStringNotContainsString('cc-hero-card--tag', $content);
        }

        $this->get(route('druk-pdf'))
            ->assertSee('Zazwyczaj realizujemy dokumenty w 24h.')
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
            ->assertSee('dopasuje format, nakład oraz technologię druku');

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
            ->assertSee('oprawa-prac-i-bindowanie.png');
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

    public function test_business_product_cards_keep_their_full_image_area(): void
    {
        $css = file_get_contents(public_path('css/concept.css'));

        $this->assertSame(true, str_contains($css, '.cc-b2b-card-image img'));
        $this->assertSame(true, str_contains($css, 'object-fit: contain;'));
        $this->assertSame(true, str_contains($css, 'aspect-ratio: 828 / 710;'));
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
        foreach (['services.diploma', 'druk-pdf', 'services.business'] as $routeName) {
            $this->get(route($routeName))
                ->assertSee('cc-inpost-picker', false)
                ->assertSee('Wybierz punkt z listy InPost');
        }
    }

    public function test_diploma_configurator_places_copy_count_in_the_summary_step(): void
    {
        $content = $this->get(route('services.diploma'))->getContent();
        $stepTwoStart = strpos($content, '<section class="cc-step" id="druk">');
        $stepThreeStart = strpos($content, '<section class="cc-step" id="oprawa">');
        $summaryStart = strpos($content, '<section class="cc-step" id="podsumowanie">');
        $copyCountPosition = strpos($content, '<div class="cc-summary-copies">');

        $this->assertIsInt($stepTwoStart);
        $this->assertIsInt($stepThreeStart);
        $this->assertIsInt($summaryStart);
        $this->assertIsInt($copyCountPosition);
        $this->assertStringNotContainsString('Liczba egzemplarzy', substr($content, $stepTwoStart, $stepThreeStart - $stepTwoStart));
        $this->assertGreaterThan($summaryStart, $copyCountPosition);
        $this->assertStringContainsString('value="color"', $content);
        $this->assertStringContainsString('całość kolorowa', $content);
    }

    public function test_diploma_configurator_renders_the_live_thesis_binding_preview(): void
    {
        $this->get(route('services.diploma'))
            ->assertOk()
            ->assertSee('cc-thesis-preview', false)
            ->assertSee('cc-swatch', false)
            ->assertSee('cc-title-chip', false)
            ->assertSee('Kolor okładki', false)
            ->assertSee('Przykładowe napisy', false)
            ->assertSee('activeVariant().degree', false)
            ->assertSee('images/produkty/graduation.png', false)
            ->assertSee('cc-hero-card--single', false)
            ->assertDontSee('cc-hero-mini-report', false)
            ->assertDontSee('cc-hero-card--tag', false);
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
