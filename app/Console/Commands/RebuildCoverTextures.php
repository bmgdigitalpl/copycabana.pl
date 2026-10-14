<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\CoverTextureExtractor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Signature('covers:rebuild-textures {--measure : Also set every colour to the average measured in its photo}')]
#[Description('Cut the cover preview textures out of the uploaded cover photos again, e.g. after changing the sample region')]
class RebuildCoverTextures extends Command
{
    public function handle(CoverTextureExtractor $textures): int
    {
        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $configuration = $product->configuration ?? [];
        $colors = $configuration['printing']['cover_colors'] ?? [];
        $disk = Storage::disk('public');
        $rebuilt = 0;

        foreach ($colors as $key => $color) {
            $photoPath = $color['photo'] ?? null;

            if (! is_string($photoPath) || $photoPath === '') {
                continue;
            }

            $photo = $disk->get($photoPath);

            if ($photo === null) {
                $this->warn("{$key}: the photo file is missing.");

                continue;
            }

            try {
                $result = $textures->extract($photo, $this->option('measure') ? null : $color['hex']);
            } catch (RuntimeException $exception) {
                $this->warn("{$key}: {$exception->getMessage()}");

                continue;
            }

            $disk->put(CoverTextureExtractor::texturePath($photoPath), $result['texture']);
            $colors[$key]['hex'] = $result['hex'];
            $this->line("{$key}: {$result['hex']}");
            $rebuilt++;
        }

        if ($this->option('measure') && $rebuilt > 0) {
            $configuration['printing']['cover_colors'] = $colors;
            $product->update(['configuration' => $configuration]);
        }

        $this->info("Rebuilt {$rebuilt} texture(s).");

        return self::SUCCESS;
    }
}
