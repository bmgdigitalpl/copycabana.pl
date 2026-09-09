<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createForOrder(Order $order): Invoice
    {
        return DB::transaction(function () use ($order): Invoice {
            if ($existing = $order->invoice()->first()) {
                return $existing;
            }

            $gross = (float) $order->total;
            $rate = (float) ($order->tax_rate ?: 23);
            $net = round($gross / (1 + ($rate / 100)), 2);

            return Invoice::create([
                'order_id' => $order->id,
                'number' => $this->nextNumber(),
                'buyer_name' => $order->customer_name,
                'buyer_email' => $order->customer_email,
                'buyer_company' => $order->customer_company,
                'buyer_nip' => $order->invoice_nip,
                'buyer_address' => $order->billing_address,
                'currency' => $order->currency,
                'net_total' => $net,
                'vat_total' => round($gross - $net, 2),
                'gross_total' => $gross,
                'vat_rate' => $rate,
                'status' => 'issued',
                'issued_at' => now(),
            ]);
        });
    }

    private function nextNumber(): string
    {
        $prefix = 'FV/'.now()->format('Y/m').'/';
        $last = Invoice::query()->where('number', 'like', $prefix.'%')->latest('id')->value('number');
        $sequence = $last ? ((int) str($last)->afterLast('/') + 1) : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
