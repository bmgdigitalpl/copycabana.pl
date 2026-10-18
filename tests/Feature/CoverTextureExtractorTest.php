<?php

namespace Tests\Feature;

use App\Services\CoverTextureExtractor;
use App\Services\IccColorSpace;
use GdImage;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class CoverTextureExtractorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cover_preview.sample' => ['left' => 25, 'top' => 20, 'width' => 50, 'height' => 60],
            'cover_preview.texture.tile' => 128,
        ]);
    }

    public function test_without_white_paper_around_it_takes_the_colour_of_the_photo_as_it_is(): void
    {
        $result = app(CoverTextureExtractor::class)->extract($this->photoOfCover(), whitePaperStrength: 1);

        // The square is the left-to-right fade from (100, 60, 20) to (200, 120, 40), on average its middle.
        $this->assertColorNear([150, 90, 30], $this->hexToRgb($result['hex']), 3);
        $this->assertColorNear([150, 90, 30], $this->averageColor($this->decode($result['texture'])), 3);
    }

    public function test_on_request_it_brightens_the_photo_until_the_paper_around_the_cover_is_white(): void
    {
        // An underexposed photo with a warm cast: the paper came out (200, 190, 175).
        $photo = imagecreatetruecolor(600, 800);
        imagefill($photo, 0, 0, 0xC8BEAF);
        imagefilledrectangle($photo, 150, 160, 449, 639, (120 << 16) | (95 << 8) | 70);

        $result = app(CoverTextureExtractor::class)->extract($this->jpeg($photo), whitePaperStrength: 1);
        $unchanged = app(CoverTextureExtractor::class)->extract($this->jpeg($photo));
        $halfway = app(CoverTextureExtractor::class)->extract($this->jpeg($photo), whitePaperStrength: 0.5);

        $this->assertColorNear([120, 95, 70], $this->hexToRgb($unchanged['hex']), 3);
        $this->assertColorNear([120 * sqrt(250 / 200), 95 * sqrt(250 / 190), 70 * sqrt(250 / 175)], $this->hexToRgb($halfway['hex']), 3);

        $level = config('cover_preview.white_reference.level');
        $expected = [120 * $level / 200, 95 * $level / 190, 70 * $level / 175];

        $this->assertColorNear($expected, $this->hexToRgb($result['hex']), 3);
        $this->assertColorNear($expected, $this->averageColor($this->decode($result['texture'])), 3);
    }

    public function test_the_tile_is_the_biggest_square_of_the_cover_with_its_texture_and_light(): void
    {
        $tile = $this->decode(app(CoverTextureExtractor::class)->extract($this->photoOfCover())['texture']);

        $this->assertSame([128, 256], [imagesx($tile), imagesy($tile)]);
        // The square is 300 px of the 300x480 region, so it runs from the left to the right edge
        // of the region and keeps the whole fade, without the red frame around the cover.
        $this->assertColorNear([100, 60, 20], $this->rgbAt($tile, 0, 64), 6);
        $this->assertColorNear([200, 120, 40], $this->rgbAt($tile, 127, 64), 6);
    }

    public function test_the_swatch_is_the_same_square_made_small(): void
    {
        config(['cover_preview.texture.swatch' => 32]);
        $swatch = $this->decode(app(CoverTextureExtractor::class)->extract($this->photoOfCover())['swatch']);

        $this->assertSame([32, 32], [imagesx($swatch), imagesy($swatch)]);
        $this->assertColorNear([100, 60, 20], $this->rgbAt($swatch, 0, 16), 8);
        $this->assertColorNear([200, 120, 40], $this->rgbAt($swatch, 31, 16), 8);
    }

    public function test_the_square_is_mirrored_below_so_the_tile_repeats_without_a_seam(): void
    {
        $photo = imagecreatetruecolor(400, 400);

        for ($y = 0; $y < 400; $y++) {
            imageline($photo, 0, $y, 399, $y, (int) round($y * 255 / 399) << 8);
        }

        config(['cover_preview.sample' => ['left' => 0, 'top' => 0, 'width' => 100, 'height' => 100]]);
        $tile = $this->decode(app(CoverTextureExtractor::class)->extract($this->jpeg($photo))['texture']);

        $this->assertEqualsWithDelta($this->rgbAt($tile, 64, 127)[1], $this->rgbAt($tile, 64, 128)[1], 6);
        $this->assertEqualsWithDelta($this->rgbAt($tile, 64, 0)[1], $this->rgbAt($tile, 64, 255)[1], 6);
        $this->assertGreaterThan(200, $this->rgbAt($tile, 64, 127)[1]);
    }

    public function test_it_shifts_the_tile_to_the_requested_colour_instead_of_the_measured_one(): void
    {
        $result = app(CoverTextureExtractor::class)->extract($this->photoOfCover(), '#506070');
        $tile = $this->decode($result['texture']);

        $this->assertSame('#506070', $result['hex']);
        $this->assertColorNear([80, 96, 112], $this->averageColor($tile), 3);
        // The fade of the photo stays: the right edge is still 100 brighter in red than the left.
        $this->assertEqualsWithDelta(100, $this->rgbAt($tile, 127, 64)[0] - $this->rgbAt($tile, 0, 64)[0], 8);
    }

    public function test_a_display_p3_photo_keeps_its_profile_on_the_tile_and_gets_its_colour_in_srgb(): void
    {
        $photo = imagecreatetruecolor(600, 800);
        imagefill($photo, 0, 0, (84 << 16) | (35 << 8) | 39);

        $result = app(CoverTextureExtractor::class)->extract(IccColorSpaceTest::jpeg($photo, IccColorSpaceTest::displayP3Profile()));

        // The same P3 colour that LittleCMS shows as sRGB (91, 31, 38).
        $this->assertColorNear([91, 31, 38], $this->hexToRgb($result['hex']), 2);
        $this->assertNotNull(IccColorSpace::fromJpeg($result['texture']));
        $this->assertNotNull(IccColorSpace::fromJpeg($result['swatch']));
        $this->assertColorNear([84, 35, 39], $this->averageColor($this->decode($result['texture'])), 2);
    }

    public function test_a_requested_colour_is_given_in_srgb_also_for_a_display_p3_photo(): void
    {
        $photo = imagecreatetruecolor(600, 800);
        imagefill($photo, 0, 0, 0x808080);

        $result = app(CoverTextureExtractor::class)->extract(IccColorSpaceTest::jpeg($photo, IccColorSpaceTest::displayP3Profile()), '#5B1F26');

        $this->assertSame('#5B1F26', $result['hex']);
        // The tile stays in P3, so it holds the P3 values of that sRGB colour.
        $this->assertColorNear([84, 35, 39], $this->averageColor($this->decode($result['texture'])), 2);
    }

    public function test_it_rejects_something_that_is_not_an_image(): void
    {
        $this->expectException(RuntimeException::class);

        app(CoverTextureExtractor::class)->extract('this is not a photo');
    }

    public function test_it_rejects_a_malformed_colour(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CoverTextureExtractor::class)->extract($this->photoOfCover(), 'red');
    }

    public function test_the_texture_path_follows_the_photo_name(): void
    {
        $this->assertSame('thesis-cover-textures/abc123.jpg', CoverTextureExtractor::texturePath('thesis-cover-photos/abc123.png'));
    }

    /**
     * A 600x800 photo: red all around, and in the sample region (x 150-450, y 160-640) a cover
     * that fades from (100, 60, 20) on the left to (200, 120, 40) on the right.
     */
    private function photoOfCover(): string
    {
        $photo = imagecreatetruecolor(600, 800);
        imagefill($photo, 0, 0, 0xFF0000);

        for ($x = 150; $x < 450; $x++) {
            $fade = ($x - 150) / 299;
            $color = ((int) round(100 + 100 * $fade) << 16) | ((int) round(60 + 60 * $fade) << 8) | (int) round(20 + 20 * $fade);
            imageline($photo, $x, 160, $x, 639, $color);
        }

        return $this->jpeg($photo);
    }

    private function jpeg(GdImage $image): string
    {
        ob_start();
        imagejpeg($image, null, 100);

        return (string) ob_get_clean();
    }

    private function decode(string $jpeg): GdImage
    {
        return imagecreatefromstring($jpeg);
    }

    /** @return array{int, int, int} */
    private function rgbAt(GdImage $image, int $x, int $y): array
    {
        $pixel = imagecolorat($image, $x, $y);

        return [($pixel >> 16) & 255, ($pixel >> 8) & 255, $pixel & 255];
    }

    /** @return array{int, int, int} */
    private function hexToRgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    /** @return array{float, float, float} */
    private function averageColor(GdImage $image): array
    {
        $sums = [0, 0, 0];

        for ($y = 0; $y < imagesy($image); $y++) {
            for ($x = 0; $x < imagesx($image); $x++) {
                foreach ($this->rgbAt($image, $x, $y) as $channel => $value) {
                    $sums[$channel] += $value;
                }
            }
        }

        $count = imagesx($image) * imagesy($image);

        return [$sums[0] / $count, $sums[1] / $count, $sums[2] / $count];
    }

    /**
     * @param  array<int, int|float>  $expected
     * @param  array<int, int|float>  $actual
     */
    private function assertColorNear(array $expected, array $actual, int $tolerance): void
    {
        foreach ($expected as $channel => $value) {
            $this->assertEqualsWithDelta($value, $actual[$channel], $tolerance, "colour channel {$channel}");
        }
    }
}
