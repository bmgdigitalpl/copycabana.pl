<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFile;
use App\Services\PayuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->client->orders()
            ->with('invoice')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, string $orderNumber): View
    {
        $order = $request->user()->client->orders()
            ->where('number', $orderNumber)
            ->with(['items', 'files', 'payments', 'invoice'])
            ->firstOrFail();
        Gate::authorize('view', $order);

        return view('customer.orders.show', compact('order'));
    }

    public function downloadFile(Request $request, string $orderNumber, OrderFile $file): StreamedResponse
    {
        $order = $request->user()->client->orders()->where('number', $orderNumber)->firstOrFail();
        Gate::authorize('view', $order);
        $file = $order->files()->whereKey($file->id)->where('status', 'attached')->firstOrFail();

        return Storage::disk($file->disk)->download($file->path, $file->original_name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    public function retryPayment(Request $request, string $orderNumber, PayuService $payu): RedirectResponse
    {
        $order = $request->user()->client->orders()->where('number', $orderNumber)->firstOrFail();
        Gate::authorize('retryPayment', $order);
        $successToken = Str::random(64);

        [$order, $payment] = DB::transaction(function () use ($order, $successToken): array {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->prepareForPaymentRetry();
            $order->forceFill(['success_token_hash' => hash('sha256', $successToken)])->save();
            $payment = $order->payments()->create([
                'provider' => config('payment.provider'),
                'status' => 'pending',
                'amount' => $order->total,
                'currency' => $order->currency,
            ]);

            return [$order->load('items'), $payment];
        });

        try {
            $paymentUrl = $payu->createPayment(
                $order,
                $payment,
                route('checkout.success', ['token' => $successToken]),
                route('api.payments.payu.notify'),
                $request->ip(),
            );
            $order->transitionTo(OrderStatus::PaymentAwaited);

            return redirect()->away($paymentUrl);
        } catch (\Throwable $exception) {
            $payment->forceFill(['status' => 'failed'])->save();
            report($exception);

            return back()->withErrors(['payment' => 'Nie udało się rozpocząć płatności. Spróbuj ponownie.']);
        }
    }
}
