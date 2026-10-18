<?php

namespace App\Services;

/**
 * The colour space a JPEG is tagged with, read from its embedded ICC profile (phones save
 * their photos in Display P3, not sRGB). It converts colours between that space and the sRGB
 * of CSS, and puts the profile into another JPEG, so the browser shows it like the original.
 *
 * Only matrix profiles (red, green and blue colorants plus a tone curve) are understood,
 * which is what cameras and phones write.
 */
class IccColorSpace
{
    /** sRGB colorants adapted to the D50 white of ICC profiles: linear sRGB to XYZ, by columns. */
    private const SRGB_TO_XYZ = [
        [0.4360747, 0.3850649, 0.1430804],
        [0.2225045, 0.7168786, 0.0606169],
        [0.0139322, 0.0971045, 0.7141733],
    ];

    /** Bytes of an ICC profile that fit in one JPEG APP2 segment, after its 14-byte header. */
    private const SEGMENT_DATA = 65519;

    /**
     * @param  array<int, array<int, float>>  $toXyz  linear RGB of this space to XYZ (D50)
     * @param  list<array{float, float, float, float, float}>  $curves  per channel: gamma, a, b, c, d
     */
    private function __construct(
        private string $profile,
        private array $toXyz,
        private array $curves,
    ) {}

    /** The colour space of the JPEG, or null when it has no profile this class understands (then it is sRGB). */
    public static function fromJpeg(string $jpeg): ?self
    {
        $profile = self::profileBytes($jpeg);

        if ($profile === null || strlen($profile) < 132) {
            return null;
        }

        $tags = [];
        $count = unpack('N', $profile, 128)[1];

        for ($i = 0; $i < $count && 132 + 12 * ($i + 1) <= strlen($profile); $i++) {
            ['offset' => $offset, 'size' => $size] = unpack('Noffset/Nsize', $profile, 136 + 12 * $i);
            $tags[substr($profile, 132 + 12 * $i, 4)] = substr($profile, $offset, $size);
        }

        $columns = [];
        $curves = [];

        foreach (['r', 'g', 'b'] as $channel) {
            $colorant = self::xyz($tags[$channel.'XYZ'] ?? '');
            $curve = self::curve($tags[$channel.'TRC'] ?? '');

            if ($colorant === null || $curve === null) {
                return null;
            }

            $columns[] = $colorant;
            $curves[] = $curve;
        }

        $toXyz = [];

        for ($row = 0; $row < 3; $row++) {
            $toXyz[$row] = [$columns[0][$row], $columns[1][$row], $columns[2][$row]];
        }

        return new self($profile, $toXyz, $curves);
    }

    /**
     * @param  array<int, float|int>  $rgb  0-255 in this space
     * @return array{int, int, int} 0-255 in sRGB, clipped to what sRGB can show
     */
    public function toSrgb(array $rgb): array
    {
        $linear = [];

        foreach ([0, 1, 2] as $channel) {
            $linear[$channel] = $this->decode($rgb[$channel] / 255, $this->curves[$channel]);
        }

        $srgb = self::multiply(self::inverse(self::SRGB_TO_XYZ), self::multiply($this->toXyz, $linear));

        return array_map(fn (float $value): int => (int) round(255 * self::encodeSrgb($value)), $srgb);
    }

    /**
     * @param  array<int, float|int>  $rgb  0-255 in sRGB
     * @return array{float, float, float} 0-255 in this space
     */
    public function fromSrgb(array $rgb): array
    {
        $linear = array_map(fn (float|int $value): float => self::decodeSrgb($value / 255), $rgb);
        $own = self::multiply(self::inverse($this->toXyz), self::multiply(self::SRGB_TO_XYZ, $linear));
        $result = [];

        foreach ([0, 1, 2] as $channel) {
            $result[$channel] = 255 * $this->encode(max(0.0, min(1.0, $own[$channel])), $this->curves[$channel]);
        }

        return $result;
    }

    /** The JPEG with this profile embedded right after its JFIF header, replacing any it had. */
    public function embedInto(string $jpeg): string
    {
        $segments = '';
        $chunks = str_split($this->profile, self::SEGMENT_DATA);

        foreach ($chunks as $index => $chunk) {
            $payload = "ICC_PROFILE\0".chr($index + 1).chr(count($chunks)).$chunk;
            $segments .= "\xFF\xE2".pack('n', strlen($payload) + 2).$payload;
        }

        $jpeg = self::withoutProfile($jpeg);
        $position = 2;

        // Keep the JFIF APP0 segment first, as decoders expect.
        if (substr($jpeg, 2, 2) === "\xFF\xE0") {
            $position += 2 + unpack('n', $jpeg, 4)[1];
        }

        return substr($jpeg, 0, $position).$segments.substr($jpeg, $position);
    }

    /** The ICC profile split over the APP2 segments of the JPEG, joined in order. */
    private static function profileBytes(string $jpeg): ?string
    {
        $chunks = [];

        foreach (self::segments($jpeg) as [$marker, $start, $length]) {
            if ($marker === 0xE2 && substr($jpeg, $start + 4, 12) === "ICC_PROFILE\0") {
                $chunks[ord($jpeg[$start + 16])] = substr($jpeg, $start + 18, $length - 16);
            }
        }

        ksort($chunks);

        return $chunks === [] ? null : implode('', $chunks);
    }

