<?php

namespace Tests\Feature;

use App\Services\CoverTextureExtractor;
use GdImage;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class CoverTextureExtractorTest extends TestCase
{
    public function test_it_measures_the_cloth_colour_ignoring_the_light_gradient_of_the_photo(): void
    {
        $result = app(CoverTextureExtractor::class)->extract($this->photoOfCloth());

        $this->assertColorNear([180, 130, 40], $this->hexToRgb($result['hex']), 8);
        $this->assertColorNear([180, 130, 40], $this->averageColor($this->decode($result['texture'])), 8);
    }

    public function test_the_tile_has_no_light_gradient_left_but_keeps_the_texture(): void
    {
        $tile = $this->decode(app(CoverTextureExtractor::class)->extract($this->photoOfCloth())['texture']);
        $size = imagesx($tile);

        $leftThird = $this->averageBrightness($tile, 0, intdiv($size, 3));
        $rightThird = $this->averageBrightness($tile, $size - intdiv($size, 3), $size);

        $this->assertSame(config('cover_preview.texture.tile'), $size);
        $this->assertLessThan(6, abs($leftThird - $rightThird), 'light gradient of the photo is still in the tile');
        $this->assertGreaterThan(1, $this->averageNeighbourDifference($tile), 'the texture is gone from the tile');
    }

    public function test_the_tile_repeats_without_a_seam(): void
    {
        $tile = $this->decode(app(CoverTextureExtractor::class)->extract($this->photoOfCloth())['texture']);
        $last = imagesx($tile) - 1;

        // The last and the first column meet when the tile repeats. Two unrelated columns of
        // this grain differ about five times as much as neighbouring ones.
        $acrossTheSeam = $this->columnDifference($tile, $last, 0);

        $this->assertLessThan(3 * $this->averageNeighbourDifference($tile), $acrossTheSeam);
    }

    public function test_it_paints_the_tile_in_the_requested_colour_instead_of_the_measured_one(): void
    {
        $result = app(CoverTextureExtractor::class)->extract($this->photoOfCloth(), '#112233');

        $this->assertSame('#112233', $result['hex']);
        $this->assertColorNear([17, 34, 51], $this->averageColor($this->decode($result['texture'])), 6);
    }

    public function test_a_stronger_contrast_setting_gives_a_stronger_texture(): void
    {
        $photo = $this->photoOfCloth();
        $extractor = app(CoverTextureExtractor::class);

        config(['cover_preview.texture.contrast' => 1]);
        $plain = $this->averageNeighbourDifference($this->decode($extractor->extract($photo)['texture']));
        config(['cover_preview.texture.contrast' => 2]);
        $strong = $this->averageNeighbourDifference($this->decode($extractor->extract($photo)['texture']));

        $this->assertGreaterThan($plain * 1.5, $strong);
    }

    public function test_it_rejects_something_that_is_not_an_image(): void
    {
        $this->expectException(RuntimeException::class);

        app(CoverTextureExtractor::class)->extract('this is not a photo');
    }

    public function test_it_rejects_a_malformed_colour(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CoverTextureExtractor::class)->extract($this->photoOfCloth(), 'red');
    }

    public function test_the_texture_path_follows_the_photo_name(): void
    {
        $this->assertSame('thesis-cover-textures/abc123.jpg', CoverTextureExtractor::texturePath('thesis-cover-photos/abc123.png'));
    }

    /**
     * A 600x800 photo: a grey table with a cloth cover (180, 130, 40) on it. The cover is lit
     * from the left, so it is about 25% brighter on the left than on the right, and carries a
     * grain of random specks about eight pixels across.
     */
    private function photoOfCloth(): string
    {
        mt_srand(42);
        $specks = imagecreatetruecolor(75, 100);

        for ($y = 0; $y < 100; $y++) {
            for ($x = 0; $x < 75; $x++) {
                $level = mt_rand(0, 255);
                imagesetpixel($specks, $x, $y, ($level << 16) | ($level << 8) | $level);
            }
        }

        $grain = imagescale($specks, 600, 800, IMG_BICUBIC);
        $photo = imagecreatetruecolor(600, 800);
        imagefill($photo, 0, 0, 0xC3C1C4);

        for ($y = 100; $y < 700; $y++) {
            for ($x = 100; $x < 500; $x++) {
                $light = 1.25 - 0.5 * ($x - 100) / 400;
                $weave = ((imagecolorat($grain, $x, $y) & 255) - 128) / 6;
                $pixel = 0;

                foreach ([180, 130, 40] as $channel) {
                    $pixel = ($pixel << 8) | max(0, min(255, (int) round($channel * $light + $weave)));
                }

                imagesetpixel($photo, $x, $y, $pixel);
            }
        }

        ob_start();
        imagejpeg($photo, null, 95);

        return (string) ob_get_clean();
    }

    private function decode(string $jpeg): GdImage
    {
        return imagecreatefromstring($jpeg);
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
                $pixel = imagecolorat($image, $x, $y);
                $sums[0] += ($pixel >> 16) & 255;
                $sums[1] += ($pixel >> 8) & 255;
                $sums[2] += $pixel & 255;
            }
        }

        $count = imagesx($image) * imagesy($image);

        return [$sums[0] / $count, $sums[1] / $count, $sums[2] / $count];
    }

    private function averageBrightness(GdImage $image, int $fromColumn, int $toColumn): float
    {
        $sum = 0;

        for ($x = $fromColumn; $x < $toColumn; $x++) {
            for ($y = 0; $y < imagesy($image); $y++) {
                $sum += $this->brightness(imagecolorat($image, $x, $y));
            }
        }

        return $sum / (($toColumn - $fromColumn) * imagesy($image));
    }

    private function brightness(int $pixel): float
    {
        return (0.299 * (($pixel >> 16) & 255)) + (0.587 * (($pixel >> 8) & 255)) + (0.114 * ($pixel & 255));
    }

    /** Average brightness difference between two columns, row by row. */
    private function columnDifference(GdImage $image, int $first, int $second): float
    {
        $sum = 0;

        for ($y = 0; $y < imagesy($image); $y++) {
            $sum += abs($this->brightness(imagecolorat($image, $first, $y)) - $this->brightness(imagecolorat($image, $second, $y)));
        }

        return $sum / imagesy($image);
    }

    /** How much neighbouring columns differ on average: the strength of the texture. */
    private function averageNeighbourDifference(GdImage $image): float
    {
        $sum = 0;
        $last = imagesx($image) - 1;

        for ($x = 0; $x < $last; $x++) {
            $sum += $this->columnDifference($image, $x, $x + 1);
        }

        return $sum / $last;
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
