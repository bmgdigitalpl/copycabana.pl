<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class InPostShipmentService
{
    /**
     * Create an InPost ShipX shipment for an order sent to a parcel locker and store its label.
     *
     * In "mock" mode (the default outside production, see config/services.php) this never
     * calls InPost at all — it only fabricates a tracking number and a placeholder label, so
     * testing the order flow locally can never create a real, payable shipment.
     *
     * @return array{tracking_number: string, shipping_label_path: string}
     */
    public function createForOrder(Order $order): array
    {
        $targetPoint = (string) data_get($order->shipping_address, 'point_code', '');

        if ($targetPoint === '') {
            throw new RuntimeException("Order {$order->number} has no InPost parcel locker selected.");
        }

        return $this->mode() === 'mock'
            ? $this->createMockShipment($order, $targetPoint)
            : $this->createRealShipment($order, $targetPoint);
    }

    private function mode(): string
    {
        return (string) config('services.inpost.shipx.mode', 'mock');
    }

    /** @return array{tracking_number: string, shipping_label_path: string} */
    private function createMockShipment(Order $order, string $targetPoint): array
    {
        $trackingNumber = 'MOCK-INPOST-'.Str::upper(Str::random(10));
        $path = "shipping-labels/{$order->number}.txt";

        Storage::disk('local')->put($path, implode("\n", [
            'Etykieta testowa — tryb mock (bez połączenia z InPost).',
            "Zamówienie: {$order->number}",
            "Paczkomat: {$targetPoint}",
            "Numer przesyłki: {$trackingNumber}",
        ]));

        return ['tracking_number' => $trackingNumber, 'shipping_label_path' => $path];
    }

    /**
     * NOTE: mirrors InPost's documented ShipX v1 flow (create shipment → poll for a tracking
     * number once it is bought/confirmed → download the label). Field names come from InPost's
     * public API docs; verify them against the current ShipX reference before relying on this
     * in production, since third-party API contracts do change over time.
     *
     * @return array{tracking_number: string, shipping_label_path: string}
     */
    private function createRealShipment(Order $order, string $targetPoint): array
    {
        $shipmentId = $this->requestShipment($order, $targetPoint);
        $trackingNumber = $this->waitForTrackingNumber($shipmentId);
        $path = $this->downloadLabel($order, $shipmentId);

        return ['tracking_number' => $trackingNumber, 'shipping_label_path' => $path];
    }

    private function requestShipment(Order $order, string $targetPoint): int
    {
        $sender = (array) config('services.inpost.shipx.sender');
        [$firstName, $lastName] = $this->splitName($order->customer_name);

        $response = $this->client()->post("/v1/organizations/{$this->organizationId()}/shipments", [
            'receiver' => [
                'first_name' => $firstName,
                'last_name' => $lastName ?: $firstName,
                'email' => $order->customer_email,
                'phone' => $this->phone($order->customer_phone),
            ],
            'sender' => [
                'first_name' => $sender['name'] ?? 'CopyCabana',
                'last_name' => '',
                'email' => $sender['email'] ?? null,
                'phone' => $this->phone($sender['phone'] ?? null),
                'address' => [
                    'street' => $sender['address'] ?? null,
                    'city' => $sender['city'] ?? null,
                    'post_code' => $sender['post_code'] ?? null,
                    'country_code' => $sender['country_code'] ?? 'PL',
                ],
            ],
            'parcels' => [['template' => 'small']],
            'service' => 'inpost_locker_standard',
            'reference' => $order->number,
            'custom_attributes' => ['target_point' => $targetPoint],
        ]);

        $response->throw();
        $id = $response->json('id');

        if (! is_int($id)) {
            throw new RuntimeException("InPost ShipX did not return a shipment id for order {$order->number}.");
        }

        return $id;
    }

    /**
     * ShipX buys/confirms a shipment asynchronously, so the tracking number is not always
     * present on the create response — poll briefly before giving up.
     */
    private function waitForTrackingNumber(int $shipmentId): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->client()->get("/v1/shipments/{$shipmentId}");
            $response->throw();
            $trackingNumber = $response->json('tracking_number');

            if (is_string($trackingNumber) && $trackingNumber !== '') {
                return $trackingNumber;
            }

            usleep(400_000);
        }

        throw new RuntimeException("InPost ShipX shipment {$shipmentId} has no tracking number yet.");
    }

    private function downloadLabel(Order $order, int $shipmentId): string
    {
        $response = $this->client()->get("/v1/shipments/{$shipmentId}/label", ['type' => 'pdf']);
        $response->throw();

        $path = "shipping-labels/{$order->number}.pdf";
        Storage::disk('local')->put($path, $response->body());

        return $path;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken((string) config('services.inpost.shipx.token'))
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function baseUrl(): string
    {
        $override = config('services.inpost.shipx.base_url');

        if (is_string($override) && $override !== '') {
            return rtrim($override, '/');
        }

        return $this->mode() === 'sandbox'
            ? 'https://sandbox-api-shipx-pl.easypack24.net'
            : 'https://api-shipx-pl.easypack24.net';
    }

    private function organizationId(): string
    {
        $organizationId = config('services.inpost.shipx.organization_id');

        if (! is_string($organizationId) || $organizationId === '') {
            throw new RuntimeException('InPost ShipX organization_id is not configured.');
        }

        return $organizationId;
    }

    /** @return array{0: string, 1: string|null} */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [$name];

        return [$parts[0] !== '' ? $parts[0] : $name, $parts[1] ?? null];
    }

    private function phone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return $digits !== '' ? $digits : '000000000';
    }
}
