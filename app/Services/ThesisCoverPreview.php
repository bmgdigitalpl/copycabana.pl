<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ThesisCoverPreview
{
    /** @var array<string, string> font file extension => CSS format() name */
    private const FONT_FORMATS = ['woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype'];

    /** @var list<string> */
    private const LOGO_EXTENSIONS = ['svg', 'png', 'webp'];

    /**
     * Everything the live cover preview on /prace-dyplomowe needs: where each element goes
     * on the photo (config/cover_preview.php) and which of the optional files exist yet.
     *
     * @param  array<string, mixed>  $printing  thesis printing settings
     * @return array{
     *     aspect: array{width: int, height: int},
     *     zones: array<string, array{style: string, tint: bool}>,
     *     font: array{family: string, url: string, format: string}|null,
     *     logos: array<string, string>,
     *     photos: array<string, string>,
     *     mapping: bool
     * }
     */
    public function build(array $printing, bool $mapping = false): array
    {
        return [
            'aspect' => [
                'width' => max(1, (int) config('cover_preview.image.width')),
                'height' => max(1, (int) config('cover_preview.image.height')),
            ],
            'zones' => $this->zones(),
            'font' => $this->font(),
            'logos' => $this->logos(array_keys($printing['universities'] ?? [])),
            'photos' => $this->photos($printing['cover_colors'] ?? []),
            'mapping' => $mapping,
        ];
    }

    /** @return array<string, array{style: string, tint: bool}> */
    private function zones(): array
    {
        $zones = [];

        foreach ((array) config('cover_preview.zones') as $name => $zone) {
            $zones[$name] = [
                'style' => $this->zoneStyle((array) $zone),
                'tint' => (bool) ($zone['tint'] ?? false),
            ];
        }

        return $zones;
    }

    /** @param  array<string, mixed>  $zone */
    private function zoneStyle(array $zone): string
    {
        $style = sprintf(
            'left:%s%%;top:%s%%;width:%s%%;height:%s%%;',
            $this->number($zone['left'] ?? 0),
            $this->number($zone['top'] ?? 0),
            $this->number($zone['width'] ?? 0),
            $this->number($zone['height'] ?? 0),
        );

        if (! isset($zone['font_size'])) {
            return $style;
        }

        $align = in_array($zone['align'] ?? null, ['left', 'center', 'right'], true) ? $zone['align'] : 'center';
        // "safe" makes a too-long text start at the top of the zone and get cut at the bottom,
        // instead of being cut equally on both sides.
        $valign = ['start' => 'flex-start', 'center' => 'safe center', 'end' => 'safe flex-end'][$zone['valign'] ?? 'center'] ?? 'safe center';

        return $style.sprintf(
            'font-size:%scqw;line-height:%s;letter-spacing:%sem;text-align:%s;align-items:%s;%s',
            $this->number($zone['font_size']),
            $this->number($zone['line_height'] ?? 1.2),
            $this->number($zone['letter_spacing'] ?? 0),
            $align,
            $valign,
            ($zone['uppercase'] ?? false) ? 'text-transform:uppercase;' : '',
        );
    }

    private function number(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.') ?: '0';
    }

    /** @return array{family: string, url: string, format: string}|null */
    private function font(): ?array
    {
        $path = (string) config('cover_preview.font.path');
        $family = preg_replace('/[^A-Za-z0-9 _-]/', '', (string) config('cover_preview.font.family')) ?: 'CoverImprint';

        foreach (self::FONT_FORMATS as $extension => $format) {
            $url = $this->publicFileUrl($path.'.'.$extension);

            if ($url !== null) {
                return ['family' => $family, 'url' => $url, 'format' => $format];
            }
        }

        return null;
    }

    /**
     * University logos found in the logos directory, keyed by university key.
     *
     * @param  list<string|int>  $universityKeys
     * @return array<string, string>
     */
    private function logos(array $universityKeys): array
    {
        $directory = trim((string) config('cover_preview.logos_path'), '/');
        $logos = [];

        foreach ($universityKeys as $key) {
            $key = (string) $key;

            if (preg_match('/^[A-Za-z0-9_-]+$/', $key) !== 1) {
                continue;
            }

            foreach (self::LOGO_EXTENSIONS as $extension) {
                $url = $this->publicFileUrl($directory.'/'.$key.'.'.$extension);

                if ($url !== null) {
                    $logos[$key] = $url;

                    break;
                }
            }
        }

        return $logos;
    }

    /**
     * Real photos of the blank covers, keyed by cover color code, as uploaded by an admin at
     * /dashboard/printing/thesis. A color without a photo shows the plain color placeholder.
     *
     * @param  array<string, array{photo?: string|null}>  $coverColors
     * @return array<string, string>
     */
    private function photos(array $coverColors): array
    {
        $photos = [];

        foreach ($coverColors as $colorKey => $color) {
            $photoPath = $color['photo'] ?? null;

            if (is_string($photoPath) && $photoPath !== '') {
                $photos[$colorKey] = Storage::disk('public')->url($photoPath);
            }
        }

        return $photos;
    }

    /** URL of a file under public/, with its modification time so a replaced file is not served stale. */
    private function publicFileUrl(string $relativePath): ?string
    {
        $file = public_path($relativePath);

        return is_file($file) ? asset($relativePath).'?v='.filemtime($file) : null;
    }
}
