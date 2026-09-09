<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    public function test_product_and_cart_load_the_shared_interactions(): void
    {
        foreach (['product', 'cart'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('<script src="'.asset('js/app.js').'"></script>', false);
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

    public function test_homepage_uses_the_main_layout_and_exposes_the_three_order_paths(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ty masz plik.')
            ->assertSee('My zajmiemy się drukiem.')
            ->assertSee('Wydrukuj i opraw pracę')
            ->assertSee('Zamów druk dla firmy')
            ->assertSee('Chcę wydrukować dokumenty PDF')
            ->assertSee(route('services.diploma'), false)
            ->assertSee(route('services.business'), false)
            ->assertSee(route('druk-pdf'), false);
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

    public function test_configurator_pages_use_the_main_layout(): void
    {
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
        $this->get(route('services.business'))
            ->assertOk()
            ->assertSee('<div class="cc-summary-actions">', false)
            ->assertSee('Wersja demonstracyjna — wycena po kontakcie.');
    }

    public function test_production_cta_styles_use_the_loaded_body_font(): void
    {
        $css = file_get_contents(public_path('css/concept.css'));

        $this->assertSame(true, str_contains($css, '.concept-site :is(.btn-magenta, .btn-geel, .btn-outline-light, .brand-upload-button)'));
        $this->assertSame(true, str_contains($css, "font-family: 'Inter', sans-serif;"));
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
            ->assertSee('activeVariant().degree', false);
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
