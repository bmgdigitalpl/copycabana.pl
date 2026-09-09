<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function orders(): StreamedResponse
    {
        return $this->csv('orders.csv', ['Numer', 'Data', 'Klient', 'E-mail', 'Status', 'Suma'], function (): iterable {
            foreach (Order::query()->latest()->cursor() as $order) {
                yield [$order->number, $order->created_at?->toDateString(), $order->customer_name, $order->customer_email, $order->status->label(), $order->total.' '.$order->currency];
            }
        });
    }

    public function clients(): StreamedResponse
    {
        return $this->csv('clients.csv', ['Nazwa', 'E-mail', 'Telefon', 'Firma', 'Zgoda marketingowa', 'Liczba zamówień'], function (): iterable {
            foreach (Client::query()->withCount('orders')->cursor() as $client) {
                yield [$client->name, $client->email, $client->phone, $client->company, $client->marketing_consent ? 'tak' : 'nie', $client->orders_count];
            }
        });
    }

    /** @param callable(): iterable<int, array<int, mixed>> $rows */
    private function csv(string $filename, array $headings, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headings, ';');
            foreach ($rows() as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
