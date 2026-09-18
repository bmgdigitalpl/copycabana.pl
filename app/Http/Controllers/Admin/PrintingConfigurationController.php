<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrintingConfigurationRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        if (isset($printing['cover_colors'])) {
            $printing['cover_colors'] = collect($printing['cover_colors'])->map(function (array $row): array {
                $row['photo_url'] = ! empty($row['photo']) ? Storage::disk('public')->url($row['photo']) : null;

                return $row;
            })->all();
        }

        return view('admin.products.printing', compact('product', 'printing', 'groups', 'type'));
    }

    public function update(PrintingConfigurationRequest $request, string $type): RedirectResponse
    {
        $product = $this->product($type);
        $printing = $request->validated();
        $previousCoverColors = collect($product->configuration['printing']['cover_colors'] ?? []);

        foreach (['bindings', 'covers', 'finishes', 'universities', 'cover_titles', 'imprint_colors', 'cover_colors'] as $group) {
            if (! isset($printing[$group])) {
                continue;
            }
            $printing[$group] = collect($printing[$group])->mapWithKeys(function (array $row) use ($group, $previousCoverColors): array {
                $key = $row['key'];
                unset($row['key']);

                if ($group === 'cover_colors') {
                    $row = $this->resolveCoverColorPhoto($row, $previousCoverColors->get($key));
                }

                return [$key => in_array($group, ['universities', 'cover_titles'], true) ? $row['label'] : $row];
            })->all();
        }

        if (isset($printing['cover_colors'])) {
            $this->deleteOrphanedCoverPhotos($previousCoverColors, collect($printing['cover_colors']));
        }

        DB::transaction(function () use ($product, $printing): void {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $product->update(['configuration' => [...($product->configuration ?? []), 'printing' => $printing]]);
        });

        return redirect()->route('admin.printing.edit', $type)->with('status', 'Konfigurator i ceny zostały zapisane.');
    }

    /**
     * A cover color row can either keep its previously uploaded photo (via the "existing_photo"
     * hidden field the form round-trips) or replace it with a freshly uploaded file.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>|null  $previous
     * @return array<string, mixed>
     */
    private function resolveCoverColorPhoto(array $row, ?array $previous): array
    {
        $upload = $row['photo'] ?? null;
        $existingPhoto = is_string($row['existing_photo'] ?? null) ? $row['existing_photo'] : ($previous['photo'] ?? null);
        unset($row['photo'], $row['existing_photo']);

        if ($upload instanceof UploadedFile) {
            $row['photo'] = $upload->store('thesis-cover-photos', 'public');

            if (is_string($existingPhoto) && $existingPhoto !== '' && $existingPhoto !== $row['photo']) {
                Storage::disk('public')->delete($existingPhoto);
            }
        } elseif (is_string($existingPhoto) && $existingPhoto !== '') {
            $row['photo'] = $existingPhoto;
        }

        return $row;
    }

    /** Remove uploaded photos belonging to cover colors the admin deleted from the list. */
    private function deleteOrphanedCoverPhotos(Collection $previousCoverColors, Collection $newCoverColors): void
    {
        $previousCoverColors
            ->except($newCoverColors->keys()->all())
            ->pluck('photo')
            ->filter(fn (mixed $photo): bool => is_string($photo) && $photo !== '')
            ->each(fn (string $photo) => Storage::disk('public')->delete($photo));
    }

    private function product(string $type): Product
    {
        abort_unless(in_array($type, ['thesis', 'pdf'], true), 404);

        return Product::query()->where('slug', $type === 'thesis' ? 'praca-dyplomowa' : 'druk')->firstOrFail();
    }
}
