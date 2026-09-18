<?php

namespace App\Jobs;

use App\Enums\Carrier;
use App\Models\Order;
use App\Services\InPostShipmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Requests an InPost shipping label for an order once its payment is confirmed.
 *
 * Runs at most once (no automatic retries): a real ShipX shipment is a paid, billable
 * action, so silently retrying after a failure could create duplicate real shipments.
 * A failed attempt is reported and left for an admin to retry manually from the order
 * page instead.
 */
class GenerateInPostShippingLabel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly int $orderId) {}

    public function handle(InPostShipmentService $shipments): void
    {
        $order = Order::query()->find($this->orderId);

        if ($order === null || $order->shipping_method !== 'parcel' || $order->tracking_number !== null) {
            return;
        }

        try {
            $result = $shipments->createForOrder($order);
        } catch (Throwable $exception) {
            report($exception);

            return;
        }

        $order->forceFill([
            'carrier' => Carrier::InPost,
            'tracking_number' => $result['tracking_number'],
            'shipping_label_path' => $result['shipping_label_path'],
        ])->save();
    }
}
