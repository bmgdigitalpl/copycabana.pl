<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderReceivedMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\ProductPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly ProductPricingService $pricing) {}

    public function store(CheckoutRequest $request, InvoiceService $invoiceService): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $customer = $data['customer'];
        $shippingTotal = match ($data['shipping_method']) {
            'parcel' => 12.00,
            'courier' => 18.00,
            default => 0.00,
        };

        $order = DB::transaction(function () use ($data, $customer, $shippingTotal, $invoiceService): Order {
            $client = Client::query()->firstOrNew(['email' => $customer['email']]);
            $client->fill([
                'name' => $customer['name'],
                'phone' => $customer['phone'] ?? null,
                'company' => $customer['company'] ?? null,
                'nip' => $customer['nip'] ?? null,
                'privacy_policy_version' => config('app.privacy_policy_version'),
                'privacy_policy_accepted_at' => now(),
            ]);
            if (! $client->exists || ($data['marketing_consent'] ?? false)) {
                $client->marketing_consent = (bool) ($data['marketing_consent'] ?? false);
                $client->marketing_consent_at = $client->marketing_consent ? now() : null;
            }
            $client->save();

            $items = [];
            $subtotal = 0.00;

            foreach ($data['items'] as $item) {
                $product = Product::query()->active()->where('slug', $item['product_slug'])->firstOrFail();
                $pricing = $this->pricing->calculate($product, $item['option_value_ids'] ?? []);
                $lineTotal = round($pricing['unit_price'] * $item['quantity'], 2);
                $subtotal += $lineTotal;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'configuration' => [
                        'selected_options' => $pricing['configuration'],
                        'customer_configuration' => $item['configuration'] ?? [],
                    ],
                    'unit_price' => $pricing['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'number' => $this->orderNumber(),
                'client_id' => $client->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => 'PLN',
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'] ?? null,
                'customer_company' => $customer['company'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'shipping_method' => $data['shipping_method'],
                'shipping_address' => $data['shipping_address'] ?? null,
                'subtotal' => $subtotal,
                'shipping_total' => $shippingTotal,
                'tax_rate' => 23,
                'invoice_required' => (bool) ($data['invoice_required'] ?? false),
                'invoice_nip' => $customer['nip'] ?? null,
                'total' => $subtotal + $shippingTotal,
                'notes' => $customer['notes'] ?? null,
            ]);
            $order->items()->createMany($items);
            $order->statusHistories()->create(['to_status' => 'pending', 'changed_by' => null]);

            if ($order->invoice_required) {
                $invoiceService->createForOrder($order);
            }

            return $order;
        });

        Mail::to($order->customer_email)->send(new OrderReceivedMail($order));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Zamówienie zostało przyjęte.', 'order' => $order->load('items')], 201);
        }

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order): View
    {
        return view('checkout.success', compact('order'));
    }

    private function orderNumber(): string
    {
        do {
            $number = 'CC-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
