<?php

namespace App\Services;

use GdImage;
use InvalidArgumentException;
use RuntimeException;

/**
 * Turns a photo of a blank cover into what the live preview paints the cover with: the
 * average colour of the cloth, a tile cut straight out of the photo and a small picture of the
 * same cloth for the colour swatch.
 *
 * The tile is the biggest square that fits in the front of the cover (config/cover_preview.php,
 * "sample"), with the texture and light of the photo and its colour profile. Below it comes the
 * same square flipped upside down, so the tile repeats down a cover taller than it is wide
 * without a visible seam.
 *
 * The phone picks the exposure of every photo on its own, so the white paper under the cover
 * comes out grey, and a white cover with it. The eye takes that paper for white when looking at
 * the photo, so the photos of the colours listed in "white_reference" are brightened until the
 * paper is white, and the cover gets the colour it seems to have in the photo.
 *
 * Phones save photos in Display P3. The colour of the cover is converted from the profile of
 * the photo to sRGB, which is what a CSS hex colour means.
 */
class CoverTextureExtractor
{
    /** Path on the public disk of the tile that belongs to the given cover photo. */
    public static function texturePath(string $photoPath): string
    {
        return 'thesis-cover-textures/'.pathinfo($photoPath, PATHINFO_FILENAME).'.jpg';
    }

    /** Path on the public disk of the swatch picture that belongs to the given cover photo. */
    public static function swatchPath(string $photoPath): string
    {
        return 'thesis-cover-swatches/'.pathinfo($photoPath, PATHINFO_FILENAME).'.jpg';
    }

    /**
     * How far the photo of this cover colour is brightened towards the paper around the cover
     * being white: 0 = not at all, 1 = until the paper is white.
     */
    public static function whitePaperStrength(string $colorKey): float
    {
        return max(0.0, min(1.0, (float) (((array) config('cover_preview.white_reference.colors'))[$colorKey] ?? 0)));
    }

