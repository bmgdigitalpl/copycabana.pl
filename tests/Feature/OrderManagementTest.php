<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderReceivedMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\PaymentConfirmedMail;
use App\Models\Option;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
                'orderId' => 'PAYU-TEST-ORDER',
            ], 302, ['Location' => 'https://payu.test/pay']),
        ]);
    }

    public function test_checkout_creates_a_real_order_and_client_from_cart_data(): void
    {
        Mail::fake();
        $this->seed(ProductSeeder::class);

        $response = $this->postJson('/api/v1/orders', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
            'marketing_consent' => false,
            'items' => [['product_slug' => 'wizytowki', 'quantity' => 2, 'configuration' => ['format' => 'A6'], 'unit_price' => 0.01]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('order.status', 'payment_awaited')
            ->assertJsonPath('payment_url', 'https://payu.test/pay');
        $this->assertDatabaseHas('clients', ['email' => 'jan@example.com', 'marketing_consent' => false]);
        $this->assertDatabaseHas('orders', ['customer_email' => 'jan@example.com', 'total' => 50]);
        Mail::assertQueued(OrderReceivedMail::class);
    }

    public function test_checkout_requires_privacy_acceptance(): void
    {
        $this->seed(ProductSeeder::class);

        $this->postJson('/api/v1/orders', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'shipping_method' => 'pickup',
            'items' => [['product_slug' => 'wizytowki', 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('privacy_policy_accepted');
    }

    public function test_product_options_are_exposed_and_priced_on_the_server(): void
    {
        Mail::fake();
        $product = Product::factory()->create(['starting_price' => 10]);
        $option = Option::factory()->create(['code' => 'paper']);
        $value = $option->values()->create(['label' => 'Premium', 'value' => 'premium', 'price_modifier' => 5]);
        $product->options()->attach($option);

        $this->getJson('/api/v1/products/'.$product->slug)
            ->assertOk()
            ->assertJsonPath('data.options.0.values.0.id', $value->id);

        $this->postJson('/api/v1/orders', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
            'items' => [['product_slug' => $product->slug, 'quantity' => 1, 'option_value_ids' => [$value->id]]],
        ])->assertCreated();

        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'unit_price' => 15]);
    }

    public function test_invoice_is_created_from_the_order_snapshot(): void
    {
        Mail::fake();
        $this->seed(ProductSeeder::class);

        $this->postJson('/api/v1/orders', [
            'customer' => ['name' => 'Firma Katowice', 'email' => 'firma@example.com', 'company' => 'Firma Sp. z o.o.', 'nip' => '5261040828'],
            'shipping_method' => 'pickup',
            'invoice_required' => true,
            'privacy_policy_accepted' => true,
            'items' => [['product_slug' => 'wizytowki', 'quantity' => 1]],
        ])->assertCreated();

        $this->assertDatabaseHas('invoices', ['buyer_nip' => '5261040828', 'gross_total' => 25]);
    }

    public function test_admin_can_move_an_order_through_the_workflow(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->actingAs($user)->put(route('admin.orders.update', $order), [
            'status' => 'processing',
            'carrier' => 'inpost',
            'tracking_number' => '123456789',
            'note' => 'Przekazano do realizacji.',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing', 'carrier' => 'inpost']);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'to_status' => 'processing']);
        Mail::assertQueued(OrderStatusChangedMail::class);
    }

    public function test_terminal_order_cannot_be_reopened(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

        $this->actingAs($user)->put(route('admin.orders.update', $order), ['status' => 'processing'])
            ->assertRedirect()->assertSessionHasErrors('status');
    }

    public function test_payu_webhook_marks_the_payment_as_paid(): void
    {
        Mail::fake();
        $this->seed(ProductSeeder::class);

        $this->postJson('/api/v1/orders', [
            'customer' => ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
            'shipping_method' => 'pickup',
            'privacy_policy_accepted' => true,
            'items' => [['product_slug' => 'wizytowki', 'quantity' => 1]],
        ])->assertCreated();

        $order = Order::query()->latest('id')->firstOrFail();
        $rawBody = json_encode([
            'order' => [
                'orderId' => 'PAYU-TEST-ORDER',
                'extOrderId' => $order->number,
                'status' => 'COMPLETED',
                'currencyCode' => 'PLN',
                'totalAmount' => (string) round((float) $order->total * 100),
                'merchantPosId' => 'test-pos',
            ],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('api.payments.payu.notify'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_OPENPAYU_SIGNATURE' => 'signature='.md5($rawBody.'test-second-key').';algorithm=MD5;sender=checkout',
        ], $rawBody)->assertOk();

        $this->call('POST', route('api.payments.payu.notify'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_OPENPAYU_SIGNATURE' => 'signature='.md5($rawBody.'test-second-key').';algorithm=MD5;sender=checkout',
        ], $rawBody)->assertOk();

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
        Mail::assertQueued(PaymentConfirmedMail::class, 1);
    }

    public function test_payu_webhook_rejects_an_invalid_signature(): void
    {
        $rawBody = json_encode(['order' => []], JSON_THROW_ON_ERROR);

        $this->call('POST', route('api.payments.payu.notify'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_OPENPAYU_SIGNATURE' => 'signature=invalid;algorithm=MD5;sender=checkout',
        ], $rawBody)->assertForbidden();
    }

    public function test_payu_webhook_requires_the_configured_merchant_pos_id(): void
    {
        $rawBody = json_encode([
            'order' => [
                'orderId' => 'PAYU-TEST-ORDER',
                'extOrderId' => 'CC-20260913-ABC123',
                'status' => 'COMPLETED',
            ],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('api.payments.payu.notify'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_OPENPAYU_SIGNATURE' => 'signature='.md5($rawBody.'test-second-key').';algorithm=MD5;sender=checkout',
        ], $rawBody)->assertBadRequest();
    }

    public function test_order_success_page_requires_the_private_token(): void
    {
        $token = 'private-success-token';
        $order = Order::factory()->create(['success_token_hash' => hash('sha256', $token)]);

        $this->get('/zamowienie/1/sukces')->assertNotFound();
        $this->get(route('checkout.success', ['token' => $token]))
            ->assertOk()
            ->assertSee($order->number);
    }
}
