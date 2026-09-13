@extends('layouts.site')

@section('title', 'FAQ — CopyCabana drukarnia w Katowicach')
@section('description', 'Najczęstsze pytania o druk, oprawę prac dyplomowych, przygotowanie plików, odbiór osobisty i wysyłkę w CopyCabana.')

@section('content')
<main class="page-content">
  <section class="page-header"><p class="section-kicker text-geel">Pomoc przed zamówieniem</p><h1>Najczęstsze pytania</h1><div class="underline"></div></section>
  <section class="content-section">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="section-intro">
        <p class="section-kicker">FAQ</p>
        <h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Zanim wyślesz plik</h2>
      </div>

      <div class="faq-list">
        <details open>
          <summary>Jak przygotować plik do druku?</summary>
          <p>Najbezpieczniej przesłać kompletny plik PDF. Przed zamówieniem sprawdź format stron, kolejność, numerację, spady, marginesy bezpieczeństwa i poprawność treści.</p>
        </details>
        <details>
          <summary>Ile trwa oprawa pracy?</summary>
          <p>Standardowy komunikat oferty mówi o realizacji w 24h. Ostateczny termin zależy od wybranej oprawy, liczby egzemplarzy, poprawności pliku i aktualnego obciążenia.</p>
        </details>
        <details>
          <summary>Czy realizujecie małe nakłady?</summary>
          <p>Tak. Wiele usług realizujemy od kilku lub kilkudziesięciu sztuk, więc możesz zamówić pojedynczy projekt, próbkę albo krótką serię.</p>
        </details>
        <details>
          <summary>Czy mogę skonsultować zamówienie przed realizacją?</summary>
          <p>Tak. Przy pierwszej lub bardziej rozbudowanej realizacji pomagamy dobrać format, nakład, papier, wykończenie i technologię druku do celu projektu.</p>
        </details>
        <details>
          <summary>Czy moja firma lub agencja może z Wami współpracować?</summary>
          <p>Tak. Realizujemy materiały reklamowe dla firm i agencji, od wizytówek oraz ulotek po plakaty, banery i billboardy.</p>
        </details>
        <details>
          <summary>Czy mogę odebrać zamówienie osobiście?</summary>
          <p>Tak. Odbiór osobisty odbywa się w punkcie CopyCabana przy ul. Bankowej 11 w Katowicach.</p>
        </details>
        <details>
          <summary>Czy obsługujecie klientów spoza Katowic?</summary>
          <p>Tak. Pliki możesz przesłać zdalnie, a w konfiguratorze wybrać dostępny sposób dostawy. Szczegóły realizacji ustalimy mailowo lub telefonicznie.</p>
        </details>
        <details>
          <summary>Czy wystawiacie faktury?</summary>
          <p>Tak. Zaznacz potrzebę faktury podczas składania zamówienia i uzupełnij dane firmy w formularzu.</p>
        </details>
        <details>
          <summary>Co jeśli nie mam gotowego projektu?</summary>
          <p>Przy wybranych materiałach możemy pomóc przygotować projekt graficzny do druku. Opisz zakres, a dobierzemy właściwe rozwiązanie.</p>
        </details>
      </div>
    </div>
  </section>
  <section class="content-section content-section-muted"><div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8"><p class="section-kicker">Nie znalazłeś odpowiedzi?</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Napisz lub zadzwoń</h2><a href="{{ route('contact') }}" class="btn-magenta mt-7 inline-block">Skontaktuj się <i class="fas fa-arrow-right ml-2"></i></a></div></section>
</main>
@endsection
