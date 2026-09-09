@extends('layouts.admin')
@section('title', 'Pulpit | CopyCabana')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm text-slate-500">Dzień dobry, {{ auth()->user()->name }}</p><h1 class="text-3xl font-bold">Pulpit</h1></div><div class="flex gap-2 text-sm"><a class="rounded bg-white px-3 py-2 shadow" href="{{ route('admin.exports.orders') }}">Eksport zamówień</a><a class="rounded bg-white px-3 py-2 shadow" href="{{ route('admin.exports.clients') }}">Eksport klientów</a></div></div>
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['Zamówienia', $stats['orders']], ['Przychód', number_format($stats['revenue'], 2, ',', ' ').' zł'], ['Klienci', $stats['clients']], ['Do obsłużenia', $stats['awaiting']]] as [$label, $value])<div class="rounded-xl bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold text-[#063A60]">{{ $value }}</p></div>@endforeach
</div>
<div class="mt-8 grid gap-6 xl:grid-cols-[1fr_2fr]">
<section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-semibold">Statusy zamówień</h2><div class="mt-4 grid gap-3">@foreach(\App\Enums\OrderStatus::labels() as $status => $label)<div class="flex justify-between border-b pb-2 text-sm"><span>{{ $label }}</span><strong>{{ $ordersByStatus[$status] ?? 0 }}</strong></div>@endforeach</div></section>
<section class="rounded-xl bg-white p-5 shadow-sm"><div class="flex justify-between"><h2 class="font-semibold">Ostatnie zamówienia</h2><a class="text-sm text-[#D51A70]" href="{{ route('admin.orders.index') }}">Wszystkie</a></div><div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b text-slate-500"><th class="p-2">Numer</th><th class="p-2">Klient</th><th class="p-2">Status</th><th class="p-2">Suma</th></tr></thead><tbody>@foreach($recentOrders as $order)<tr class="border-b"><td class="p-2"><a class="font-medium text-[#063A60]" href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a></td><td class="p-2">{{ $order->customer_name }}</td><td class="p-2">{{ $order->status->label() }}</td><td class="p-2">{{ number_format((float) $order->total, 2, ',', ' ') }} zł</td></tr>@endforeach</tbody></table></div></section>
</div>
@endsection
