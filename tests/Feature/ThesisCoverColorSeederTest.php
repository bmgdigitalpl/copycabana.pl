<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\ConfiguratorSettings;
use App\Services\CoverTextureExtractor;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ThesisCoverColorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThesisCoverColorSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_the_cover_colours_with_the_ones_from_the_data_file(): void
    {
        Storage::fake('public');
        config(['cover_preview.texture.tile' => 64]);
        $this->seed(ProductSeeder::class);
        $this->giveTheThesisAnOldCoverColor();

        $this->seed(ThesisCoverColorSeeder::class);

        $printing = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail()->configuration['printing'];
        $expected = json_decode(File::get(database_path('seeders/data/configurator.json')), true)['praca-dyplomowa']['cover_colors'];

        $this->assertEqualsCanonicalizing(array_keys($expected), array_keys($printing['cover_colors']));
        $this->assertArrayHasKey('bindings', $printing);

        foreach ($printing['cover_colors'] as $color) {
            Storage::disk('public')->assertExists([$color['photo'], CoverTextureExtractor::texturePath($color['photo']), CoverTextureExtractor::swatchPath($color['photo'])]);
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $color['hex']);
        }

        Storage::disk('public')->assertMissing(['thesis-cover-photos/stary.jpg', 'thesis-cover-textures/stary.jpg']);
    }

    public function test_the_configurator_shows_the_seeded_colours_with_look_alikes_next_to_each_other(): void
    {
        Storage::fake('public');
        config(['cover_preview.texture.tile' => 64]);

        $this->seed(ThesisCoverColorSeeder::class);
        $order = array_keys(app(ConfiguratorSettings::class)->printing('thesis')['cover_colors']);

        // Light neutrals first, dark ones last, whatever order the database keeps the keys in.
        $this->assertEqualsCanonicalizing(['bialy', 'perlowy', 'srebrny-metalik', 'szary-jasny'], array_slice($order, 0, 4));
        $this->assertSame(['grafit-metalik', 'czarny'], array_slice($order, -2));
        $this->assertSame(1, abs(array_search('granat-ciemny', $order) - array_search('granat-klasyczny', $order)));
        $this->assertSame(1, abs(array_search('blekit', $order) - array_search('niebieski-jasny', $order)));
        $this->assertSame(1, abs(array_search('czerwony', $order) - array_search('czerwony-jasny', $order)));
    }

    private function giveTheThesisAnOldCoverColor(): void
    {
        Storage::disk('public')->put('thesis-cover-photos/stary.jpg', 'photo');
        Storage::disk('public')->put('thesis-cover-textures/stary.jpg', 'tile');

        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $configuration = $product->configuration;
        $configuration['printing']['cover_colors'] = ['stary' => ['label' => 'Stary', 'hex' => '#123456', 'photo' => 'thesis-cover-photos/stary.jpg']];
        $product->update(['configuration' => $configuration]);
    }
}
