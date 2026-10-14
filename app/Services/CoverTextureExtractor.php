<?php

namespace App\Services;

use GdImage;
use InvalidArgumentException;
use RuntimeException;

/**
 * Turns a photo of a blank cover into what the live preview paints the cover with: the
 * average colour of the cloth and a small seamless tile of its texture in that colour.
 *
 * The tile is taken from the middle of the cover (config/cover_preview.php, "sample"),
 * flattened so the light and shadow of the photo do not show, and cross-faded with itself
 * so it repeats without visible seams.
 */
class CoverTextureExtractor
{
    /** Path on the public disk of the tile that belongs to the given cover photo. */
    public static function texturePath(string $photoPath): string
    {
        return 'thesis-cover-textures/'.pathinfo($photoPath, PATHINFO_FILENAME).'.jpg';
    }

    /**
     * @param  string|null  $hex  paint the tile in this colour instead of the one measured in the photo
     * @return array{hex: string, texture: string} the colour used ("#RRGGBB") and the tile as JPEG bytes
     *
     * @throws RuntimeException when the bytes are not an image GD can read
     */
    public function extract(string $imageBytes, ?string $hex = null): array
    {
        if ($hex !== null && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) {
            throw new InvalidArgumentException('Colour must be written as #RRGGBB.');
        }

        $size = max(64, (int) config('cover_preview.texture.tile'));
        $contrast = (float) config('cover_preview.texture.contrast');

        $sample = $this->sampleSquare($imageBytes, $size);
        $detail = $this->detail($sample, $size, $mean);
        $color = $hex !== null
            ? [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))]
            : array_map(fn (float $channel): int => (int) round($channel), $mean);

        return [
            'hex' => sprintf('#%02X%02X%02X', ...$color),
            'texture' => $this->encode($this->seamless($detail, $size, $color, $contrast)),
        ];
    }

    /** The configured part of the photo, as a square of $size pixels. */
    private function sampleSquare(string $imageBytes, int $size): GdImage
    {
        $dimensions = @getimagesizefromstring($imageBytes);

        if ($dimensions === false) {
            throw new RuntimeException('The file is not an image.');
        }

        [$width, $height] = $dimensions;
        $this->allowMemoryFor($width * $height);
        $photo = @imagecreatefromstring($imageBytes);

        if ($photo === false) {
            throw new RuntimeException('The image could not be decoded.');
        }

        $region = (array) config('cover_preview.sample');
        $left = (int) round($width * (float) ($region['left'] ?? 0) / 100);
        $top = (int) round($height * (float) ($region['top'] ?? 0) / 100);
        $regionWidth = max(1, (int) round($width * (float) ($region['width'] ?? 100) / 100));
        $regionHeight = max(1, (int) round($height * (float) ($region['height'] ?? 100) / 100));
        $side = min($regionWidth, $regionHeight, $width - $left, $height - $top);

        if ($side < 1) {
            throw new RuntimeException('The sample region lies outside the photo.');
        }

        $left += intdiv($regionWidth - $side, 2);
        $top += intdiv($regionHeight - $side, 2);

        $sample = imagecreatetruecolor($size, $size);
        imagecopyresampled($sample, $photo, 0, 0, $left, $top, $size, $size, $side, $side);

        return $sample;
    }

    /**
     * The sample minus its own large-scale light and shade: per channel, how far each pixel
     * is from the blurred picture. The blur is the sample squeezed to a few dozen pixels and
     * stretched back, which keeps everything bigger than a few weave threads out of the detail.
     *
     * @param  array<int, float>|null  $mean  receives the average R, G and B of the sample
     * @return array<int, list<float>> per channel, row by row
     */
    private function detail(GdImage $sample, int $size, ?array &$mean): array
    {
        $blurSize = max(4, intdiv($size, 12));
        $small = imagecreatetruecolor($blurSize, $blurSize);
        imagecopyresampled($small, $sample, 0, 0, 0, 0, $blurSize, $blurSize, $size, $size);
        $blurred = imagescale($small, $size, $size, IMG_BICUBIC);

        $detail = [[], [], []];
        $sums = [0, 0, 0];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $pixel = imagecolorat($sample, $x, $y);
                $base = imagecolorat($blurred, $x, $y);

                foreach ([16, 8, 0] as $channel => $shift) {
                    $value = ($pixel >> $shift) & 255;
                    $sums[$channel] += $value;
                    $detail[$channel][] = (float) ($value - (($base >> $shift) & 255));
                }
            }
        }

        $mean = array_map(fn (int $sum): float => $sum / ($size * $size), $sums);

        return $detail;
    }

    /**
     * Paints the detail onto a flat colour, cross-fading each pixel with the one half a tile
     * away. That makes the opposite edges continue into each other, so the tile repeats without
     * a seam. The fade keeps the texture's strength (it adds the two as independent noise).
     *
     * @param  array<int, list<float>>  $detail
     * @param  array<int, int>  $color
     */
    private function seamless(array $detail, int $size, array $color, float $contrast): GdImage
    {
        $tile = imagecreatetruecolor($size, $size);
        $half = intdiv($size, 2);
        $weights = [];

        for ($i = 0; $i < $size; $i++) {
            $weights[$i] = 0.5 - 0.5 * cos(2 * M_PI * ($i + 0.5) / $size);
        }

        for ($y = 0; $y < $size; $y++) {
            $shiftedRow = (($y + $half) % $size) * $size;

            for ($x = 0; $x < $size; $x++) {
                $weight = $weights[$x] * $weights[$y];
                $scale = 1 / sqrt($weight * $weight + (1 - $weight) * (1 - $weight));
                $index = $y * $size + $x;
                $shifted = $shiftedRow + (($x + $half) % $size);
                $pixel = 0;

                foreach ([0, 1, 2] as $channel) {
                    $faded = ($weight * $detail[$channel][$index] + (1 - $weight) * $detail[$channel][$shifted]) * $scale;
                    $value = (int) round($color[$channel] + $contrast * $faded);
                    $pixel = ($pixel << 8) | max(0, min(255, $value));
                }

                imagesetpixel($tile, $x, $y, $pixel);
            }
        }

        return $tile;
    }

    private function encode(GdImage $tile): string
    {
        ob_start();
        imagejpeg($tile, null, 85);

        return (string) ob_get_clean();
    }

    /** Decoding a phone photo takes about four bytes per pixel, more than a default 128M limit allows for the biggest ones. */
    private function allowMemoryFor(int $pixels): void
    {
        $limit = ini_parse_quantity((string) ini_get('memory_limit'));
        $needed = memory_get_usage() + $pixels * 6;

        if ($limit > 0 && $needed > $limit) {
            ini_set('memory_limit', (string) ($needed + 64 * 1024 * 1024));
        }
    }
}
