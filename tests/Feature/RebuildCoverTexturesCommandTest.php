<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CoverTextureExtractor;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RebuildCoverTexturesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_cuts_the_textures_again_in_the_saved_colours(): void
    {
        $photo = $this->storeCoverPhoto([200, 100, 50]);

        $this->artisan('covers:rebuild-textures')->assertSuccessful();

        Storage::disk('public')->assertExists(CoverTextureExtractor::texturePath($photo));
        $this->assertSame('#063A60', $this->granat()['hex']);
    }

    public function test_it_can_set_every_colour_to_the_one_measured_in_its_photo(): void
    {
        $this->storeCoverPhoto([200, 100, 50]);

        $this->artisan('covers:rebuild-textures', ['--measure' => true])->assertSuccessful();

        $this->assertEqualsWithDelta(200, hexdec(substr($this->granat()['hex'], 1, 2)), 4);
        $this->assertEqualsWithDelta(50, hexdec(substr($this->granat()['hex'], 5, 2)), 4);
    }

    public function test_it_skips_colours_whose_photo_file_is_missing(): void
    {
        $this->storeCoverPhoto([200, 100, 50]);
        Storage::disk('public')->deleteDirectory('thesis-cover-photos');

        $this->artisan('covers:rebuild-textures')
            ->expectsOutputToContain('granat: the photo file is missing.')
            ->expectsOutputToContain('Rebuilt 0 texture(s).')
            ->assertSuccessful();
    }

    /**
     * Adds a "granat" cover colour with a stored photo of the given colour.
     *
     * @param  array{int, int, int}  $rgb
     * @return string path of the photo on the public disk
     */
    private function storeCoverPhoto(array $rgb): string
    {
        Storage::fake('public');
        $this->seed(ProductSeeder::class);

        $image = imagecreatetruecolor(400, 500);
        imagefill($image, 0, 0, ($rgb[0] << 16) | ($rgb[1] << 8) | $rgb[2]);
        ob_start();
        imagejpeg($image, null, 95);
        Storage::disk('public')->put('thesis-cover-photos/granat.jpg', (string) ob_get_clean());

        $product = Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail();
        $configuration = $product->configuration;
        $configuration['printing']['cover_colors'] = ['granat' => ['label' => 'Granat', 'hex' => '#063A60', 'photo' => 'thesis-cover-photos/granat.jpg']];
        $product->update(['configuration' => $configuration]);

        return 'thesis-cover-photos/granat.jpg';
    }

    /** @return array<string, mixed> */
    private function granat(): array
    {
        return Product::query()->where('slug', 'praca-dyplomowa')->firstOrFail()->configuration['printing']['cover_colors']['granat'];
    }
}