    /**
     * @param  string|null  $hex  shift the tile to this colour instead of the one measured in the photo
     * @param  float  $whitePaperStrength  see whitePaperStrength()
     * @return array{hex: string, texture: string, swatch: string} the colour used ("#RRGGBB"), the tile and the swatch picture as JPEG bytes
     *
     * @throws RuntimeException when the bytes are not an image GD can read
     */
    public function extract(string $imageBytes, ?string $hex = null, float $whitePaperStrength = 0.0): array
    {
        if ($hex !== null && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) {
            throw new InvalidArgumentException('Colour must be written as #RRGGBB.');
        }

        $size = max(64, (int) config('cover_preview.texture.tile'));
        $space = IccColorSpace::fromJpeg($imageBytes);
        $photo = $this->decode($imageBytes);
        $gains = $whitePaperStrength > 0
            ? array_map(fn (float $gain): float => $gain ** $whitePaperStrength, $this->whiteBalanceGains($photo))
            : [1.0, 1.0, 1.0];
        $square = $this->sampleSquare($photo, $size);
        $mean = array_map(fn (float $channel, float $gain): float => min(255.0, $channel * $gain), $this->averageColor($square, $size), $gains);
        $offsets = [0.0, 0.0, 0.0];

        if ($hex === null) {
            $color = $space?->toSrgb($mean) ?? array_map(fn (float $channel): int => (int) round($channel), $mean);
        } else {
            $color = [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
            $target = $space?->fromSrgb($color) ?? $color;
            $offsets = array_map(fn (float|int $wanted, float $measured): float => $wanted - $measured, $target, $mean);
        }

        if ($gains !== [1.0, 1.0, 1.0] || $offsets !== [0.0, 0.0, 0.0]) {
            $this->adjust($square, $size, $gains, $offsets);
        }

        $swatchSize = max(16, (int) config('cover_preview.texture.swatch'));
        $swatch = $this->encode(imagescale($square, $swatchSize, $swatchSize, IMG_BICUBIC));
        $texture = $this->encode($this->mirroredBelow($square, $size));

        return [
            'hex' => sprintf('#%02X%02X%02X', ...$color),
            'texture' => $space?->embedInto($texture) ?? $texture,
            'swatch' => $space?->embedInto($swatch) ?? $swatch,
        ];
    }

    private function decode(string $imageBytes): GdImage
    {
        $dimensions = @getimagesizefromstring($imageBytes);

        if ($dimensions === false) {
            throw new RuntimeException('The file is not an image.');
        }

        $this->allowMemoryFor($dimensions[0] * $dimensions[1]);
        $photo = @imagecreatefromstring($imageBytes);

        if ($photo === false) {
            throw new RuntimeException('The image could not be decoded.');
        }

        return $photo;
    }

    /**
     * Per channel, what to multiply the photo by so the paper around the cover comes out at the
     * configured white level. A background too dark or too coloured to be white paper (e.g. a
     * close-up of the cloth) leaves the photo as it is.
     *
     * @return array{float, float, float}
     */
    private function whiteBalanceGains(GdImage $photo): array
    {
        $level = (float) config('cover_preview.white_reference.level');
        $width = imagesx($photo);
        $height = imagesy($photo);
        $sums = [0, 0, 0];
        $count = 0;

        foreach ((array) config('cover_preview.white_reference.regions') as $region) {
            $left = (int) round($width * (float) ($region['left'] ?? 0) / 100);
            $top = (int) round($height * (float) ($region['top'] ?? 0) / 100);
            $right = min($width, $left + (int) round($width * (float) ($region['width'] ?? 0) / 100));
            $bottom = min($height, $top + (int) round($height * (float) ($region['height'] ?? 0) / 100));

            for ($y = $top; $y < $bottom; $y += 4) {
                for ($x = $left; $x < $right; $x += 4) {
                    $pixel = imagecolorat($photo, $x, $y);
                    $sums[0] += ($pixel >> 16) & 255;
                    $sums[1] += ($pixel >> 8) & 255;
                    $sums[2] += $pixel & 255;
                    $count++;
                }
            }
        }

        if ($level <= 0 || $count === 0) {
            return [1.0, 1.0, 1.0];
        }

        $paper = array_map(fn (int $sum): float => $sum / $count, $sums);
        $isWhitePaper = min($paper) >= 100 && max($paper) / min($paper) <= 1.15;

        return $isWhitePaper ? array_map(fn (float $channel): float => $level / $channel, $paper) : [1.0, 1.0, 1.0];
    }

    /** The biggest square in the configured part of the photo, scaled to $size pixels. */
    private function sampleSquare(GdImage $photo, int $size): GdImage
    {
        $width = imagesx($photo);
        $height = imagesy($photo);
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

    /** @return array{float, float, float} average R, G and B */
    private function averageColor(GdImage $square, int $size): array
    {
        $sums = [0, 0, 0];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $pixel = imagecolorat($square, $x, $y);
                $sums[0] += ($pixel >> 16) & 255;
                $sums[1] += ($pixel >> 8) & 255;
                $sums[2] += $pixel & 255;
            }
        }

        return array_map(fn (int $sum): float => $sum / ($size * $size), $sums);
    }

    /**
     * Multiplies every pixel by the white balance gains, then moves it by the offsets (towards a
     * requested colour), per channel. The texture and light of the photo stay.
     *
     * @param  array<int, float>  $gains
     * @param  array<int, float>  $offsets
     */
    private function adjust(GdImage $square, int $size, array $gains, array $offsets): void
    {
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $pixel = imagecolorat($square, $x, $y);
                $shifted = 0;

                foreach ([16, 8, 0] as $channel => $shift) {
                    $value = (int) round((($pixel >> $shift) & 255) * $gains[$channel] + $offsets[$channel]);
                    $shifted = ($shifted << 8) | max(0, min(255, $value));
                }

                imagesetpixel($square, $x, $y, $shifted);
            }
        }
    }

    /** The square with its upside-down copy below: a tile that repeats downwards without a seam. */
    private function mirroredBelow(GdImage $square, int $size): GdImage
    {
        $tile = imagecreatetruecolor($size, 2 * $size);
        imagecopy($tile, $square, 0, 0, 0, 0, $size, $size);
        imageflip($square, IMG_FLIP_VERTICAL);
        imagecopy($tile, $square, 0, $size, 0, 0, $size, $size);

        return $tile;
    }

    private function encode(GdImage $tile): string
    {
        ob_start();
        imagejpeg($tile, null, 90);

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
