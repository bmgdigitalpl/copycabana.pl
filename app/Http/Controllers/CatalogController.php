<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        return view('products', [
            'products' => Product::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Request $request): View
    {
        $slug = $request->string('slug')->toString();
        $product = Product::query()
            ->active()
            ->with(['options' => fn ($query) => $query->active()->with(['values' => fn ($values) => $values->where('is_active', true)])])
            ->when($slug !== '', fn ($query) => $query->where('slug', $slug))
            ->orderBy('sort_order')
            ->first();

        if ($product === null && $slug !== '') {
            abort(404);
        }

        if ($product === null) {
            $product = new Product([
                'slug' => 'produkt',
                'name' => 'Produkt',
                'description' => 'Wybierz produkt z katalogu CopyCabana.',
                'category' => 'oferta',
                'starting_price' => 0,
            ]);
            $product->setRelation('options', collect());
        }

        return view('product', compact('product'));
    }
}
