<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrintingConfigurationRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrintingConfigurationController extends Controller
{
    public function edit(string $type): View
    {
        $product = $this->product($type);
        $printing = $product->configuration['printing'] ?? [];
        $groups = $type === 'thesis'
            ? ['bindings' => 'Oprawy', 'covers' => 'Napisy na okładce', 'universities' => 'Uczelnie', 'cover_titles' => 'Tytuły prac', 'imprint_colors' => 'Kolory nadruku', 'cover_colors' => 'Kolory okładki']
            : ['finishes' => 'Wykończenia'];

        foreach ($groups as $key => $label) {
            $printing[$key] = collect($printing[$key] ?? [])->map(fn (mixed $value, string $code): array => [
                'key' => $code,
                ...(is_array($value) ? $value : ['label' => $value]),
            ])->values()->all();
        }

        return view('admin.products.printing', compact('product', 'printing', 'groups', 'type'));
    }

    public function update(PrintingConfigurationRequest $request, string $type): RedirectResponse
    {
        $product = $this->product($type);
        $printing = $request->validated();
        foreach (['bindings', 'covers', 'finishes', 'universities', 'cover_titles', 'imprint_colors', 'cover_colors'] as $group) {
            if (! isset($printing[$group])) {
                continue;
            }
            $printing[$group] = collect($printing[$group])->mapWithKeys(function (array $row) use ($group): array {
                $key = $row['key'];
                unset($row['key']);

                return [$key => in_array($group, ['universities', 'cover_titles'], true) ? $row['label'] : $row];
            })->all();
        }

        DB::transaction(function () use ($product, $printing): void {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $product->update(['configuration' => [...($product->configuration ?? []), 'printing' => $printing]]);
        });

        return redirect()->route('admin.printing.edit', $type)->with('status', 'Konfigurator i ceny zostały zapisane.');
    }

    private function product(string $type): Product
    {
        abort_unless(in_array($type, ['thesis', 'pdf'], true), 404);

        return Product::query()->where('slug', $type === 'thesis' ? 'praca-dyplomowa' : 'druk')->firstOrFail();
    }
}
