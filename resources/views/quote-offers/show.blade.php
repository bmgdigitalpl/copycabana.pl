@extends('layouts.main')
@section('title', 'Oferta '.$offer->quoteRequest->reference.' | CopyCabana')
@section('description', 'Indywidualna oferta CopyCabana dla Twojego zapytania B2B.')
@section('content')
<main class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="rounded-2xl bg-white p-6 shadow-sm sm:p-10">
        <p class="text-sm font-semibold uppercase tracking-wide text-[#D51A70]">Oferta B2B</p>
        <h1 class="mt-2 text-3xl font-bold text-[#063A60]">{{ $offer->quoteRequest->reference }}</h1>
        <p class="mt-3 text-slate-600">Oferta dla {{ $offer->quoteRequest->company_name }}. Wersja {{ $offer->version }}.</p>

        @if($errors->any())
            <div class="mt-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_280px]">
            <section>
                <h2 class="text-lg font-semibold text-[#063A60]">Zakres</h2>
                <div class="mt-4 grid gap-3">
                    @foreach($offer->quoteRequest->items as $item)
                        <article class="rounded-xl border border-slate-200 p-4">
                            <div class="flex justify-between gap-4"><strong>{{ $item->product_name }}</strong><span class="text-sm text-slate-500">{{ $item->quantity }} szt.</span></div>
                            @if($item->configuration)<p class="mt-2 text-sm text-slate-600">@foreach($item->configuration as $key => $value){{ $key }}: {{ $value }}@if(!$loop->last) · @endif @endforeach</p>@endif
                            @if($item->help_wanted)<p class="mt-2 text-sm font-medium text-[#D51A70]">Projekt przygotuje pracownia.</p>@endif
                        </article>
                    @endforeach
                </div>
                @if($offer->notes)<div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-700"><strong>Informacja od pracowni</strong><p class="mt-2 whitespace-pre-line">{{ $offer->notes }}</p></div>@endif
            </section>

            <aside class="h-fit rounded-xl bg-[#063A60] p-5 text-white">
                <p class="text-sm text-white/70">Do zapłaty brutto</p>
                <p class="mt-1 text-3xl font-bold text-[#FFED00]">{{ number_format((float) $offer->total, 2, ',', ' ') }} {{ $offer->currency }}</p>
                <dl class="mt-5 grid gap-2 border-t border-white/20 pt-4 text-sm"><div class="flex justify-between gap-3"><dt class="text-white/70">Ważna do</dt><dd>{{ $offer->valid_until->format('d.m.Y') }}</dd></div><div class="flex justify-between gap-3"><dt class="text-white/70">Dostawa</dt><dd>{{ $offer->shipping_total > 0 ? number_format((float) $offer->shipping_total, 2, ',', ' ').' '.$offer->currency : 'odbiór osobisty' }}</dd></div></dl>
                @if($offer->status->value === 'sent' && ! $offer->valid_until->isBefore(today()))
                    <form method="post" action="{{ route('quote-offers.accept', ['token' => request()->route('token')]) }}" class="mt-6">@csrf<button class="w-full rounded-lg bg-[#D51A70] px-4 py-3 font-semibold text-white hover:bg-[#b5165f]">Akceptuję ofertę i przechodzę do płatności</button></form>
                @elseif($offer->status->value === 'accepted')
                    @php($latestPayment = $offer->order?->payments?->sortByDesc('id')->first())
                    @if($latestPayment?->status === 'failed')
                        <form method="post" action="{{ route('quote-offers.accept', ['token' => request()->route('token')]) }}" class="mt-6">
                            @csrf
                            <button class="w-full rounded-lg bg-[#D51A70] px-4 py-3 font-semibold text-white hover:bg-[#b5165f]">Ponów płatność</button>
                        </form>
                    @else
                        <p class="mt-6 rounded-lg bg-white/10 p-3 text-sm">Oferta została już zaakceptowana. Możesz kontynuować płatność z otrzymanego linku.</p>
                    @endif
                @elseif($offer->status->value === 'expired')
                    <p class="mt-6 rounded-lg bg-white/10 p-3 text-sm">Termin ważności oferty minął. Skontaktuj się z nami, aby otrzymać aktualną wycenę.</p>
                @else
                    <p class="mt-6 rounded-lg bg-white/10 p-3 text-sm">Ta oferta nie jest już dostępna.</p>
                @endif
            </aside>
        </div>
    </div>
</main>
@endsection
