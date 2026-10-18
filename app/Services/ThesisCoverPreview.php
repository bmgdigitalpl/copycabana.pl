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
     * on the cover (config/cover_preview.php) and which of the optional files exist yet.
     *
     * @param  array<string, mixed>  $printing  thesis printing settings
     * @return array{
     *     aspect: array{width: int, height: int},
     *     textureSize: float,
     *     zones: array<string, array{style: string, tint: bool}>,
     *     font: array{family: string, url: string, format: string}|null,
     *     logos: array<string, string>,
     *     titles: array<string, array{url: string, width: float, height: float}>,
     *     titleScale: float,
     *     foils: array<string, string>,
     *     textures: array<string, string>,
     *     swatches: array<string, string>,
     *     mapping: bool
     * }
     */
    public function build(array $printing, bool $mapping = false): array
    {
        return [
            'aspect' => [
                'width' => max(1, (int) config('cover_preview.cover.width')),
                'height' => max(1, (int) config('cover_preview.cover.height')),
            ],
            'textureSize' => max(1, (float) config('cover_preview.texture.size')),
            'zones' => $this->zones(),
            'font' => $this->font(),
            'logos' => $this->logos(array_keys($printing['universities'] ?? [])),
            'titles' => $this->titles(array_keys($printing['cover_titles'] ?? [])),
            'titleScale' => max(0.0, (float) config('cover_preview.title_art_scale')),
            'foils' => $this->foils(),
            'textures' => $this->coverColorFiles($printing['cover_colors'] ?? [], CoverTextureExtractor::texturePath(...)),
            'swatches' => $this->coverColorFiles($printing['cover_colors'] ?? [], CoverTextureExtractor::swatchPath(...)),
            'mapping' => $mapping,
        ];
    }

    /**
     * CSS gradients of the metallic imprint colours, keyed by imprint colour key.
     *
     * @return array<string, string>
     */
    private function foils(): array
    {
        $foils = [];

        foreach ((array) config('cover_preview.foils') as $key => $stops) {
            $stops = array_values(array_filter((array) $stops, fn (mixed $stop): bool => is_string($stop) && preg_match('/^#[0-9a-fA-F]{6}$/', $stop) === 1));

            if (count($stops) >= 2) {
                $foils[(string) $key] = 'linear-gradient(110deg, '.implode(', ', $stops).')';
            }
        }

        return $foils;
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
        return $this->artwork((string) config('cover_preview.logos_path'), $universityKeys);
    }

    /**
     * SVG pictures of the standard cover titles found in the titles directory, keyed by title
     * key. They are drawn at the size of the matrices (1 SVG unit = 1 pt on an A4 cover), so
     * every title gets the same lettering size, like the custom text; hence the dimensions.
     *
     * @param  list<string|int>  $titleKeys
     * @return array<string, array{url: string, width: float, height: float}>
     */
    private function titles(array $titleKeys): array
    {
        $directory = trim((string) config('cover_preview.titles_path'), '/');
        $titles = [];

        foreach ($titleKeys as $key) {
            $key = (string) $key;
            $file = public_path($directory.'/'.$key.'.svg');

            if (preg_match('/^[A-Za-z0-9_-]+$/', $key) !== 1 || ! is_file($file)) {
                continue;
            }

            $head = (string) file_get_contents($file, false, null, 0, 1024);

            if (preg_match('/<svg[^>]*\swidth="([0-9.]+)"[^>]*\sheight="([0-9.]+)"/', $head, $size) !== 1) {
                continue;
            }

            $titles[$key] = ['url' => $this->publicFileUrl($directory.'/'.$key.'.svg'), 'width' => (float) $size[1], 'height' => (float) $size[2]];
        }

        return $titles;
    }

    /**
     * @param  list<string|int>  $keys
     * @return array<string, string> key => URL of the picture named after it in the directory
     */
    private function artwork(string $directory, array $keys): array
    {
        $directory = trim($directory, '/');
        $logos = [];

        foreach ($keys as $key) {
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
     * Cloth tiles or swatch pictures cut from the photos of the blank covers, keyed by cover
     * color code. They are made when an admin uploads a photo at /dashboard/printing/thesis.
     * A color without them shows its plain colour.
     *
     * @param  array<string, array{photo?: string|null}>  $coverColors
     * @param  callable(string): string  $pathOfPhoto  where the file made from a photo is kept
     * @return array<string, string>
     */
    private function coverColorFiles(array $coverColors, callable $pathOfPhoto): array
    {
        $disk = Storage::disk('public');
        $files = [];

        foreach ($coverColors as $colorKey => $color) {
            $photoPath = $color['photo'] ?? null;

            if (! is_string($photoPath) || $photoPath === '') {
                continue;
            }

            $path = $pathOfPhoto($photoPath);

            if ($disk->exists($path)) {
                $files[$colorKey] = $disk->url($path).'?v='.$disk->lastModified($path);
            }
        }

        return $files;
    }

    /** URL of a file under public/, with its modification time so a replaced file is not served stale. */
    private function publicFileUrl(string $relativePath): ?string
    {
        $file = public_path($relativePath);

        return is_file($file) ? asset($relativePath).'?v='.filemtime($file) : null;
    }
}
