<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PdfColorAnalyzer
{
    /**
     * @return array{color_pages: int, bw_pages: int}
     */
    public function analyze(string $path, int $pages): array
    {
        $colorPages = 0;

        for ($page = 1; $page <= $pages; $page++) {
            if ($this->pageHasColor($path, $page)) {
                $colorPages++;
            }
        }

        return [
            'color_pages' => $colorPages,
            'bw_pages' => max(0, $pages - $colorPages),
        ];
    }

    private function pageHasColor(string $path, int $page): bool
    {
        $imagePath = tempnam(sys_get_temp_dir(), 'cc-pdf-page-');

        if (! is_string($imagePath)) {
            throw new RuntimeException('Could not create a temporary image file.');
        }

        $pngPath = $imagePath.'.png';
        File::delete($imagePath);

        try {
            $process = new Process([
                'gs',
                '-dSAFER',
                '-dBATCH',
                '-dNOPAUSE',
                '-sDEVICE=png16m',
                '-r72',
                '-dFirstPage='.$page,
                '-dLastPage='.$page,
                '-sOutputFile='.$pngPath,
                $path,
            ]);
            $process->setTimeout(15);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $this->imageHasColor($pngPath);
        } finally {
            File::delete($pngPath);
        }
    }

    private function imageHasColor(string $path): bool
    {
        $image = imagecreatefrompng($path);

        if ($image === false) {
            throw new RuntimeException('Could not read rendered PDF page.');
        }

        try {
            $width = imagesx($image);
            $height = imagesy($image);
            $stepX = max(1, (int) floor($width / 80));
            $stepY = max(1, (int) floor($height / 80));
            $samples = 0;
            $colored = 0;

            for ($y = 0; $y < $height; $y += $stepY) {
                for ($x = 0; $x < $width; $x += $stepX) {
                    $rgb = imagecolorat($image, $x, $y);
                    $red = ($rgb >> 16) & 0xFF;
                    $green = ($rgb >> 8) & 0xFF;
                    $blue = $rgb & 0xFF;
                    $delta = max($red, $green, $blue) - min($red, $green, $blue);
                    $ink = 255 - min($red, $green, $blue);

                    $samples++;

                    if ($ink > 20 && $delta > 12) {
                        $colored++;
                    }
                }
            }

            return $samples > 0 && ($colored / $samples) > 0.0025;
        } finally {
            imagedestroy($image);
        }
    }
}
