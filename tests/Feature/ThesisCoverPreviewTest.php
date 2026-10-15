<?php

namespace Tests\Feature;

use App\Services\ThesisCoverPreview;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThesisCoverPreviewTest extends TestCase
{
    public function test_it_lists_only_the_logos_font_and_textures_that_exist(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('thesis-cover-textures/granat.jpg', 'tile');
        $this->usePublicDirectoryWith(['images/uczelnie/us.svg', 'images/uczelnie/ue.png', 'fonts/okladka.ttf']);
        $printing = [
            'universities' => ['us' => 'Uniwersytet Śląski', 'ue' => 'Uniwersytet Ekonomiczny', 'polsl' => 'Politechnika Śląska'],
            'cover_colors' => [
                'granat' => ['label' => 'Granat', 'hex' => '#063A60', 'photo' => 'thesis-cover-photos/granat.jpg'],
                'zielony' => ['label' => 'Zielony', 'hex' => '#7FBF45', 'photo' => 'thesis-cover-photos/zielony.jpg'],
                'magenta' => ['label' => 'Magenta', 'hex' => '#D51A70'],
            ],
        ];

        $preview = app(ThesisCoverPreview::class)->build($printing);

        $this->assertSame(['us', 'ue'], array_keys($preview['logos']));
        $this->assertStringContainsString('images/uczelnie/us.svg', $preview['logos']['us']);
        $this->assertStringContainsString('images/uczelnie/ue.png', $preview['logos']['ue']);
        $this->assertSame('truetype', $preview['font']['format']);
        $this->assertStringContainsString('fonts/okladka.ttf', $preview['font']['url']);
        $this->assertSame(['granat'], array_keys($preview['textures']));
        $this->assertStringContainsString('thesis-cover-textures/granat.jpg', $preview['textures']['granat']);
    }

    public function test_it_lists_only_the_title_artwork_that_exists(): void
    {
        Storage::fake('public');
        $this->usePublicDirectoryWith(['images/uczelnie/master_thesis.svg']);
        File::ensureDirectoryExists(public_path('images/tytuly'));
        File::put(public_path('images/tytuly/master_thesis.svg'), '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="264.5" height="96.3" viewBox="0 0 264.5 96.3"></svg>');
        File::put(public_path('images/tytuly/magisterska.svg'), 'not an svg');

        $preview = app(ThesisCoverPreview::class)->build([
            'cover_titles' => ['master_thesis' => 'Master Thesis', 'magisterska' => 'Praca Magisterska', 'doktorska' => 'Praca Doktorska', '../secret' => 'Poza katalogiem'],
        ]);

        $this->assertSame(['master_thesis'], array_keys($preview['titles']));
        $this->assertStringContainsString('images/tytuly/master_thesis.svg', $preview['titles']['master_thesis']['url']);
        $this->assertSame(264.5, $preview['titles']['master_thesis']['width']);
        $this->assertSame(96.3, $preview['titles']['master_thesis']['height']);
        $this->assertGreaterThan(0, $preview['titleScale']);
    }

    public function test_metallic_imprint_colours_become_gradients(): void
    {
        config(['cover_preview.foils' => [
            'gold' => ['#8a6712', '#fff1ab', 'not a colour', '#7a590d'],
            'silver' => ['#999999'],
        ]]);

        $foils = app(ThesisCoverPreview::class)->build([])['foils'];

        $this->assertSame(['gold' => 'linear-gradient(110deg, #8a6712, #fff1ab, #7a590d)'], $foils);
    }

    public function test_it_falls_back_to_plain_colours_when_no_files_have_been_added(): void
    {
        Storage::fake('public');
        $this->usePublicDirectoryWith([]);

        $preview = app(ThesisCoverPreview::class)->build([
            'universities' => ['us' => 'Uniwersytet Śląski'],
            'cover_colors' => ['granat' => ['label' => 'Granat', 'hex' => '#063A60']],
        ]);

        $this->assertSame([], $preview['logos']);
        $this->assertNull($preview['font']);
        $this->assertSame([], $preview['textures']);
    }

    public function test_it_never_looks_for_logos_outside_the_logos_directory(): void
    {
        $this->usePublicDirectoryWith(['secret.svg']);

        $preview = app(ThesisCoverPreview::class)->build(['universities' => ['../secret' => 'Poza katalogiem']]);

        $this->assertSame([], $preview['logos']);
    }

    public function test_zone_positions_become_percentage_styles(): void
    {
        config(['cover_preview.zones' => [
            'logo' => ['left' => 30, 'top' => 12.5, 'width' => 40, 'height' => 22, 'tint' => true],
            'title' => [
                'left' => 8, 'top' => 44, 'width' => 84, 'height' => 14,
                'font_size' => 6.2, 'line_height' => 1.2, 'letter_spacing' => 0.04,
                'align' => 'right', 'valign' => 'end', 'uppercase' => true,
            ],
        ]]);

        $zones = app(ThesisCoverPreview::class)->build([])['zones'];

        $this->assertSame('left:30%;top:12.5%;width:40%;height:22%;', $zones['logo']['style']);
        $this->assertTrue($zones['logo']['tint']);
        $this->assertSame(
            'left:8%;top:44%;width:84%;height:14%;font-size:6.2cqw;line-height:1.2;letter-spacing:0.04em;text-align:right;align-items:safe flex-end;text-transform:uppercase;',
            $zones['title']['style'],
        );
        $this->assertFalse($zones['title']['tint']);
    }

    public function test_configured_zones_stay_inside_the_cover(): void
    {
        $zones = config('cover_preview.zones');

        $this->assertEqualsCanonicalizing(['logo', 'title', 'custom'], array_keys($zones));

        foreach ($zones as $name => $zone) {
            $this->assertGreaterThanOrEqual(0, $zone['left'], "{$name}: left");
            $this->assertGreaterThanOrEqual(0, $zone['top'], "{$name}: top");
            $this->assertGreaterThan(0, $zone['width'], "{$name}: width");
            $this->assertGreaterThan(0, $zone['height'], "{$name}: height");
            $this->assertLessThanOrEqual(100, $zone['left'] + $zone['width'], "{$name}: left + width");
            $this->assertLessThanOrEqual(100, $zone['top'] + $zone['height'], "{$name}: top + height");
        }
    }

    /**
     * Points public_path() at an empty temporary directory holding only the given files, so the
     * test neither depends on nor touches the real logos and fonts in public/.
     *
     * @param  list<string>  $files  paths relative to the public directory
     */
    private function usePublicDirectoryWith(array $files): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cover-preview-'.uniqid();

        foreach ($files as $file) {
            File::ensureDirectoryExists(dirname($directory.DIRECTORY_SEPARATOR.$file));
            File::put($directory.DIRECTORY_SEPARATOR.$file, 'x');
        }

        File::ensureDirectoryExists($directory);
        $this->app->usePublicPath($directory);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($directory));
    }
}
