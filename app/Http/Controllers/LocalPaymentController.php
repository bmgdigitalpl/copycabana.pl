<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInPostShippingLabel;
use App\Mail\PaymentConfirmedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LocalPaymentController extends Controller
{
    public function show(Payment $payment): View
    {
        $this->ensureLocalPayment($payment);

        return view('payments.local', ['payment' => $payment->load('order')]);
    }

    public function store(Payment $payment, AdminNotificationService $notifications): RedirectResponse
    {
        $this->ensureLocalPayment($payment);
        $continueUrl = $this->continueUrl($payment);

        $changedOrder = DB::transaction(function () use ($payment): ?Order {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            if (! $payment->canTransitionTo('paid')) {
                return null;
            }

            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => [...$payment->payload, 'status' => 'COMPLETED'],
            ])->save();
            $order->forceFill([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ])->save();

            return $order;
        });

        if ($changedOrder !== null) {
            Mail::to($changedOrder->customer_email)->queue(new PaymentConfirmedMail($changedOrder->refresh()));
            $notifications->paymentStatusChanged($changedOrder->refresh());

            if ($changedOrder->shipping_method === 'parcel') {
                GenerateInPostShippingLabel::dispatch($changedOrder->id);
            }
        }

        return redirect()->to($continueUrl);
    }

    private function ensureLocalPayment(Payment $payment): void
    {
        abort_unless(app()->environment('local', 'testing') && $payment->provider === 'mock', 404);
    }

    private function continueUrl(Payment $payment): string
    {
        $continueUrl = is_array($payment->payload) ? $payment->payload['continue_url'] ?? null : null;

        abort_unless(is_string($continueUrl) && $continueUrl !== '', 404);

        return $continueUrl;
    }
}
