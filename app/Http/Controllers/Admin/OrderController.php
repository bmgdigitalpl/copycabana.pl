<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Carrier;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()->with('client')
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->string('q')->toString(), function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            })->latest()->paginate(25)->withQueryString();

        return view('admin.orders.index', ['orders' => $orders, 'statuses' => OrderStatus::labels()]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['items', 'client', 'payments', 'statusHistories.changedBy', 'invoice']),
            'statuses' => OrderStatus::labels(),
            'carriers' => Carrier::labels(),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $status = OrderStatus::from($data['status']);
        $wasChanged = $order->status !== $status;

        try {
            DB::transaction(function () use ($data, $order, $status): void {
                $order->forceFill([
                    'carrier' => $data['carrier'] ?? null,
                    'tracking_number' => $data['tracking_number'] ?? null,
                ])->save();
                $order->transitionTo($status, $data['note'] ?? null);
            });
        } catch (\DomainException $exception) {
            return back()->withErrors(['status' => 'Niedozwolona zmiana statusu zamówienia.']);
        }

        if ($wasChanged) {
            Mail::to($order->customer_email)->send(new OrderStatusChangedMail($order->refresh()));
        }

        return back()->with('status', 'Zamówienie zostało zaktualizowane.');
    }

    public function invoice(Order $order, InvoiceService $invoiceService): View
    {
        $invoice = $invoiceService->createForOrder($order);

        return view('admin.invoices.show', compact('invoice'));
    }
}
