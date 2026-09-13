<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AdminNotificationService;
use App\Services\PayuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayuWebhookController extends Controller
{
    public function __invoke(Request $request, PayuService $payu, AdminNotificationService $notifications): JsonResponse
    {
        $rawBody = $request->getContent();

        abort_unless($payu->hasValidNotificationSignature($rawBody, $request->header('OpenPayU-Signature')), 403);

        $payload = json_decode($rawBody, true);
        abort_unless(is_array($payload), 400);

        $payuOrder = $payload['order'] ?? null;
        abort_unless(is_array($payuOrder), 400);

        $externalOrderNumber = $payuOrder['extOrderId'] ?? null;
        $providerReference = $payuOrder['orderId'] ?? null;
        $status = $payuOrder['status'] ?? null;
        $merchantPosId = $payuOrder['merchantPosId'] ?? null;

        abort_unless(is_string($externalOrderNumber) && is_string($providerReference) && is_string($status) && is_string($merchantPosId), 400);

        $changedOrder = DB::transaction(function () use ($externalOrderNumber, $providerReference, $status, $merchantPosId, $payload): ?Order {
            $order = Order::query()->where('number', $externalOrderNumber)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->where('provider', 'payu')
                ->where('provider_reference', $providerReference)
                ->lockForUpdate()
                ->firstOrFail();
            $wasPaid = $order->payment_status === 'paid';
            $nextPaymentStatus = $this->paymentStatus($status);
            $wasPaymentStatus = $payment->status;

            abort_unless($this->amountMatches($order, $payload), 400);
            abort_unless($merchantPosId === (string) config('services.payu.pos_id'), 400);

            $payment->forceFill([
                'provider_reference' => $providerReference,
                'status' => $nextPaymentStatus,
                'payload' => $payload,
                'paid_at' => $status === 'COMPLETED' ? now() : $payment->paid_at,
            ])->save();

            if ($status === 'COMPLETED' && $order->payment_status !== 'paid') {
                $order->forceFill([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ])->save();
            }

            if ($status === 'COMPLETED' && ! $wasPaid) {
                Mail::to($order->customer_email)->queue(new PaymentConfirmedMail($order->refresh()));
            }

            if (in_array($status, ['CANCELED', 'REJECTED'], true) && $order->status === OrderStatus::PaymentAwaited) {
                $order->transitionTo(OrderStatus::Cancelled, 'Płatność PayU została odrzucona lub anulowana.');
            }

            return $wasPaymentStatus !== $nextPaymentStatus ? $order : null;
        });

        if ($changedOrder !== null) {
            $notifications->paymentStatusChanged($changedOrder->refresh());
        }

        return response()->json(['received' => true]);
    }

    private function amountMatches(Order $order, array $payload): bool
    {
        $payuOrder = $payload['order'] ?? [];

        return (string) ($payuOrder['currencyCode'] ?? '') === $order->currency
            && (string) ($payuOrder['totalAmount'] ?? '') === (string) round((float) $order->total * 100);
    }

    private function paymentStatus(string $status): string
    {
        return match ($status) {
            'COMPLETED' => 'paid',
            'CANCELED', 'REJECTED' => 'failed',
            default => 'pending',
        };
    }
}
