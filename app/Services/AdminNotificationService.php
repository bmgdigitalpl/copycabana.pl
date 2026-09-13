<?php

namespace App\Services;

use App\Models\DataRequest;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Support\Facades\Notification;

class AdminNotificationService
{
    public function orderCreated(Order $order): void
    {
        $this->sendToTeam(new AdminActivityNotification(
            'Nowe zamówienie',
            sprintf('Zamówienie %s od %s wymaga obsługi.', $order->number, $order->customer_name),
            'Zamówienie',
            route('admin.orders.show', $order),
        ));
    }

    public function quoteRequestCreated(QuoteRequest $quoteRequest): void
    {
        $this->sendToTeam(new AdminActivityNotification(
            'Nowe zapytanie B2B',
            sprintf('Zapytanie %s od %s czeka na wycenę.', $quoteRequest->reference, $quoteRequest->company_name),
            'B2B',
            route('admin.quote-requests.show', $quoteRequest),
        ));
    }

    public function paymentStatusChanged(Order $order): void
    {
        $this->sendToTeam(new AdminActivityNotification(
            'Zmiana statusu płatności',
            sprintf('Płatność zamówienia %s ma teraz status: %s.', $order->number, $order->payment_status),
            'Płatność',
            route('admin.orders.show', $order),
        ));
    }

    public function privacyRequestCreated(DataRequest $dataRequest): void
    {
        $this->sendToOwner(new AdminActivityNotification(
            'Nowy wniosek RODO',
            sprintf('Wniosek dotyczący klienta %s wymaga obsługi.', $dataRequest->client->name),
            'RODO',
            route('admin.privacy.index'),
        ));
    }

    private function sendToTeam(AdminActivityNotification $notification): void
    {
        Notification::send(
            User::query()->whereIn('role', ['admin', 'staff'])->whereNull('disabled_at')->get(),
            $notification,
        );
    }

    private function sendToOwner(AdminActivityNotification $notification): void
    {
        Notification::send(
            User::query()->where('role', 'admin')->whereNull('disabled_at')->get(),
            $notification,
        );
    }
}
