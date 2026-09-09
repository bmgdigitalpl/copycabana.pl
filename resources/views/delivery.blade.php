@extends('layouts.site')

@section('title', 'Dostawa i odbiór zamówień — CopyCabana')
@section('description', 'Informacje o odbiorze osobistym w Katowicach i wysyłce zamówień CopyCabana.')

@section('content')
<main class="page-content">
  <section class="page-header"><p class="section-kicker text-geel">Po zakupie</p><h1>Dostawa i odbiór</h1><div class="underline"></div></section>
  <section class="content-section"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="section-intro"><p class="section-kicker">Dwie proste opcje</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Wybierz, jak chcesz odebrać zamówienie</h2></div><div class="content-grid"><article class="content-card"><span class="card-icon"><i class="fas fa-location-dot"></i></span><h3>Odbiór osobisty</h3><p>Odbierz gotowe zamówienie w CopyCabana przy ul. Bankowej 11 w Katowicach. Godziny odbioru i termin będą widoczne przy zamówieniu.</p></article><article class="content-card"><span class="card-icon"><i class="fas fa-truck"></i></span><h3>Wysyłka</h3><p>Jeśli wybierzesz wysyłkę, podasz adres dostawy w checkout. Dostępne metody i koszt pokażemy przed płatnością.</p></article><article class="content-card"><span class="card-icon"><i class="fas fa-credit-card"></i></span><h3>Płatność online</h3><p>Po podsumowaniu zamówienia przejdziesz do bezpiecznej płatności online. Realizację uruchamiamy po jej potwierdzeniu.</p></article></div></div></section>
  <section class="content-section content-section-muted"><div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8"><div class="content-copy"><p class="section-kicker">Przebieg</p><h2>Co dzieje się po zakupie?</h2><ul><li>otrzymujesz podsumowanie zamówienia;</li><li>operator potwierdza płatność;</li><li>przygotowujemy zamówienie zgodnie z konfiguracją;</li><li>informujemy o gotowości lub wysyłce.</li></ul></div><div class="content-card"><span class="card-icon"><i class="fas fa-circle-info"></i></span><h3>Ważne</h3><p>Dokładne ceny dostawy, terminy i dane odbioru zostaną ustawione przed uruchomieniem checkoutu produkcyjnego.</p></div></div></section>
</main>
@endsection
