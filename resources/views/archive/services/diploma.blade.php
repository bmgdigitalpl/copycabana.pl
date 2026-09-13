@extends('layouts.site')

@section('title', 'Oprawa prac dyplomowych w Katowicach — CopyCabana')
@section('description', 'Druk i oprawa prac dyplomowych w Katowicach. Oprawa twarda, miękka i bindowanie oraz odbiór osobisty lub wysyłka.')

@section('content')
<main class="page-content">
  <section class="page-header">
    <p class="section-kicker text-geel">Dla studentów | Katowice</p>
    <h1>Oprawa prac dyplomowych</h1>
    <div class="underline"></div>
  </section>

  <section class="content-section">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
      <div class="content-copy">
        <p class="section-kicker">Najważniejsze informacje</p>
        <h2>Przygotuj pracę, wybierz oprawę i zamów bez zbędnego czekania</h2>
        <p>Drukujemy i oprawiamy prace licencjackie, magisterskie oraz inne prace dyplomowe. Możesz wybrać oprawę twardą, miękką albo bindowanie, a gotowe zamówienie odebrać w Katowicach.</p>
        <ul>
          <li>oprawa twarda standardowa i premium;</li>
          <li>oprawa miękka i bindowanie;</li>
          <li>druk czarno-biały lub kolorowy;</li>
          <li>odbiór osobisty przy ul. Bankowej 11;</li>
          <li>możliwość przygotowania zamówienia z wysyłką.</li>
        </ul>
        <a href="{{ route('services.diploma') }}" class="btn-magenta mt-6 inline-block">Skonfiguruj oprawę <i class="fas fa-arrow-right ml-2"></i></a>
      </div>
      <div class="content-grid !grid-cols-1">
        <div class="content-card quick-card-feature"><span class="card-icon"><i class="fas fa-clock"></i></span><h3>Termin 24h</h3><p>Sprawdź dostępny termin przy składaniu zamówienia. Czas zależy od wybranej opcji i poprawności pliku.</p></div>
        <div class="content-card"><span class="card-icon"><i class="fas fa-file-pdf"></i></span><h3>Plik PDF</h3><p>Najbezpieczniej przygotować jeden kompletny plik PDF z poprawnym formatem stron i marginesami.</p></div>
      </div>
    </div>
  </section>

  <section class="content-section content-section-muted">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="section-intro"><p class="section-kicker">Jak się przygotować</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Krótka lista przed wysłaniem pliku</h2></div>
      <div class="content-grid">
        <article class="content-card"><span class="card-icon">01</span><h3>Sprawdź strony</h3><p>Upewnij się, że kolejność, numeracja i orientacja stron są prawidłowe.</p></article>
        <article class="content-card"><span class="card-icon">02</span><h3>Wybierz wariant</h3><p>Dobierz rodzaj oprawy, kolor okładki i liczbę egzemplarzy.</p></article>
        <article class="content-card"><span class="card-icon">03</span><h3>Zamów online</h3><p>Dodaj konfigurację do koszyka, wybierz odbiór lub wysyłkę i przejdź do płatności.</p></article>
      </div>
    </div>
  </section>
</main>
@endsection
