<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ConfiguratorSettings;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function __invoke(ConfiguratorSettings $settings): View
    {
        $product = Product::query()->active()->where('slug', 'praca-dyplomowa')->firstOrFail();

        return view('prace-dyplomowe', [
            'thesisPricing' => [...$settings->printing('thesis'), 'shipping' => config('business.shipping')],
            'thesisProduct' => $product,
        ]);
    }
}
