<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductImageRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
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

    public function update(ProductImageRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product-images', 'public');
        } elseif (! empty($validated['catalog_image_path'])) {
            $imagePath = $validated['catalog_image_path'];
        }

        if ($imagePath !== null && $imagePath !== $product->image_path) {
            $oldImagePath = $product->image_path;
            $product->update(['image_path' => $imagePath]);

            if ($this->isUploadedImage($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        return redirect()->route('admin.products.index')->with('status', 'Zdjęcie produktu zostało zaktualizowane.');
    }

    private function isUploadedImage(?string $imagePath): bool
    {
        return $imagePath !== null && str_starts_with($imagePath, 'product-images/');
    }
}
