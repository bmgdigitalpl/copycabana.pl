<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderReceivedMail;
use App\Mail\OrderStatusChangedMail;
use App\Models\Option;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

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

        $response->assertCreated()->assertJsonPath('order.status', 'pending');
        $this->assertDatabaseHas('clients', ['email' => 'jan@example.com', 'marketing_consent' => false]);
        $this->assertDatabaseHas('orders', ['customer_email' => 'jan@example.com', 'total' => 50]);
        Mail::assertSent(OrderReceivedMail::class);
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
        Mail::assertSent(OrderStatusChangedMail::class);
    }

    public function test_terminal_order_cannot_be_reopened(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

        $this->actingAs($user)->put(route('admin.orders.update', $order), ['status' => 'processing'])
            ->assertRedirect()->assertSessionHasErrors('status');
    }
}
