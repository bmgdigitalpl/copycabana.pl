<?php

namespace App\Console\Commands;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

#[Signature('configurator:sync {--force : Replace a configurator that is already saved, e.g. prices changed in the admin panel}')]
#[Description('Load the print configurator (bindings, covers, universities, cover colours with their photos and textures) into the database and the public disk')]
class SyncConfigurator extends Command
{
    private const DATA_PATH = 'seeders/data';

    public function handle(): int
    {
        $settings = json_decode((string) File::get(database_path(self::DATA_PATH.'/configurator.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->callSilent('db:seed', ['--class' => ProductSeeder::class, '--force' => true]);

        foreach ($settings as $slug => $printing) {
            $product = Product::query()->where('slug', $slug)->first();

            if ($product === null) {
                $this->error("{$slug}: the product does not exist.");

                return self::FAILURE;
            }

            $configuration = $product->configuration ?? [];

            if ($this->isAlreadyConfigured($slug, $configuration['printing'] ?? null) && ! $this->option('force')) {
                $this->warn("{$slug}: the configurator is already saved, skipped (use --force to replace it).");

                continue;
            }

            $product->update(['configuration' => [...$configuration, 'printing' => $printing]]);
            $this->info("{$slug}: the configurator is saved.");
        }

        $copied = $this->copyPublicFiles();
        $this->info("Copied {$copied} cover photo/texture file(s) to the public disk.");

        return self::SUCCESS;
    }

    /**
     * The product seeder leaves a bare default behind, so the thesis configurator counts as saved
     * only once it has cover colours; the plain print one as soon as it exists.
     */
    private function isAlreadyConfigured(string $slug, mixed $printing): bool
    {
        if (! is_array($printing)) {
            return false;
        }

        return $slug !== 'praca-dyplomowa' || ($printing['cover_colors'] ?? []) !== [];
    }

    /** Cover photos and textures that go with the saved cover colours. */
    private function copyPublicFiles(): int
    {
        $source = database_path(self::DATA_PATH.'/public-disk');
        $disk = Storage::disk('public');
        $copied = 0;

        foreach (File::allFiles($source) as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());

            if ($disk->exists($path) && ! $this->option('force')) {
                continue;
            }

            $disk->put($path, File::get($file->getPathname()));
            $copied++;
        }

        return $copied;
    }
}
