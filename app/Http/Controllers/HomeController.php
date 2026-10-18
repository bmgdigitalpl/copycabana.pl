<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CmsContent;
use App\Services\ConfiguratorSettings;
use App\Services\ThesisCoverPreview;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Cover colour keys shown first on the home page, when they exist and have a texture. */
    private const HOME_COVER_COLORS = ['granat-klasyczny', 'bordowy', 'czarny', 'zielen-butelkowa', 'czerwony', 'turkus', 'fiolet', 'bezowy', 'srebrny-metalik', 'zloty-metalik'];

    private const HOME_COVER_LIMIT = 10;

    public function __invoke(CmsContent $content, ConfiguratorSettings $settings, ThesisCoverPreview $coverPreview): View
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

        $thesisImage = Product::query()->where('slug', 'praca-dyplomowa')->first()?->imageUrl()
            ?? asset('images/produkty/graduation.png');

        return view('home', [...compact('cms', 'thesisImage'), 'homeCover' => $this->homeCover($settings, $coverPreview)]);
    }

    /**
     * The cover shown in the thesis call to action: the preview data and a handful of cover colours
     * that have a cloth texture. Null when the thesis configurator is not set up yet.
     *
     * @return array{preview: array<string, mixed>, colors: list<array{key: string, label: string, hex: string, texture: string|null, swatch: string|null}>}|null
     */
    private function homeCover(ConfiguratorSettings $settings, ThesisCoverPreview $coverPreview): ?array
    {
        try {
            $printing = $settings->printing('thesis');
        } catch (ValidationException) {
            return null;
        }

        $preview = $coverPreview->build($printing);
        $colors = collect($printing['cover_colors'] ?? [])
            ->map(fn (array $color, string $key): array => [
                'key' => $key,
                'label' => (string) ($color['label'] ?? $key),
                'hex' => (string) ($color['hex'] ?? '#d0d7de'),
                'texture' => $preview['textures'][$key] ?? null,
                'swatch' => $preview['swatches'][$key] ?? null,
            ])
            ->filter(fn (array $color): bool => $color['texture'] !== null && preg_match('/^#[0-9a-fA-F]{6}$/', $color['hex']) === 1);
        $preferred = $colors->only(self::HOME_COVER_COLORS);
        $colors = ($preferred->count() >= 3 ? $preferred : $colors->take(self::HOME_COVER_LIMIT))->values()->all();

        if ($colors === [] || ! isset($preview['logos']['us'])) {
            return null;
        }

        return ['preview' => $preview, 'colors' => $colors];
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
