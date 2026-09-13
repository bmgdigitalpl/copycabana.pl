<?php

namespace Tests\Feature;

use App\Enums\QuoteRequestStatus;
use App\Mail\B2bQuoteOfferSent;
use App\Mail\B2bQuoteRequestConfirmation;
use App\Mail\B2bQuoteRequestReceived;
use App\Mail\B2bQuoteRequestStatusChanged;
use App\Mail\OrderReceivedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\QuoteOffer;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class B2bQuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_b2b_quote_request_with_a_private_file(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(ProductSeeder::class);
        $upload = $this->post('/api/v1/b2b/uploads', [
            'file' => UploadedFile::fake()->image('logo.png', 100, 100),
        ]);

        $response = $this->withHeader('Idempotency-Key', 'b2b-request-key')
            ->postJson('/api/v1/b2b/quote-requests', [
                'customer' => [
                    'name' => 'Jan Kowalski',
                    'email' => 'jan@firma.pl',
                    'company' => 'Kowalski Sp. z o.o.',
                    'phone' => '502000000',
                ],
                'items' => [[
                    'product_slug' => 'wizytowki',
                    'configuration' => $this->businessConfiguration('wizytowki'),
                    'quantity' => 1,
                    'upload_token' => $upload->json('upload_token'),
                    'help_wanted' => false,
                ]],
                'shipping_method' => 'pickup',
                'requested_by_date' => now()->addDays(7)->toDateString(),
                'privacy_policy_accepted' => true,
            ]);

        $response->assertCreated()->assertJsonPath('quote_request.status', 'submitted');
        $quoteRequest = QuoteRequest::query()->with(['items', 'files'])->firstOrFail();

        $this->assertSame('B2B-', substr($quoteRequest->reference, 0, 4));
        $this->assertSame('Kowalski Sp. z o.o.', $quoteRequest->company_name);
        $this->assertSame(500, $quoteRequest->items->first()->quantity);
        $this->assertSame('350 g biały', $quoteRequest->items->first()->configuration['paper']['display']);
        $this->assertSame('attached', $quoteRequest->files->first()->status);
        Storage::disk('local')->assertExists($quoteRequest->files->first()->path);
        Mail::assertQueued(B2bQuoteRequestReceived::class, 1);
        Mail::assertQueued(B2bQuoteRequestConfirmation::class, 1);
    }

    public function test_repeated_b2b_submission_with_the_same_idempotency_key_does_not_duplicate_the_request(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(ProductSeeder::class);
        $payload = [
            'customer' => ['name' => 'Anna Nowak', 'email' => 'anna@firma.pl', 'company' => 'Nowak Design'],
            'items' => [['product_slug' => 'plakaty', 'configuration' => $this->businessConfiguration('plakaty'), 'quantity' => 10]],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ];

        $first = $this->withHeader('Idempotency-Key', 'b2b-repeat-key')->postJson('/api/v1/b2b/quote-requests', $payload);
        $second = $this->withHeader('Idempotency-Key', 'b2b-repeat-key')->postJson('/api/v1/b2b/quote-requests', $payload);

        $first->assertCreated();
        $second->assertOk();
        $this->assertSame($first->json('quote_request.reference'), $second->json('quote_request.reference'));
        $this->assertDatabaseCount('quote_requests', 1);
        Mail::assertQueued(B2bQuoteRequestReceived::class, 1);
        Mail::assertQueued(B2bQuoteRequestConfirmation::class, 1);
    }

    public function test_b2b_request_rejects_missing_privacy_consent(): void
    {
        $this->postJson('/api/v1/b2b/quote-requests', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@firma.pl', 'company' => 'Firma'],
            'items' => [['product_slug' => 'wizytowki', 'configuration' => $this->businessConfiguration('wizytowki'), 'quantity' => 100]],
            'shipping_method' => 'pickup',
        ])->assertUnprocessable()->assertJsonValidationErrors('privacy_policy_accepted');
    }

    public function test_b2b_upload_rejects_an_unsupported_file(): void
    {
        Storage::fake('local');

        $this->post('/api/v1/b2b/uploads', [
            'file' => UploadedFile::fake()->create('brief.exe', 10, 'application/octet-stream'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_admin_can_update_a_b2b_request_and_download_its_file(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(ProductSeeder::class);
        $upload = $this->post('/api/v1/b2b/uploads', ['file' => UploadedFile::fake()->image('brief.png')]);
        $this->postJson('/api/v1/b2b/quote-requests', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@firma.pl', 'company' => 'Firma'],
            'items' => [['product_slug' => 'wizytowki', 'configuration' => $this->businessConfiguration('wizytowki'), 'quantity' => 100, 'upload_token' => $upload->json('upload_token')]],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ])->assertCreated();
        $quoteRequest = QuoteRequest::query()->with('files')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.quote-requests.update', $quoteRequest), [
            'status' => QuoteRequestStatus::Quoted->value,
            'note' => 'Wycena gotowa do wysłania.',
        ])->assertRedirect();

        $this->assertDatabaseHas('quote_requests', [
            'id' => $quoteRequest->id,
            'status' => QuoteRequestStatus::Quoted->value,
            'admin_notes' => 'Wycena gotowa do wysłania.',
        ]);
        $this->actingAs($admin)
            ->get(route('admin.quote-requests.files.download', [$quoteRequest, $quoteRequest->files->first()]))
            ->assertDownload('brief.png');
        Mail::assertQueued(B2bQuoteRequestStatusChanged::class, 1);
    }

    public function test_admin_can_send_a_versioned_offer_to_a_b2b_customer(): void
    {
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.quote-offers.store', $quoteRequest), [
            'subtotal' => 100,
            'shipping_total' => 12,
            'total' => 112,
            'currency' => 'PLN',
            'valid_until' => today()->addDays(14)->toDateString(),
            'notes' => 'Oferta obejmuje przygotowanie plików do druku.',
        ]);

        $response->assertRedirect(route('admin.quote-requests.show', $quoteRequest));
        $offer = QuoteOffer::query()->firstOrFail();
        $this->assertSame(1, $offer->version);
        $this->assertSame('sent', $offer->status->value);
        $this->assertDatabaseHas('quote_requests', ['id' => $quoteRequest->id, 'status' => 'quoted']);
        Mail::assertQueued(B2bQuoteOfferSent::class, 1);
    }

    public function test_customer_can_accept_a_valid_offer_and_start_blik_payment_once(): void
    {
        Http::fake([
            '*/pl/standard/user/oauth/authorize' => Http::response(['access_token' => 'test-token']),
            '*/api/v2_1/orders' => Http::response([
                'status' => ['statusCode' => 'SUCCESS'],
                'redirectUri' => 'https://payu.test/offer-pay',
                'orderId' => 'PAYU-OFFER-ORDER',
            ], 302, ['Location' => 'https://payu.test/offer-pay']),
        ]);
        config([
            'services.payu.pos_id' => 'offer-test-pos',
            'services.payu.client_id' => 'test-client',
            'services.payu.client_secret' => 'test-secret',
            'services.payu.second_key' => 'test-second-key',
        ]);
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $token = 'valid-b2b-offer-token';
        $offer = QuoteOffer::create([
            'quote_request_id' => $quoteRequest->id,
            'version' => 1,
            'status' => 'sent',
            'token_hash' => hash('sha256', $token),
            'subtotal' => 100,
            'shipping_total' => 12,
            'total' => 112,
            'currency' => 'PLN',
            'valid_until' => today()->addDay(),
        ]);

        $first = $this->from(route('quote-offers.show', $token))->post(route('quote-offers.accept', $token));
        $second = $this->post(route('quote-offers.accept', $token));

        $first->assertRedirect('https://payu.test/offer-pay');
        $second->assertRedirect('https://payu.test/offer-pay');
        $this->assertDatabaseHas('quote_offers', ['id' => $offer->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('quote_requests', ['id' => $quoteRequest->id, 'status' => 'accepted']);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', ['total' => 112.00, 'customer_email' => 'anna@firma.pl']);
        Mail::assertQueued(OrderReceivedMail::class, 1);
    }

    public function test_customer_cannot_accept_an_expired_offer(): void
    {
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $token = 'expired-b2b-offer-token';
        $offer = QuoteOffer::create([
            'quote_request_id' => $quoteRequest->id,
            'version' => 1,
            'status' => 'sent',
            'token_hash' => hash('sha256', $token),
            'subtotal' => 100,
            'shipping_total' => 0,
            'total' => 100,
            'currency' => 'PLN',
            'valid_until' => today()->subDay(),
        ]);

        $this->from(route('quote-offers.show', $token))
            ->post(route('quote-offers.accept', $token))
            ->assertRedirect(route('quote-offers.show', $token))
            ->assertSessionHasErrors('offer');

        $this->assertDatabaseHas('quote_offers', ['id' => $offer->id, 'status' => 'expired']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_cannot_send_an_offer_with_an_incorrect_total(): void
    {
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.quote-requests.show', $quoteRequest))
            ->post(route('admin.quote-offers.store', $quoteRequest), [
                'subtotal' => 100,
                'shipping_total' => 12,
                'total' => 113,
                'currency' => 'PLN',
                'valid_until' => today()->addDays(14)->toDateString(),
            ])
            ->assertRedirect(route('admin.quote-requests.show', $quoteRequest))
            ->assertSessionHasErrors('total');

        $this->assertDatabaseCount('quote_offers', 0);
        Mail::assertQueued(B2bQuoteOfferSent::class, 0);
    }

    public function test_customer_can_retry_a_failed_offer_payment_without_creating_a_second_order(): void
    {
        Http::fakeSequence()
            ->push(['access_token' => 'test-token'])
            ->push(['message' => 'temporary failure'], 500)
            ->push(['status' => ['statusCode' => 'SUCCESS'], 'redirectUri' => 'https://payu.test/retry', 'orderId' => 'PAYU-RETRY-ORDER'], 302, ['Location' => 'https://payu.test/retry']);
        config([
            'services.payu.pos_id' => 'offer-retry-pos',
            'services.payu.client_id' => 'test-client',
            'services.payu.client_secret' => 'test-secret',
            'services.payu.second_key' => 'test-second-key',
        ]);
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $token = 'retry-b2b-offer-token';
        $offer = QuoteOffer::create([
            'quote_request_id' => $quoteRequest->id,
            'version' => 1,
            'status' => 'sent',
            'token_hash' => hash('sha256', $token),
            'subtotal' => 100,
            'shipping_total' => 0,
            'total' => 100,
            'currency' => 'PLN',
            'valid_until' => today()->addDay(),
        ]);

        $first = $this->from(route('quote-offers.show', $token))->post(route('quote-offers.accept', $token));
        $this->get(route('quote-offers.show', $token))->assertSee('Ponów płatność');
        $order = Order::query()->latest('id')->firstOrFail();
        $order->forceFill(['status' => 'cancelled'])->save();
        $second = $this->post(route('quote-offers.accept', $token));

        $first->assertRedirect(route('quote-offers.show', $token));
        $second->assertRedirect('https://payu.test/retry');
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseHas('payments', ['status' => 'failed']);
        $this->assertDatabaseHas('quote_offers', ['id' => $offer->id, 'status' => 'accepted']);
    }

    public function test_new_offer_invalidates_the_previous_sent_version(): void
    {
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = ['subtotal' => 100, 'shipping_total' => 0, 'total' => 100, 'currency' => 'PLN', 'valid_until' => today()->addDays(14)->toDateString()];
        $this->actingAs($admin)->post(route('admin.quote-offers.store', $quoteRequest), $payload);
        $this->actingAs($admin)->post(route('admin.quote-offers.store', $quoteRequest), $payload);

        $this->assertDatabaseHas('quote_offers', ['version' => 1, 'status' => 'superseded']);
        $this->assertDatabaseHas('quote_offers', ['version' => 2, 'status' => 'sent']);
    }

    public function test_b2b_request_rejects_a_configuration_value_outside_the_product_schema(): void
    {
        $this->seed(ProductSeeder::class);
        $configuration = $this->businessConfiguration('wizytowki');
        $configuration['format'] = 'nieistniejący-format';

        $this->postJson('/api/v1/b2b/quote-requests', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@firma.pl', 'company' => 'Firma'],
            'items' => [['product_slug' => 'wizytowki', 'configuration' => $configuration, 'quantity' => 100]],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.configuration.format');

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_b2b_request_rejects_an_inactive_configurator_product(): void
    {
        $this->seed(ProductSeeder::class);
        Product::query()->where('slug', 'plakaty')->update(['is_active' => false]);

        $this->postJson('/api/v1/b2b/quote-requests', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@firma.pl', 'company' => 'Firma'],
            'items' => [['product_slug' => 'plakaty', 'configuration' => $this->businessConfiguration('plakaty'), 'quantity' => 10]],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.product_slug');

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_b2b_request_rejects_a_numeric_value_outside_the_product_range(): void
    {
        $this->seed(ProductSeeder::class);
        $configuration = $this->businessConfiguration('banery');
        $configuration['width'] = '501';

        $this->postJson('/api/v1/b2b/quote-requests', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@firma.pl', 'company' => 'Firma'],
            'items' => [['product_slug' => 'banery', 'configuration' => $configuration, 'quantity' => 1]],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.configuration.width');

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_expire_quote_offers_command_updates_the_request_when_no_active_version_remains(): void
    {
        Mail::fake();
        $quoteRequest = $this->createQuoteRequest();
        $quoteRequest->forceFill(['status' => 'quoted'])->save();
        QuoteOffer::create([
            'quote_request_id' => $quoteRequest->id,
            'version' => 1,
            'status' => 'sent',
            'token_hash' => hash('sha256', 'command-expired-offer'),
            'subtotal' => 100,
            'shipping_total' => 0,
            'total' => 100,
            'currency' => 'PLN',
            'valid_until' => today()->subDay(),
        ]);

        $this->artisan('quote-offers:expire')->assertSuccessful();

        $this->assertDatabaseHas('quote_offers', ['status' => 'expired']);
        $this->assertDatabaseHas('quote_requests', ['id' => $quoteRequest->id, 'status' => 'expired']);
    }

    private function createQuoteRequest(): QuoteRequest
    {
        $this->seed(ProductSeeder::class);
        $this->postJson('/api/v1/b2b/quote-requests', [
            'customer' => ['name' => 'Anna Nowak', 'email' => 'anna@firma.pl', 'company' => 'Nowak Design'],
            'items' => [['product_slug' => 'plakaty', 'configuration' => $this->businessConfiguration('plakaty'), 'quantity' => 10]],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
        ])->assertCreated();

        return QuoteRequest::query()->latest('id')->firstOrFail();
    }

    /** @return array<string, string> */
    private function businessConfiguration(string $productSlug): array
    {
        return match ($productSlug) {
            'wizytowki' => [
                'qty' => '500',
                'format' => '90×50 mm',
                'printing' => 'dwustronnie',
                'paper' => '350 g biały',
                'finish' => 'bez',
                'designs' => '1',
            ],
            'plakaty' => [
                'qty' => '10',
                'format' => 'A3',
                'substrate' => 'papier 170 g',
            ],
            'banery' => [
                'width' => '100',
                'height' => '100',
                'material' => 'baner 510 g',
                'finish' => 'szwy z oczkami',
                'qty' => '1',
            ],
        };
    }
}
