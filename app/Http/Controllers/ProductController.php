<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'in:druk,reklama,foto,uslugi'],
        ]);

        $products = Product::query()
            ->active()
            ->when(
                $validated['category'] ?? null,
                fn (Builder $query, string $category): Builder => $query->where('category', $category),
            )
            ->orderBy('sort_order')
            ->get();

        return ProductResource::collection($products);
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
