<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ConfiguratorSettings;
use App\Services\ThesisCoverPreview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function __invoke(Request $request, ConfiguratorSettings $settings, ThesisCoverPreview $coverPreview): View
    {
        $product = Product::query()->active()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $printing = $settings->printing('thesis');

        return view('prace-dyplomowe', [
            'thesisPricing' => [...$printing, 'shipping' => config('business.shipping')],
            'thesisProduct' => $product,
            'coverPreview' => $coverPreview->build($printing, $request->boolean('mapa')),
        ]);
    }
}
