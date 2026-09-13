@extends('layouts.main')
@section('title', 'Moje konto | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-sm uppercase tracking-widest text-[#D51A70]">Portal klienta</p><h1 class="text-4xl font-bold">Witaj, {{ $client->name }}</h1></div>
    <form method="POST" action="{{ route('customer.logout') }}">@csrf<button class="text-sm text-slate-600 underline" type="submit">Wyloguj się</button></form>
  </div>
  @if(session('status'))<p class="mt-5 rounded-lg bg-green-50 p-3 text-green-800">{{ session('status') }}</p>@endif
  <div class="mt-8 grid gap-5 md:grid-cols-3">
    <a class="cc-local-card block" href="{{ route('customer.orders.index') }}"><p class="text-sm text-slate-500">Zamówienia</p><p class="mt-2 text-xl font-bold">Historia i płatności</p></a>
    <a class="cc-local-card block" href="{{ route('customer.quotes.index') }}"><p class="text-sm text-slate-500">Wyceny B2B</p><p class="mt-2 text-xl font-bold">Oferty i zapytania</p></a>
    <a class="cc-local-card block" href="{{ route('customer.profile.edit') }}"><p class="text-sm text-slate-500">Profil</p><p class="mt-2 text-xl font-bold">Dane kontaktowe</p></a>
  </div>
  <section class="mt-8 cc-local-card">
    <h2 class="text-2xl font-bold">Ostatnie zamówienie</h2>
    @if($latestOrder)
      <div class="mt-4 flex flex-wrap items-center justify-between gap-4"><div><a class="font-bold text-[#D51A70]" href="{{ route('customer.orders.show', $latestOrder->number) }}">{{ $latestOrder->number }}</a><p class="text-sm text-slate-500">{{ $latestOrder->created_at->format('d.m.Y H:i') }}</p></div><span>{{ $latestOrder->status->label() }}</span><strong>{{ number_format((float) $latestOrder->total, 2, ',', ' ') }} zł</strong></div>
    @else
      <p class="mt-3 text-slate-600">Nie masz jeszcze zamówień.</p>
    @endif
  </section>
  @if($openOffers->isNotEmpty())
    <section class="mt-8 cc-local-card"><h2 class="text-2xl font-bold">Oferty oczekujące na decyzję</h2><div class="mt-4 space-y-3">@foreach($openOffers as $quote)<a class="flex flex-wrap justify-between gap-3 border-b pb-3" href="{{ route('customer.quotes.show', $quote->reference) }}"><span>{{ $quote->reference }}</span><strong>{{ number_format((float) $quote->latestOffer->total, 2, ',', ' ') }} {{ $quote->latestOffer->currency }}</strong></a>@endforeach</div></section>
  @endif
</main>
@endsection
