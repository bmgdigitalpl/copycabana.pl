<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ConfiguratorSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function __invoke(ConfiguratorSettings $settings): View
    {
        $product = Product::query()->active()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $printing = $settings->printing('thesis');

        return view('prace-dyplomowe', [
            'thesisPricing' => [...$printing, 'shipping' => config('business.shipping')],
            'thesisProduct' => $product,
            'coverPhotos' => $this->coverPhotos($printing['cover_colors'] ?? []),
        ]);
    }

    /**
     * Real product photos of the blank covers, keyed by cover color code, as uploaded by an
     * admin at /dashboard/printing/thesis. A color without a photo yet simply falls back to
     * the code-generated mockup in the configurator preview.
     *
     * @param  array<string, array{photo?: string|null}>  $coverColors
     * @return array<string, string>
     */
    private function coverPhotos(array $coverColors): array
    {
        $photos = [];

        foreach ($coverColors as $colorKey => $color) {
            $photoPath = $color['photo'] ?? null;

            if (is_string($photoPath) && $photoPath !== '') {
                $photos[$colorKey] = Storage::disk('public')->url($photoPath);
            }
        }

        return $photos;
    }
}
