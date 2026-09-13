<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2bQuoteRequest;
use App\Http\Requests\B2bUploadRequest;
use App\Mail\B2bQuoteRequestConfirmation;
use App\Mail\B2bQuoteRequestReceived;
use App\Models\Client;
use App\Models\QuoteRequest;
use App\Services\AdminNotificationService;
use App\Services\B2bQuoteRequestUploadService;
use App\Services\BusinessConfiguratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class B2bQuoteRequestController extends Controller
{
    public function upload(B2bUploadRequest $request, B2bQuoteRequestUploadService $uploads): JsonResponse
    {
        $result = $uploads->store($request->file('file'));
        $file = $result['file'];

        return response()->json([
            'upload_token' => $result['token'],
            'file' => [
                'name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
            ],
        ], 201);
    }

    public function store(B2bQuoteRequest $request, B2bQuoteRequestUploadService $uploads, AdminNotificationService $notifications, BusinessConfiguratorService $configurator): JsonResponse
    {
        $data = $request->validated();
        $idempotencyKey = $request->header('Idempotency-Key');

        if (is_string($idempotencyKey) && strlen($idempotencyKey) > 100) {
            return response()->json(['message' => 'Idempotency-Key jest zbyt długi.'], 422);
        }

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $existing = QuoteRequest::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $this->response($existing, false);
            }
        }

        $quoteRequest = DB::transaction(function () use ($data, $idempotencyKey, $uploads, $configurator): QuoteRequest {
            $customer = $data['customer'];
            $productSlugs = collect($data['items'])->pluck('product_slug')->unique();
            $products = $configurator->productsForSlugs($productSlugs->all());

            if ($products->count() !== $productSlugs->count()) {
                throw ValidationException::withMessages(['items' => 'Jedna z wybranych usług nie jest już dostępna.']);
            }

            $client = Client::query()->firstOrNew(['email' => $customer['email']]);
            $client->fill([
                'name' => $customer['name'],
                'phone' => $customer['phone'] ?? null,
                'company' => $customer['company'],
                'nip' => $customer['nip'] ?? null,
                'privacy_policy_version' => config('privacy.policy_version'),
                'privacy_policy_accepted_at' => now(),
                'retention_until' => now()->addDays((int) config('privacy.client_retention_days')),
            ]);
            if (! $client->exists || ($data['marketing_consent'] ?? false)) {
                $client->marketing_consent = (bool) ($data['marketing_consent'] ?? false);
                $client->marketing_consent_at = $client->marketing_consent ? now() : null;
            }
            $client->save();

            $quoteRequest = QuoteRequest::create([
                'reference' => $this->reference(),
                'status' => 'submitted',
                'client_id' => $client->id,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'] ?? null,
                'company_name' => $customer['company'],
                'nip' => $customer['nip'] ?? null,
                'shipping_method' => $data['shipping_method'],
                'shipping_address' => $data['shipping_address'] ?? null,
                'requested_by_date' => $data['requested_by_date'] ?? null,
                'invoice_required' => (bool) ($data['invoice_required'] ?? false),
                'privacy_policy_version' => config('privacy.policy_version'),
                'privacy_policy_accepted_at' => now(),
                'notes' => $this->notes($data),
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($data['items'] as $itemData) {
                $item = $quoteRequest->items()->create([
                    'product_id' => $products[$itemData['product_slug']]->id,
                    'product_slug' => $itemData['product_slug'],
                    'product_name' => $products[$itemData['product_slug']]->name,
                    'configuration' => $configurator->snapshot($products[$itemData['product_slug']], $itemData['configuration'] ?? []),
                    'quantity' => $itemData['quantity'],
                    'help_wanted' => (bool) ($itemData['help_wanted'] ?? false),
                ]);

                if (! empty($itemData['upload_token'])) {
                    $file = $uploads->findTemporary($itemData['upload_token']);
                    $uploads->attach($file, $quoteRequest->id, $item->id);
                }
            }

            return $quoteRequest;
        });

        $quoteRequest->load(['items', 'files']);
        $notifications->quoteRequestCreated($quoteRequest);
        Mail::to(config('business.quote_email'))->queue((new B2bQuoteRequestReceived($quoteRequest))->afterCommit());
        Mail::to($quoteRequest->customer_email)->queue((new B2bQuoteRequestConfirmation($quoteRequest))->afterCommit());

        return $this->response($quoteRequest, true);
    }

    private function response(QuoteRequest $quoteRequest, bool $created): JsonResponse
    {
        return response()->json([
            'message' => $created ? 'Zapytanie o wycenę zostało wysłane.' : 'Zwrócono istniejące zapytanie o wycenę.',
            'quote_request' => [
                'reference' => $quoteRequest->reference,
                'status' => $quoteRequest->status->value,
            ],
        ], $created ? 201 : 200);
    }

    /** @param array<string, mixed> $data */
    private function notes(array $data): ?string
    {
        $notes = array_filter([
            $data['customer']['notes'] ?? null,
            isset($data['brief']['type']) ? 'Rodzaj niestandardowego zlecenia: '.$data['brief']['type'] : null,
            $data['brief']['description'] ?? null,
            isset($data['brief']['quantity']) ? 'Ilość: '.$data['brief']['quantity'] : null,
            isset($data['brief']['requested_by_date']) ? 'Termin z briefu: '.$data['brief']['requested_by_date'] : null,
        ], fn (mixed $note): bool => is_string($note) && trim($note) !== '');

        return $notes ? implode("\n\n", $notes) : null;
    }

    private function reference(): string
    {
        do {
            $reference = 'B2B-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (QuoteRequest::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
