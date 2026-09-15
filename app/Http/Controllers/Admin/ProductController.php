<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductImageRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(25),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'catalogImages' => ProductImageRequest::catalogImagePaths(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product-images', 'public');
        } elseif (! empty($validated['catalog_image_path'])) {
            $imagePath = $validated['catalog_image_path'];
        }

        $oldImagePath = $product->image_path;
        try {
            DB::transaction(function () use ($product, $validated, $imagePath): void {
                $product = Product::query()->lockForUpdate()->findOrFail($product->id);
                $attributes = collect($validated)->only(['name', 'description', 'starting_price', 'is_active', 'sort_order'])->all();
                $configuration = $product->configuration ?? [];
                if (array_key_exists('fields', $validated)) {
                    $configuration['configurator']['fields'] = array_values($validated['fields']);
                }
                if (array_key_exists('starting_price', $validated)) {
                    $configuration['starting_price'] = $validated['starting_price'];
                }
                $attributes['configuration'] = $configuration;
                if ($imagePath !== null) {
                    $attributes['image_path'] = $imagePath;
                }
                $product->update($attributes);
            });
        } catch (\Throwable $exception) {
            if ($request->hasFile('image') && $imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $exception;
        }
        if ($imagePath !== null && $imagePath !== $oldImagePath && $this->isUploadedImage($oldImagePath)) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route('admin.products.index')->with('status', 'Produkt, zdjęcie i konfigurator zostały zapisane.');
    }

    private function isUploadedImage(?string $imagePath): bool
    {
        return $imagePath !== null && str_starts_with($imagePath, 'product-images/');
    }
}
