@extends('layouts.site')

@section('title', 'Realizacje drukarni CopyCabana w Katowicach')
@section('description', 'Przykładowe realizacje CopyCabana: wizytówki, banery, billboardy i materiały drukowane dla klientów z Katowic i Śląska.')

@section('content')
<main class="page-content">
  <section class="page-header"><p class="section-kicker text-geel">Przykłady</p><h1>Realizacje</h1><div class="underline"></div></section>
  <section class="content-section"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="section-intro"><p class="section-kicker">Zobacz zakres</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Od małego formatu po duży efekt</h2><p class="mt-4 max-w-3xl text-base leading-7 text-slate-600">Poniżej pokazujemy przykładowe obszary realizacji. Galerię rzeczywistych zdjęć będziemy uzupełniać wraz z kolejnymi projektami.</p></div><div class="content-grid"><article class="content-card portfolio-card"><img src="{{ asset('images/produkty/product-02.png') }}" alt="Przykład wizytówek"><div><span class="section-kicker">Druk</span><h3>Wizytówki</h3><p>Materiały firmowe z doborem papieru i wykończenia.</p></div></article><article class="content-card portfolio-card"><img src="{{ asset('images/produkty/product-06.png') }}" alt="Przykład banera"><div><span class="section-kicker">Wielki format</span><h3>Banery</h3><p>Grafiki do ekspozycji zewnętrznej i wydarzeń.</p></div></article><article class="content-card portfolio-card"><img src="{{ asset('images/produkty/product-07.png') }}" alt="Przykład billboardu"><div><span class="section-kicker">Reklama zewnętrzna</span><h3>Billboardy</h3><p>Materiały wielkoformatowe do komunikacji w przestrzeni.</p></div></article></div></div></section>
  <section class="content-section content-section-muted"><div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8"><div><p class="section-kicker">Szukasz konkretnego rozwiązania?</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Opisz nam swój projekt</h2><p class="mt-4 max-w-2xl leading-8 text-slate-600">Format, nakład, termin i miejsce użycia wystarczą, żeby rozpocząć rozmowę o realizacji.</p></div><a href="{{ route('contact') }}" class="btn-magenta inline-block">Skontaktuj się <i class="fas fa-arrow-right ml-2"></i></a></div></section>
</main>
@endsection
