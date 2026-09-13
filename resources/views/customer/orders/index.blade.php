@extends('layouts.main')
@section('title', 'Moje zamówienia | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem"><div class="flex flex-wrap items-end justify-between gap-4"><div><a class="text-sm text-[#D51A70]" href="{{ route('customer.dashboard') }}">← Moje konto</a><h1 class="mt-2 text-4xl font-bold">Moje zamówienia</h1></div></div>
  <div class="mt-8 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full min-w-[700px] text-left text-sm"><thead><tr class="border-b text-slate-500"><th class="p-4">Numer</th><th class="p-4">Data</th><th class="p-4">Status</th><th class="p-4">Płatność</th><th class="p-4 text-right">Suma</th></tr></thead><tbody>@forelse($orders as $order)<tr class="border-b"><td class="p-4"><a class="font-semibold text-[#D51A70]" href="{{ route('customer.orders.show', $order->number) }}">{{ $order->number }}</a></td><td class="p-4">{{ $order->created_at->format('d.m.Y H:i') }}</td><td class="p-4">{{ $order->status->label() }}</td><td class="p-4">{{ $order->payment_status }}</td><td class="p-4 text-right">{{ number_format((float) $order->total, 2, ',', ' ') }} zł</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Brak zamówień.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $orders->links() }}</div>
</main>
@endsection
