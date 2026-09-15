<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Throwable;

class PdfUploadService
{
    public function __construct(private PdfColorAnalyzer $colorAnalyzer) {}

    /**
     * @return array{file: OrderFile, token: string}
     */
    public function store(UploadedFile $uploadedFile): array
    {
        $disk = Storage::disk('local');
        $path = $uploadedFile->store('order-uploads', 'local');

        if (! is_string($path)) {
            throw new RuntimeException('The uploaded PDF could not be stored.');
        }

        try {
            $absolutePath = $disk->path($path);
            $pages = count((new Parser)->parseFile($absolutePath)->getPages());

            if ($pages < 1 || $pages > (int) config('business.thesis.max_pages', 500)) {
                throw ValidationException::withMessages([
                    'file' => 'PDF musi zawierać od 1 do '.config('business.thesis.max_pages', 500).' stron.',
                ]);
            }

            $pageColors = $this->colorAnalyzer->analyze($absolutePath, $pages);
            $token = Str::random(64);
            $file = DB::transaction(fn (): OrderFile => OrderFile::create([
                'token_hash' => hash('sha256', $token),
                'disk' => 'local',
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType() ?: 'application/pdf',
                'size' => $uploadedFile->getSize() ?: 0,
                'sha256' => hash_file('sha256', $absolutePath),
                'pages' => $pages,
                'color_pages' => $pageColors['color_pages'],
                'bw_pages' => $pageColors['bw_pages'],
                'status' => 'temporary',
                'expires_at' => now()->addHours((int) config('business.thesis.upload_retention_hours', 24)),
            ]));

            return ['file' => $file, 'token' => $token];
        } catch (ValidationException $exception) {
            $disk->delete($path);

            throw $exception;
        } catch (Throwable $exception) {
            $disk->delete($path);

            throw ValidationException::withMessages([
                'file' => 'Nie udało się odczytać PDF. Wybierz niezabezpieczony, poprawny plik.',
            ], $exception);
        }
    }

    public function findTemporary(string $token): OrderFile
    {
        return $this->resolveTemporary($this->temporaryQuery($token));
    }

    public function findTemporaryForUpdate(string $token): OrderFile
    {
        return $this->resolveTemporary($this->temporaryQuery($token)->lockForUpdate());
    }

    private function temporaryQuery(string $token): Builder
    {
        return OrderFile::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'temporary')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    private function resolveTemporary(Builder $query): OrderFile
    {
        $file = $query->first();

        if (! $file) {
            throw ValidationException::withMessages([
                'upload_token' => 'Plik wygasł albo nie istnieje. Prześlij PDF ponownie.',
            ]);
        }

        return $file;
    }

    public function attach(OrderFile $file, Order $order, OrderItem $item): void
    {
        $file->forceFill([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => 'attached',
            'expires_at' => null,
        ])->save();
    }
}
