<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrintingConfigurationRequest;
use App\Models\Product;
use App\Services\CoverTextureExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PrintingConfigurationController extends Controller
{
    public function __construct(private readonly CoverTextureExtractor $coverTextures) {}

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
                $disk = Storage::disk('public');
                $texturePath = ! empty($row['photo']) ? CoverTextureExtractor::texturePath($row['photo']) : null;
                $row['photo_url'] = ! empty($row['photo']) ? $disk->url($row['photo']) : null;
                $row['texture_url'] = $texturePath !== null && $disk->exists($texturePath)
                    ? $disk->url($texturePath).'?v='.$disk->lastModified($texturePath)
                    : null;

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
                    $row = $this->resolveCoverColorPhoto((string) $key, $row, $previousCoverColors->get($key));
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
     * A new photo also sets the colour of the row to the cloth's average colour and cuts the
     * texture tile used by the preview out of it. When only the colour was edited by hand, the
     * tile is repainted in that colour, so the preview always matches the colour of the row.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>|null  $previous
     * @return array<string, mixed>
     *
     * @throws ValidationException when the uploaded photo cannot be read
     */
    private function resolveCoverColorPhoto(string $key, array $row, ?array $previous): array
    {
        $disk = Storage::disk('public');
        $upload = $row['photo'] ?? null;
        $existingPhoto = is_string($row['existing_photo'] ?? null) ? $row['existing_photo'] : ($previous['photo'] ?? null);
        unset($row['photo'], $row['existing_photo']);

        if ($upload instanceof UploadedFile) {
            try {
                $extracted = $this->coverTextures->extract((string) $upload->get(), whitePaperStrength: CoverTextureExtractor::whitePaperStrength($key));
            } catch (RuntimeException) {
                throw ValidationException::withMessages(['cover_colors' => 'Nie udało się odczytać wgranego zdjęcia okładki. Wgraj inne zdjęcie.']);
            }

            $row['photo'] = $upload->store('thesis-cover-photos', 'public');
            $row['hex'] = $extracted['hex'];
            $disk->put(CoverTextureExtractor::texturePath($row['photo']), $extracted['texture']);
            $disk->put(CoverTextureExtractor::swatchPath($row['photo']), $extracted['swatch']);

            if (is_string($existingPhoto) && $existingPhoto !== '' && $existingPhoto !== $row['photo']) {
                $this->deleteCoverPhoto($existingPhoto);
            }
        } elseif (is_string($existingPhoto) && $existingPhoto !== '') {
            $row['photo'] = $existingPhoto;

            $colorChanged = strcasecmp((string) $row['hex'], (string) ($previous['hex'] ?? '')) !== 0;

            if ($colorChanged || ! $disk->exists(CoverTextureExtractor::texturePath($existingPhoto)) || ! $disk->exists(CoverTextureExtractor::swatchPath($existingPhoto))) {
                $this->rebuildCoverTexture($key, $existingPhoto, $row['hex']);
            }
        }

        return $row;
    }

    /** Cut the texture tile and the swatch picture out of a stored photo again, painted in the given colour. */
    private function rebuildCoverTexture(string $key, string $photoPath, string $hex): void
    {
        $disk = Storage::disk('public');

        try {
            $photo = $disk->get($photoPath);

            if ($photo !== null) {
                $extracted = $this->coverTextures->extract($photo, $hex, CoverTextureExtractor::whitePaperStrength($key));
                $disk->put(CoverTextureExtractor::texturePath($photoPath), $extracted['texture']);
                $disk->put(CoverTextureExtractor::swatchPath($photoPath), $extracted['swatch']);
            }
        } catch (RuntimeException) {
            // The stored photo cannot be read any more; the preview keeps the plain colour.
        }
    }

    private function deleteCoverPhoto(string $photoPath): void
    {
        Storage::disk('public')->delete([$photoPath, CoverTextureExtractor::texturePath($photoPath), CoverTextureExtractor::swatchPath($photoPath)]);
    }

    /** Remove uploaded photos (and their textures) belonging to cover colors the admin deleted from the list. */
    private function deleteOrphanedCoverPhotos(Collection $previousCoverColors, Collection $newCoverColors): void
    {
        $previousCoverColors
            ->except($newCoverColors->keys()->all())
            ->pluck('photo')
            ->filter(fn (mixed $photo): bool => is_string($photo) && $photo !== '')
            ->each(fn (string $photo) => $this->deleteCoverPhoto($photo));
    }

    private function product(string $type): Product
    {
        abort_unless(in_array($type, ['thesis', 'pdf'], true), 404);

        return Product::query()->where('slug', $type === 'thesis' ? 'praca-dyplomowa' : 'druk')->firstOrFail();
    }
}
