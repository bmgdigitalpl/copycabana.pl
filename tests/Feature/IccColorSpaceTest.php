<?php

namespace Tests\Feature;

use App\Services\IccColorSpace;
use GdImage;
use Tests\TestCase;

class IccColorSpaceTest extends TestCase
{
    public function test_it_converts_display_p3_to_srgb_like_colour_managed_apps(): void
    {
        $space = IccColorSpace::fromJpeg(self::jpeg(imagecreatetruecolor(8, 8), self::displayP3Profile()));

        // Reference values from LittleCMS (relative colorimetric, Display P3 -> sRGB).
        $this->assertColorNear([91, 31, 38], $space->toSrgb([84, 35, 39]), 1);
        $this->assertColorNear([0, 104, 132], $space->toSrgb([16, 102, 129]), 1);
        $this->assertColorNear([153, 152, 148], $space->toSrgb([153, 152, 148]), 1);
    }

    public function test_it_converts_an_srgb_colour_back_into_the_space(): void
    {
        $space = IccColorSpace::fromJpeg(self::jpeg(imagecreatetruecolor(8, 8), self::displayP3Profile()));

        $this->assertColorNear([84, 35, 39], $space->fromSrgb([91, 31, 38]), 1);
    }

    public function test_a_jpeg_without_a_profile_has_no_colour_space(): void
    {
        $this->assertNull(IccColorSpace::fromJpeg(self::jpeg(imagecreatetruecolor(8, 8))));
    }

    public function test_it_embeds_its_profile_into_another_jpeg_that_still_decodes(): void
    {
        $space = IccColorSpace::fromJpeg(self::jpeg(imagecreatetruecolor(8, 8), self::displayP3Profile()));
        $image = imagecreatetruecolor(16, 16);
        imagefill($image, 0, 0, 0x336699);

        $tagged = $space->embedInto(self::jpeg($image));

        $this->assertNotNull(IccColorSpace::fromJpeg($tagged));
        $this->assertEqualsWithDelta(0x33, (imagecolorat(imagecreatefromstring($tagged), 8, 8) >> 16) & 255, 3);
    }

    /** A JPEG of the image, with the ICC profile in an APP2 segment after its JFIF header when given. */
    public static function jpeg(GdImage $image, ?string $profile = null): string
    {
        ob_start();
        imagejpeg($image, null, 95);
        $jpeg = (string) ob_get_clean();

        if ($profile === null) {
            return $jpeg;
        }

        $payload = "ICC_PROFILE\0\x01\x01".$profile;
        $afterJfif = 4 + unpack('n', $jpeg, 4)[1];

        return substr($jpeg, 0, $afterJfif)."\xFF\xE2".pack('n', strlen($payload) + 2).$payload.substr($jpeg, $afterJfif);
    }

    /** A minimal Display P3 matrix profile: the colorants of P3 (D50) and the sRGB tone curve. */
    public static function displayP3Profile(): string
    {
        $fixed = fn (float $value): string => pack('N', (int) round($value * 65536) & 0xFFFFFFFF);
        $xyz = fn (float $x, float $y, float $z): string => 'XYZ '."\0\0\0\0".$fixed($x).$fixed($y).$fixed($z);
        $curve = 'para'."\0\0\0\0".pack('n', 3)."\0\0".$fixed(2.4).$fixed(1 / 1.055).$fixed(0.055 / 1.055).$fixed(1 / 12.92).$fixed(0.04045);

        $tags = [
            'rXYZ' => $xyz(0.515102, 0.241182, -0.001049),
            'gXYZ' => $xyz(0.291965, 0.692236, 0.041882),
            'bXYZ' => $xyz(0.157153, 0.066582, 0.784378),
            'rTRC' => $curve,
            'gTRC' => $curve,
            'bTRC' => $curve,
        ];

        $table = pack('N', count($tags));
        $data = '';
        $offset = 128 + 4 + 12 * count($tags);

        foreach ($tags as $signature => $bytes) {
            $table .= $signature.pack('N', $offset + strlen($data)).pack('N', strlen($bytes));
            $data .= $bytes;
        }

        $body = $table.$data;

        return pack('N', 128 + strlen($body)).str_repeat("\0", 124).$body;
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
