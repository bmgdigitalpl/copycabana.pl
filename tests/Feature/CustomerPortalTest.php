<?php

namespace Tests\Feature;

use App\Enums\QuoteOfferStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\QuoteOffer;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_only_their_own_orders(): void
    {
        [$user, $client] = $this->createCustomer('owner@example.com');
        $ownOrder = Order::factory()->create([
            'client_id' => $client->id,
            'number' => 'CC-OWN-001',
        ]);
        $otherClient = Client::factory()->create(['email' => 'other@example.com']);
        $otherOrder = Order::factory()->create([
            'client_id' => $otherClient->id,
            'number' => 'CC-OTHER-001',
        ]);

        $response = $this->actingAs($user)->get(route('customer.orders.index'));

        $response->assertSee($ownOrder->number);
        $response->assertDontSee($otherOrder->number);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        [$user] = $this->createCustomer('owner@example.com');
        $otherClient = Client::factory()->create(['email' => 'other@example.com']);
        $otherOrder = Order::factory()->create([
            'client_id' => $otherClient->id,
            'number' => 'CC-OTHER-002',
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $otherOrder->number))
            ->assertNotFound();
    }

    public function test_customer_can_view_their_invoice_but_not_another_customers_invoice(): void
    {
        [$user, $client] = $this->createCustomer('owner@example.com');
        $ownOrder = Order::factory()->create(['client_id' => $client->id, 'number' => 'CC-OWN-003']);
        $ownInvoice = Invoice::create([
            'order_id' => $ownOrder->id,
            'number' => 'FV/2026/09/0001',
            'buyer_name' => 'Owner',
            'buyer_email' => 'owner@example.com',
            'currency' => 'PLN',
            'net_total' => 81.30,
            'vat_total' => 18.70,
            'gross_total' => 100,
            'vat_rate' => 23,
            'status' => 'issued',
            'issued_at' => now(),
        ]);
        $otherClient = Client::factory()->create(['email' => 'other@example.com']);
        $otherOrder = Order::factory()->create(['client_id' => $otherClient->id, 'number' => 'CC-OTHER-003']);
        $otherInvoice = Invoice::create([
            'order_id' => $otherOrder->id,
            'number' => 'FV/2026/09/0002',
            'buyer_name' => 'Other',
            'buyer_email' => 'other@example.com',
            'currency' => 'PLN',
            'net_total' => 81.30,
            'vat_total' => 18.70,
            'gross_total' => 100,
            'vat_rate' => 23,
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        $this->actingAs($user)->get(route('customer.invoices.show', $ownInvoice))->assertSee($ownInvoice->number);
        $this->actingAs($user)->get(route('customer.invoices.show', $otherInvoice))->assertNotFound();
    }

    public function test_customer_can_download_an_attached_file_from_their_order(): void
    {
        Storage::fake('local');
        [$user, $client] = $this->createCustomer('owner@example.com');
        $order = Order::factory()->create(['client_id' => $client->id, 'number' => 'CC-OWN-004']);
        Storage::disk('local')->put('orders/owner.pdf', 'pdf content');
        $file = OrderFile::create([
            'order_id' => $order->id,
            'token_hash' => hash('sha256', 'token'),
            'disk' => 'local',
            'path' => 'orders/owner.pdf',
            'original_name' => 'owner.pdf',
            'mime_type' => 'application/pdf',
            'size' => 11,
            'sha256' => hash('sha256', 'pdf content'),
            'pages' => 1,
            'status' => 'attached',
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.files.download', [$order->number, $file]))
            ->assertDownload('owner.pdf');
    }

    public function test_customer_can_update_profile_data_without_changing_their_email(): void
    {
        [$user, $client] = $this->createCustomer('owner@example.com');

        $response = $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => 'Nowe Imię',
            'phone' => '500600700',
            'company' => 'Nowa Firma',
            'nip' => '5261040828',
            'billing_address' => [
                'street' => 'Bankowa 11',
                'postal_code' => '40-007',
                'city' => 'Katowice',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Nowe Imię', 'email' => 'owner@example.com']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nowe Imię']);
    }

    public function test_customer_can_view_their_quote_and_current_offer(): void
    {
        [$user, $client] = $this->createCustomer('owner@example.com');
        $quoteRequest = QuoteRequest::create([
            'reference' => 'Q-OWNER-001',
            'status' => QuoteRequestStatus::Quoted,
            'client_id' => $client->id,
            'customer_name' => $client->name,
            'customer_email' => $client->email,
            'company_name' => 'Owner Sp. z o.o.',
            'invoice_required' => true,
        ]);
        $offer = QuoteOffer::create([
            'quote_request_id' => $quoteRequest->id,
            'version' => 1,
            'status' => QuoteOfferStatus::Sent,
            'token_hash' => hash('sha256', 'offer-token'),
            'subtotal' => 100,
            'shipping_total' => 0,
            'total' => 100,
            'currency' => 'PLN',
            'valid_until' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->get(route('customer.quotes.show', $quoteRequest->reference));

        $response->assertSee($quoteRequest->reference)->assertSee('100,00')->assertSee((string) $offer->version);
    }

    public function test_customer_can_retry_a_failed_payment_for_their_order(): void
    {
        config([
            'services.payu.pos_id' => 'test-pos',
            'services.payu.client_id' => 'test-client',
            'services.payu.client_secret' => 'test-secret',
            'services.payu.second_key' => 'test-second-key',
        ]);
        Http::fake([
            '*/pl/standard/user/oauth/authorize' => Http::response(['access_token' => 'test-token'], 200),
            '*/api/v2_1/orders' => Http::response([
                'status' => ['statusCode' => 'SUCCESS'],
                'redirectUri' => 'https://payu.test/pay',
                'orderId' => 'PAYU-RETRY-ORDER',
            ], 302),
        ]);
        [$user, $client] = $this->createCustomer('owner@example.com');
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'number' => 'CC-OWN-005',
            'payment_status' => 'failed',
        ]);
        $order->items()->create([
            'product_name' => 'Wizytówki',
            'product_slug' => 'wizytowki',
            'unit_price' => 100,
            'quantity' => 1,
            'total' => 100,
        ]);

        $response = $this->actingAs($user)->post(route('customer.orders.retry-payment', $order->number));

        $response->assertRedirect('https://payu.test/pay');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'payment_awaited']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'provider_reference' => 'PAYU-RETRY-ORDER']);
    }

    /** @return array{0: User, 1: Client} */
    private function createCustomer(string $email): array
    {
        $client = Client::factory()->create(['email' => $email]);
        $user = User::factory()->create([
            'email' => $email,
            'role' => 'customer',
            'client_id' => $client->id,
        ]);

        return [$user, $client];
    }
}
