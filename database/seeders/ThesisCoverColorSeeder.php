<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\CoverTextureExtractor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces the cover colours of the thesis configurator with the ones in
 * seeders/data/configurator.json: copies their photos to the public disk, cuts the preview
 * tiles and swatch pictures out of them again and measures each colour from its photo. Colours
 * that are no longer in the file lose their photo, tile and swatch picture.
 *
 * The swatches are put in order when the configurator is read (CoverColorSorter).
 *
 * php artisan db:seed --class=ThesisCoverColorSeeder --force
 */
class ThesisCoverColorSeeder extends Seeder
{
    private const DATA_PATH = 'seeders/data';

    public function run(CoverTextureExtractor $textures): void
    {
        $this->call(ProductSeeder::class);

        $settings = json_decode(File::get(database_path(self::DATA_PATH.'/configurator.json')), true, flags: JSON_THROW_ON_ERROR);
        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $configuration = $product->configuration ?? [];
        $disk = Storage::disk('public');
        $colors = [];

        foreach ($settings['praca-dyplomowa']['cover_colors'] as $key => $color) {
            $photo = File::get(database_path(self::DATA_PATH.'/public-disk/'.$color['photo']));
            $extracted = $textures->extract($photo, whitePaperStrength: CoverTextureExtractor::whitePaperStrength((string) $key));

            $disk->put($color['photo'], $photo);
            $disk->put(CoverTextureExtractor::texturePath($color['photo']), $extracted['texture']);
            $disk->put(CoverTextureExtractor::swatchPath($color['photo']), $extracted['swatch']);
            $colors[$key] = ['label' => $color['label'], 'hex' => $extracted['hex'], 'photo' => $color['photo']];
        }

        foreach ($configuration['printing']['cover_colors'] ?? [] as $previous) {
            $photoPath = $previous['photo'] ?? null;

            if (is_string($photoPath) && $photoPath !== '' && ! in_array($photoPath, array_column($colors, 'photo'), true)) {
                $disk->delete([$photoPath, CoverTextureExtractor::texturePath($photoPath), CoverTextureExtractor::swatchPath($photoPath)]);
            }
        }

        $configuration['printing']['cover_colors'] = $colors;
        $product->update(['configuration' => $configuration]);
    }
}
