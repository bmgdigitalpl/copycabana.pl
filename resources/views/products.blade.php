@extends('layouts.main')

@section('title', 'Produkty — CopyCabana Drukarnia Katowice')
@section('description', 'Oferta drukarni CopyCabana w Katowicach. Wybierz produkt i skonfiguruj zamówienie.')

@section('content')
<main class="cc-page">
    <section class="cc-hero cc-hero--compact">
        <div class="cc-container">
            <p class="cc-hero-overline">Katalog CopyCabana</p>
            <h1 class="cc-hero-title">Wybierz produkt.<em>Skonfiguruj po swojemu.</em></h1>
            <p>Sprawdź dostępne usługi, wybierz parametry i dodaj przygotowaną konfigurację do koszyka.</p>
        </div>
    </section>

    <section class="cc-container py-12">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-[#D51A70]">Oferta</p>
                <h2 class="mt-1 text-3xl font-bold text-[#063A60]">Produkty i usługi</h2>
            </div>
            <a href="{{ route('cart') }}" class="btn-magenta">Przejdź do koszyka <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($products as $product)
                <a href="{{ route('product', ['slug' => $product->slug]) }}" class="group rounded-2xl border border-slate-200 bg-white p-4 no-underline shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-xl bg-slate-50">
                        @if($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-contain transition group-hover:scale-105">
                        @else
                            <i class="fas fa-print text-4xl text-slate-300" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="pt-4">
                        <span class="text-xs font-semibold uppercase tracking-wide text-[#D51A70]">{{ $product->category }}</span>
                        <h3 class="mt-1 text-lg font-bold text-[#063A60]">{{ $product->name }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $product->description }}</p>
                        <p class="mt-4 text-sm font-semibold text-[#D51A70]">
                            @if($product->starting_price !== null)
                                Od {{ number_format((float) $product->starting_price, 2, ',', ' ') }} zł
                            @else
                                Wycena indywidualna
                            @endif
                        </p>
                    </div>
                </a>
            @empty
                <p class="text-slate-600">Katalog jest chwilowo niedostępny.</p>
            @endforelse
        </div>
    </section>
</main>
@endsection
