<?php

namespace App\Http\Controllers;

use App\Services\CmsContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CmsContent $content): View
    {
        $cms = $content->home();
        $cms['hero']['primary_url'] = $content->url($cms['hero']['primary_link'] ?? null);
        $cms['hero']['secondary_url'] = $content->url($cms['hero']['secondary_link'] ?? null);
        $cms['why']['cta_primary_url'] = $content->url($cms['why']['cta_primary_link'] ?? null);
        $cms['why']['cta_secondary_url'] = $content->url($cms['why']['cta_secondary_link'] ?? null);
        $cms['contact']['phone_url'] = $content->url($cms['contact']['phone_link'] ?? null);
        $cms['contact']['email_url'] = $content->url($cms['contact']['email_link'] ?? null);

        $cms['hero-cards']['items'] = collect($cms['hero-cards']['items'] ?? [])
            ->map(fn (array $item): array => [...$item, 'url' => $content->url($item['link'] ?? null)])
            ->all();
        $cms['services']['items'] = collect($cms['services']['items'] ?? [])
            ->map(fn (array $item): array => [
                ...$item,
                'image_url' => $this->assetUrl($item['image'] ?? null),
                'url' => $content->url($item['href'] ?? null),
            ])
            ->all();

        return view('home', compact('cms'));
    }

    private function assetUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if (preg_match('/^https?:/', $path) === 1) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