    private static function withoutProfile(string $jpeg): string
    {
        foreach (array_reverse(self::segments($jpeg)) as [$marker, $start, $length]) {
            if ($marker === 0xE2 && substr($jpeg, $start + 4, 12) === "ICC_PROFILE\0") {
                $jpeg = substr($jpeg, 0, $start).substr($jpeg, $start + 2 + $length);
            }
        }

        return $jpeg;
    }

    /**
     * The header segments of the JPEG, up to the image data.
     *
     * @return list<array{int, int, int}> marker, offset of the segment, its length field
     */
    private static function segments(string $jpeg): array
    {
        $segments = [];
        $position = 2;

        while ($position + 4 <= strlen($jpeg) && $jpeg[$position] === "\xFF") {
            $marker = ord($jpeg[$position + 1]);

            if ($marker === 0xDA || $marker === 0xD9) {
                break;
            }

            $length = unpack('n', $jpeg, $position + 2)[1];
            $segments[] = [$marker, $position, $length];
            $position += 2 + $length;
        }

        return $segments;
    }

    /** @return array{float, float, float}|null */
    private static function xyz(string $tag): ?array
    {
        if (strlen($tag) < 20 || substr($tag, 0, 4) !== 'XYZ ') {
            return null;
        }

        return [self::fixed($tag, 8), self::fixed($tag, 12), self::fixed($tag, 16)];
    }

    /**
     * A tone curve as the parametric function Y = (aX + b)^gamma for X >= d, Y = cX below.
     *
     * @return array{float, float, float, float, float}|null gamma, a, b, c, d
     */
    private static function curve(string $tag): ?array
    {
        $type = substr($tag, 0, 4);

        if ($type === 'curv' && strlen($tag) >= 12) {
            $points = unpack('N', $tag, 8)[1];

            return match ($points) {
                0 => [1.0, 1.0, 0.0, 0.0, 0.0],
                1 => [unpack('n', $tag, 12)[1] / 256, 1.0, 0.0, 0.0, 0.0],
                default => null,
            };
        }

        if ($type === 'para' && strlen($tag) >= 16) {
            $function = unpack('n', $tag, 8)[1];
            $parameterCount = [0 => 1, 3 => 5][$function] ?? 0;
            $parameters = [];

            if ($parameterCount === 0 || strlen($tag) < 12 + 4 * $parameterCount) {
                return null;
            }

            for ($i = 0; $i < $parameterCount; $i++) {
                $parameters[] = self::fixed($tag, 12 + 4 * $i);
            }

            return match ($function) {
                0 => [$parameters[0], 1.0, 0.0, 0.0, 0.0],
                3 => [$parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]],
                default => null,
            };
        }

        return null;
    }

    /** @param  array{float, float, float, float, float}  $curve */
    private function decode(float $value, array $curve): float
    {
        [$gamma, $a, $b, $c, $d] = $curve;

        return $value >= $d ? max(0.0, $a * $value + $b) ** $gamma : $c * $value;
    }

    /** @param  array{float, float, float, float, float}  $curve */
    private function encode(float $linear, array $curve): float
    {
        [$gamma, $a, $b, $c, $d] = $curve;

        if ($linear >= max(0.0, $a * $d + $b) ** $gamma || $c == 0.0) {
            return max(0.0, min(1.0, ($linear ** (1 / $gamma) - $b) / $a));
        }

        return $linear / $c;
    }

    private static function decodeSrgb(float $value): float
    {
        return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
    }

    private static function encodeSrgb(float $linear): float
    {
        $linear = max(0.0, min(1.0, $linear));

        return $linear <= 0.0031308 ? 12.92 * $linear : 1.055 * $linear ** (1 / 2.4) - 0.055;
    }

    private static function fixed(string $bytes, int $offset): float
    {
        $value = unpack('N', $bytes, $offset)[1];

        return ($value >= 0x80000000 ? $value - 0x100000000 : $value) / 65536;
    }

    /**
     * @param  array<int, array<int, float>>  $matrix
     * @param  array<int, float>  $vector
     * @return array{float, float, float}
     */
    private static function multiply(array $matrix, array $vector): array
    {
        return array_map(
            fn (array $row): float => $row[0] * $vector[0] + $row[1] * $vector[1] + $row[2] * $vector[2],
            $matrix,
        );
    }

    /**
     * @param  array<int, array<int, float>>  $m
     * @return array<int, array<int, float>>
     */
    private static function inverse(array $m): array
    {
        $cofactors = [
            [$m[1][1] * $m[2][2] - $m[1][2] * $m[2][1], $m[0][2] * $m[2][1] - $m[0][1] * $m[2][2], $m[0][1] * $m[1][2] - $m[0][2] * $m[1][1]],
            [$m[1][2] * $m[2][0] - $m[1][0] * $m[2][2], $m[0][0] * $m[2][2] - $m[0][2] * $m[2][0], $m[0][2] * $m[1][0] - $m[0][0] * $m[1][2]],
            [$m[1][0] * $m[2][1] - $m[1][1] * $m[2][0], $m[0][1] * $m[2][0] - $m[0][0] * $m[2][1], $m[0][0] * $m[1][1] - $m[0][1] * $m[1][0]],
        ];
        $determinant = $m[0][0] * $cofactors[0][0] + $m[0][1] * $cofactors[1][0] + $m[0][2] * $cofactors[2][0];

        return array_map(fn (array $row): array => array_map(fn (float $value): float => $value / $determinant, $row), $cofactors);
    }
}
