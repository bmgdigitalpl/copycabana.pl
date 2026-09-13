<?php

namespace App\Services;

use App\Models\QuoteRequestFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class B2bQuoteRequestUploadService
{
    /**
     * @return array{token: string, file: QuoteRequestFile}
     */
    public function store(UploadedFile $uploadedFile): array
    {
        $path = $uploadedFile->store('quote-uploads', 'local');
        $token = Str::random(64);

        try {
            $file = QuoteRequestFile::create([
                'token_hash' => hash('sha256', $token),
                'disk' => 'local',
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType() ?: 'application/octet-stream',
                'size' => (int) $uploadedFile->getSize(),
                'sha256' => hash_file('sha256', $uploadedFile->getRealPath()),
                'status' => 'temporary',
                'expires_at' => now()->addHours((int) config('business.thesis.upload_retention_hours', 24)),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return ['token' => $token, 'file' => $file];
    }

    public function findTemporary(string $token): QuoteRequestFile
    {
        return $this->temporaryFileQuery($token)->firstOr(function (): void {
            throw ValidationException::withMessages([
                'items' => 'Jeden z załączników jest nieaktualny. Dodaj plik ponownie.',
            ]);
        });
    }

    public function findTemporaryForUpdate(string $token): QuoteRequestFile
    {
        return $this->temporaryFileQuery($token)->lockForUpdate()->firstOr(function (): void {
            throw ValidationException::withMessages([
                'items' => 'Jeden z załączników jest nieaktualny. Dodaj plik ponownie.',
            ]);
        });
    }

    private function temporaryFileQuery(string $token): Builder
    {
        return QuoteRequestFile::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'temporary')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function attach(QuoteRequestFile $file, int $quoteRequestId, int $quoteRequestItemId): void
    {
        $file->forceFill([
            'quote_request_id' => $quoteRequestId,
            'quote_request_item_id' => $quoteRequestItemId,
            'status' => 'attached',
            'expires_at' => null,
        ])->save();
    }
}
