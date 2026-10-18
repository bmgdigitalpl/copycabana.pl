<?php

namespace App\Services;

/**
 * Puts the cover colours in the order the swatches are shown, so look-alikes sit next to each
 * other: light neutrals, then the colours around the colour wheel (beige, gold, green, blue,
 * violet, pink, red, brown), then dark neutrals. The order is worked out from the colours
 * themselves, because a MySQL JSON column does not keep the order the colours were saved in.
 */
class CoverColorSorter
{
    /** OKLCH chroma below which a colour counts as grey, white or black. */
    private const NEUTRAL_CHROMA = 0.02;

    /** OKLCH lightness that splits the neutrals into the light ones (first) and the dark ones (last). */
    private const LIGHT_NEUTRAL = 0.5;

    /** Hue (degrees) the colour wheel starts at: between brown and beige. */
    private const WHEEL_START = 45;

    /**
     * @template TColor of array{hex?: mixed}
     *
     * @param  array<string, TColor>  $colors
     * @return array<string, TColor>
     */
    public function sort(array $colors): array
    {
        uksort($colors, fn (string|int $first, string|int $second): int => [$this->sortKey($colors[$first]['hex'] ?? null), (string) $first]
            <=> [$this->sortKey($colors[$second]['hex'] ?? null), (string) $second]);

        return $colors;
    }

    /** @return array{int, float} group (light neutrals, colours, dark neutrals, no colour) and the place within it */
    private function sortKey(mixed $hex): array
    {
        if (! is_string($hex) || preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return [3, 0.0];
        }

        [$lightness, $chroma, $hue] = $this->oklch($hex);

        if ($chroma < self::NEUTRAL_CHROMA) {
            return $lightness >= self::LIGHT_NEUTRAL ? [0, -$lightness] : [2, -$lightness];
        }

        return [1, fmod($hue - self::WHEEL_START + 360, 360)];
    }

    /**
     * The colour in OKLCH, where similar-looking colours have similar numbers.
     *
     * @return array{float, float, float} lightness 0-1, chroma, hue in degrees
     */
    private function oklch(string $hex): array
    {
        $linear = array_map(function (string $channel): float {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($hex, 1), 2));

        [$l, $m, $s] = array_map(fn (array $row): float => ($row[0] * $linear[0] + $row[1] * $linear[1] + $row[2] * $linear[2]) ** (1 / 3), [
            [0.4122214708, 0.5363325363, 0.0514459929],
            [0.2119034982, 0.6806995451, 0.1073969566],
            [0.0883024619, 0.2817188376, 0.6299787005],
        ]);

        $lightness = 0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s;
        $a = 1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s;
        $b = 0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s;

        return [$lightness, hypot($a, $b), fmod(rad2deg(atan2($b, $a)) + 360, 360)];
    }
}
