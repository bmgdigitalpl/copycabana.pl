<?php

namespace Tests\Feature;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_description_canonical_social_tags_and_local_business_data(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->get(route('home'))->assertOk();

        $response
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">', false)
            ->assertSee('<meta property="og:title" content="CopyCabana — drukarnia w Katowicach: prace dyplomowe i druk">', false)
            ->assertSee('<meta property="og:image" content="'.asset('images/og-default.png').'">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee(asset('favicon.svg'), false)
            ->assertSee(asset('apple-touch-icon.png'), false)
            ->assertSee(asset('site.webmanifest'), false)
            ->assertSee('"@type":"LocalBusiness"', false)
            ->assertSee('"streetAddress":"ul. Bankowa 11"', false);
        $this->assertSame(1, substr_count($response->getContent(), '<meta name="description"'));
    }

    public function test_service_pages_describe_the_service_and_a_breadcrumb(): void
    {
        $this->seed(ProductSeeder::class);

        $this->get(route('services.diploma'))
            ->assertOk()
            ->assertSee('"@type":"Service"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
        $this->get(route('services.business'))
            ->assertOk()
            ->assertSee('"@type":"Service"', false);
    }

    public function test_private_pages_are_noindex_and_have_no_canonical_or_social_tags(): void
    {
        $response = $this->get(route('cart'))->assertOk();

        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertDontSee('rel="canonical"', false);
        $response->assertDontSee('og:title', false);
    }

    public function test_robots_txt_points_at_the_sitemap_and_hides_private_paths(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /dashboard', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_sitemap_lists_the_public_pages_only(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.route('home').'</loc>', false)
            ->assertSee('<loc>'.route('services.diploma').'</loc>', false)
            ->assertSee('<loc>'.route('services.business').'</loc>', false)
            ->assertSee('<loc>'.route('contact').'</loc>', false)
            ->assertDontSee(route('cart'), false)
            ->assertDontSee('/dashboard', false);
    }

    public function test_favicon_files_exist_and_the_ico_url_redirects_to_a_png(): void
    {
        foreach (['favicon.svg', 'favicon-16x16.png', 'favicon-32x32.png', 'favicon-48x48.png', 'apple-touch-icon.png', 'icon-192.png', 'icon-512.png', 'site.webmanifest', 'images/og-default.png'] as $file) {
            $this->assertFileExists(public_path($file));
            $this->assertGreaterThan(0, filesize(public_path($file)), $file);
        }

        $this->get('/favicon.ico')->assertRedirect(asset('favicon-48x48.png'));
    }
}
