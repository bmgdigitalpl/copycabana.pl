<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayuService
{
    public function createPayment(
        Order $order,
        Payment $payment,
        string $continueUrl,
        string $notifyUrl,
        string $customerIp,
    ): string {
        if (config('payment.provider') === 'mock') {
            return $this->createMockPayment($payment, $continueUrl);
        }

        $response = $this->client()->withToken($this->accessToken())
            ->withOptions(['allow_redirects' => false])
            ->post('/api/v2_1/orders', $this->orderPayload($order, $continueUrl, $notifyUrl, $customerIp));

        if (! in_array($response->status(), [200, 201, 302], true)) {
            $response->throw();
        }

        $redirectUri = $response->json('redirectUri') ?: $response->header('Location');
        $providerReference = $response->json('orderId');

        if (! is_string($redirectUri) || $redirectUri === '' || ! is_string($providerReference) || $providerReference === '') {
            throw new RuntimeException('PayU returned an incomplete payment response.');
        }

        $payment->forceFill([
            'provider_reference' => $providerReference,
            'status' => 'pending',
            'payload' => [
                'order_id' => $providerReference,
                'redirect_uri' => $redirectUri,
                'status' => $response->json('status.statusCode'),
            ],
        ])->save();

        return $redirectUri;
    }

    private function createMockPayment(Payment $payment, string $continueUrl): string
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Mock payments are only available in local and testing environments.');
        }

        $redirectUri = route('local-payments.show', $payment);

        $payment->forceFill([
            'provider_reference' => 'MOCK-'.Str::upper(Str::random(12)),
            'status' => 'pending',
            'payload' => [
                'redirect_uri' => $redirectUri,
                'continue_url' => $continueUrl,
                'status' => 'PENDING',
            ],
        ])->save();

        return $redirectUri;
    }

    public function hasValidNotificationSignature(string $rawBody, ?string $header): bool
    {
        if (! is_string($header) || ! is_string(config('services.payu.second_key')) || config('services.payu.second_key') === '') {
            return false;
        }

        preg_match('/(?:^|;)\s*signature=([^;]+)/', $header, $signatureMatches);
        preg_match('/(?:^|;)\s*algorithm=([^;]+)/', $header, $algorithmMatches);
        $algorithm = strtoupper($algorithmMatches[1] ?? '');
        $signature = $signatureMatches[1] ?? null;

        return $algorithm === 'MD5'
            && is_string($signature)
            && hash_equals((string) $signature, md5($rawBody.(string) config('services.payu.second_key')));
    }

    private function accessToken(): string
    {
        $clientId = config('services.payu.client_id');
        $clientSecret = config('services.payu.client_secret');
        $posId = config('services.payu.pos_id');

        if (! is_string($clientId) || $clientId === '' || ! is_string($clientSecret) || $clientSecret === '' || ! is_string($posId) || $posId === '') {
            throw new RuntimeException('PayU credentials are not configured.');
        }

        return Cache::remember('payu.oauth.'.$posId, now()->addHours(11), function () use ($clientId, $clientSecret): string {
            $response = $this->client()->asForm()->post('/pl/standard/user/oauth/authorize', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            $response->throw();
            $token = $response->json('access_token');

            if (! is_string($token) || $token === '') {
                throw new RuntimeException('PayU did not return an access token.');
            }

            return $token;
        });
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('services.payu.base_url'))
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(10);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order, string $continueUrl, string $notifyUrl, string $customerIp): array
    {
        $nameParts = preg_split('/\s+/', trim($order->customer_name), 2) ?: [];
        $products = $order->items->map(fn ($item): array => [
            'name' => $item->product_name,
            'unitPrice' => (string) round((float) $item->unit_price * 100),
            'quantity' => (int) $item->quantity,
        ])->values()->all();

        if ((float) $order->shipping_total > 0) {
            $products[] = [
                'name' => 'Dostawa',
                'unitPrice' => (string) round((float) $order->shipping_total * 100),
                'quantity' => 1,
            ];
        }

        return [
            'notifyUrl' => $notifyUrl,
            'continueUrl' => $continueUrl,
            'customerIp' => $customerIp,
            'merchantPosId' => (string) config('services.payu.pos_id'),
            'description' => 'Zamówienie '.$order->number,
            'visibleDescription' => 'Zamówienie '.$order->number,
            'statementDescription' => 'CopyCabana '.$order->number,
            'extOrderId' => $order->number,
            'currencyCode' => $order->currency,
            'totalAmount' => (string) round((float) $order->total * 100),
            'validityTime' => '86400',
            'buyer' => array_filter([
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'firstName' => $nameParts[0] ?? $order->customer_name,
                'lastName' => $nameParts[1] ?? null,
                'language' => 'pl',
            ]),
            'products' => $products,
            'payMethods' => [
                'payMethod' => [
                    'type' => 'PBL',
                    'value' => 'blik',
                ],
            ],
        ];
    }
}
