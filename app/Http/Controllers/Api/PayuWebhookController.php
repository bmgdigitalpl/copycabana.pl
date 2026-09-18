<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateInPostShippingLabel;
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
        abort_unless(config('payment.provider') === 'payu', 404);

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

        [$changedOrder, $paymentConfirmed] = DB::transaction(function () use ($externalOrderNumber, $providerReference, $status, $merchantPosId, $payload): array {
            $order = Order::query()->where('number', $externalOrderNumber)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->where('provider', config('payment.provider'))
                ->where('provider_reference', $providerReference)
                ->lockForUpdate()
                ->firstOrFail();
            $nextPaymentStatus = $this->paymentStatus($status);

            abort_unless($this->amountMatches($order, $payload), 400);
            abort_unless($merchantPosId === (string) config('services.payu.pos_id'), 400);

            if (! $payment->canTransitionTo($nextPaymentStatus)) {
                return [null, false];
            }

            $payment->forceFill([
                'status' => $nextPaymentStatus,
                'payload' => $payload,
                'paid_at' => $nextPaymentStatus === 'paid' ? now() : $payment->paid_at,
            ])->save();

            if ($nextPaymentStatus === 'paid') {
                $order->forceFill([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ])->save();
            }

            if ($nextPaymentStatus === 'failed') {
                $order->forceFill(['payment_status' => 'failed'])->save();
            }

            if ($nextPaymentStatus === 'failed' && $order->status === OrderStatus::PaymentAwaited) {
                $order->transitionTo(OrderStatus::Cancelled, 'Płatność PayU została odrzucona lub anulowana.');
            }

            return [$order, $nextPaymentStatus === 'paid'];
        });

        if ($paymentConfirmed) {
            Mail::to($changedOrder->customer_email)->queue(new PaymentConfirmedMail($changedOrder->refresh()));

            if ($changedOrder->shipping_method === 'parcel') {
                GenerateInPostShippingLabel::dispatch($changedOrder->id);
            }
        }

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
