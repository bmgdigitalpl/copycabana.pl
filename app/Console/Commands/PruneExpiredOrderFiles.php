<?php

namespace App\Console\Commands;

use App\Models\OrderFile;
use App\Models\QuoteRequestFile;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('order-files:prune-expired')]
#[Description('Remove expired temporary order files')]
class PruneExpiredOrderFiles extends Command
{
    public function handle(): int
    {
        $removed = 0;

        foreach ([OrderFile::class, QuoteRequestFile::class] as $fileModel) {
            $fileModel::query()
                ->where('status', 'temporary')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->chunkById(100, function ($files) use (&$removed): void {
                    foreach ($files as $file) {
                        Storage::disk($file->disk)->delete($file->path);
                        $file->delete();
                        $removed++;
                    }
                });
        }

        $this->info("Removed {$removed} expired order file(s).");

        return self::SUCCESS;
    }
}
