<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** Public, indexable pages as named routes. */
    private const SITEMAP_ROUTES = [
        'home',
        'services.diploma',
        'services.business',
        'about',
        'contact',
        'faq',
        'portfolio',
        'delivery',
        'privacy',
        'cookies',
    ];

    /**
     * Private or technical paths crawlers have no use for. The cart, account and login pages are
     * left crawlable on purpose: a crawler has to fetch them to see their noindex.
     */
    private const DISALLOWED_PATHS = [
        '/dashboard',
        '/zamowienie',
        '/wycena',
        '/platnosc-testowa',
        '/api/',
        '/test/',
    ];

    public function favicon(): RedirectResponse
    {
        return redirect(asset('favicon-48x48.png'), 301);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        foreach (self::DISALLOWED_PATHS as $path) {
            $lines[] = "Disallow: {$path}";
        }

        $lines[] = 'Allow: /';
        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = array_map(fn (string $name): string => route($name), self::SITEMAP_ROUTES);

        return response()
            ->view('sitemap', ['urls' => $urls], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
