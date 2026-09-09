@extends('layouts.main')

@section('title', 'Drukarnia CopyCabana w Katowicach — prace dyplomowe, dokumenty PDF, druk dla firm')
@section('description', 'Prace dyplomowe z oprawą, dokumenty PDF i materiały dla firm. Konfigurator online — CopyCabana, Katowice, ul. Bankowa 11.')

@section('content')
<main class="cc-page">
  {{-- 01 Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">CopyCabana · Drukarnia w Katowicach</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Ty masz plik.<em>My zajmiemy się drukiem.</em></h1>
        <p class="reveal reveal-delay-2">Prace dyplomowe, dokumenty i materiały dla firm. Wybierz, co chcesz wydrukować, i przejdź do zamawiania.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="{{ route('services.diploma') }}" class="btn-magenta inline-block">Wydrukuj i opraw pracę <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
          <a href="{{ route('services.business') }}" class="btn-geel inline-block">Zamów druk dla firmy</a>
        </div>
        <a href="{{ route('druk-pdf') }}" class="cc-hero-trio-link reveal reveal-delay-4">Chcę wydrukować dokumenty PDF →</a>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-hero-stack" aria-hidden="true">
          <div class="cc-hero-card cc-hero-card--a">
            <img src="{{ asset('images/carousel-6.jpg') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--b">
            <img src="{{ asset('images/produkty/product-03.png') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--c">
            <img src="{{ asset('images/produkty/product-02.png') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--tag">Od PDF do gotowej pracy</div>
        </div>
      </div>
    </div>
  </section>

  {{-- 02 Service slider --}}
  <x-concept.marquee :items="['Prace dyplomowe', 'Druk PDF', 'Wizytówki', 'Ulotki', 'Plakaty', 'Banery', 'Rollupy', 'Oprawa']" />

  {{-- 03 What are we printing --}}
  <section class="cc-section cc-section--light">
    <div class="cc-container">
      <x-concept.section-heading label="Wybór ścieżki">
        <x-slot:lead>Nie musisz rozumieć druku. Wybierz, co chcesz zrobić, a pokażemy momentalnie właściwy konfigurator.</x-slot:lead>
        Co dziś drukujemy?
      </x-concept.section-heading>

      <div class="cc-need-grid">
        <x-concept.need-card icon="fa-graduation-cap" title="Prace dyplomowe" cta="Wydrukuj i opraw pracę" href="{{ route('services.diploma') }}">
          Wydrukuj pracę i dobierz oprawę. Wszystkie ustawienia sprawdzisz w jednym podsumowaniu.
        </x-concept.need-card>
        <x-concept.need-card icon="fa-file-pdf" accent="yellow" title="Dokumenty PDF" cta="Wydrukuj PDF" href="{{ route('druk-pdf') }}">
          Materiały do nauki, instrukcje i codzienne dokumenty. Wybierz druk bez zbędnych dodatków.
        </x-concept.need-card>
        <x-concept.need-card icon="fa-building" accent="blue" title="Druk dla firm" cta="Zamów druk dla firmy" href="{{ route('services.business') }}">
          Wizytówki, ulotki, banery i materiały dla Twojej firmy. Kilka pozycji w jednym zamówieniu.
        </x-concept.need-card>
      </div>
    </div>
  </section>

  {{-- 04 Animated process --}}
  <section class="cc-section cc-section--dark cc-process" data-cc-process>
    <div class="cc-container">
      <x-concept.section-heading label="Jak to działa" dark align="center">
        <x-slot:lead>Od pliku do gotowego wydruku. Bez maili, bez zawijania, bez zgadywania ceny.</x-slot:lead>
        Od pliku do gotowego wydruku.
      </x-concept.section-heading>

      <div class="cc-process-steps">
        <article class="cc-process-step"><span class="num">01</span><h3>Dodajesz plik</h3><p>Przeciągasz PDF. System liczy strony i pokazuje raport.</p></article>
        <article class="cc-process-step"><span class="num">02</span><h3>Wybierasz opcje</h3><p>Kolor, strony kartki, egzemplarze i oprawa.</p></article>
        <article class="cc-process-step"><span class="num">03</span><h3>Widzisz cenę</h3><p>Kwota rośnie razem z wyborem — od razu, nie po zapytaniu.</p></article>
        <article class="cc-process-step"><span class="num">04</span><h3>Odbiór lub dostawa</h3><p>Katowice, paczkomat albo kurier. Termin osobno od produkcji.</p></article>
        <article class="cc-process-step"><span class="num">05</span><h3>My drukujemy</h3><p>Konfiguracja trafia do produkcji w jasnej formie.</p></article>
      </div>
    </div>
  </section>

  {{-- 05 Price + control --}}
  <section class="cc-section cc-section--light" id="cc-cena">
    <div class="cc-container cc-example-grid">
      <x-concept.section-heading label="Cena i kontrola">
        <x-slot:lead>Live podsumowanie po prawej nie jest prawdziwą wyceną. To demonstracja tego, jak będzie działać konfigurator: cena reaguje na każdą zmianę.</x-slot:lead>
        Wiesz, co zamawiasz.<br>Wiesz, ile płacisz.
      </x-concept.section-heading>

      <div x-data="ccExample()" class="cc-example-card reveal">
        <div class="cc-example-head">
          <strong><i class="fas fa-file-pdf mr-2 text-magenta" aria-hidden="true"></i>Praca.pdf — przykład</strong>
          <x-concept.demo-notice>Przykład poglądowy</x-concept.demo-notice>
        </div>
        <div class="cc-example-body">
          <div class="cc-example-field">
            <span>Strony / kolor</span>
            <div class="cc-seg">
              <template x-for="m in ['mieszane','czarno-biały']">
                <button type="button"
                        :class="{ 'is-active': colorMode === m }"
                        @click="setColor(m)"
                        x-text="m"></button>
              </template>
            </div>
          </div>
          <div class="cc-example-field">
            <span>Oprawa</span>
            <div class="cc-seg">
              <button type="button" :class="{ 'is-active': binding === 'miękka' }" @click="setBinding('miękka')">miękka · 0 zł</button>
              <button type="button" :class="{ 'is-active': binding === 'kanałowa' }" @click="setBinding('kanałowa')">kanałowa · 25 zł</button>
              <button type="button" :class="{ 'is-active': binding === 'twarda' }" @click="setBinding('twarda')">twarda · 50 zł</button>
            </div>
          </div>
          <div class="cc-example-field">
            <span>Egzemplarze <b class="text-magenta" x-text="copies"></b></span>
            <div class="cc-qty">
              <button type="button" @click="copies = copies > 1 ? copies - 1 : 1" aria-label="Mniej egzemplarzy"><i class="fas fa-minus" aria-hidden="true"></i></button>
              <strong x-text="copies"></strong>
              <button type="button" @click="copies = copies < 10 ? copies + 1 : 10" aria-label="Więcej egzemplarzy"><i class="fas fa-plus" aria-hidden="true"></i></button>
            </div>
          </div>
        </div>
        <div class="cc-example-target">
          <div class="cc-price-row"><span>Druk <small>x<template x-text="copies"></template></small></span><strong x-text="fmt(printTotal())"></strong></div>
          <div class="cc-price-row"><span>Oprawa <small x-text="binding"></small></span><strong x-text="fmt(bindings[binding])"></strong></div>
          <div class="cc-price-row"><span>Dostawa</span><strong class="cc-price-status">do wyboru</strong></div>
          <footer class="cc-summary-total"><span>Razem brutto</span><strong x-text="fmt(total())"></strong></footer>
        </div>
      </div>
    </div>
  </section>

  {{-- 06 Product / quality showcase --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading label="Jakość">
        <x-slot:lead>Nie pokazujemy stockowych rójek. Pokazujemy to, co faktycznie odbierzesz — produkt, jego fakturę i detale.</x-slot:lead>
        Zobacz, co odbierzesz.
      </x-concept.section-heading>

      <div class="cc-showcase">
        <a href="{{ route('services.diploma') }}" class="cc-showcase-card reveal">
          <img src="{{ asset('images/carousel-6.jpg') }}" alt="Oprawione prace dyplomowe">
          <div class="cc-showcase-meta"><strong>Oprawa prac</strong><span aria-hidden="true"><i class="fas fa-arrow-right"></i></span></div>
        </a>
        <a href="{{ route('services.business') }}" class="cc-showcase-card reveal reveal-delay-1">
          <img src="{{ asset('images/produkty/product-02.png') }}" alt="Wizytówki premium">
          <div class="cc-showcase-meta"><strong>Wizytówki</strong><span aria-hidden="true"><i class="fas fa-arrow-right"></i></span></div>
        </a>
        <a href="{{ route('services.business') }}" class="cc-showcase-card reveal reveal-delay-2">
          <img src="{{ asset('images/produkty/product-06.png') }}" alt="Materiały i banery dla firm">
          <div class="cc-showcase-meta"><strong>Materiały firmowe</strong><span aria-hidden="true"><i class="fas fa-arrow-right"></i></span></div>
        </a>
      </div>
    </div>
  </section>

  {{-- 07 Local trust --}}
  <section class="cc-section cc-section--light">
    <div class="cc-container">
      <x-concept.section-heading label="Lokalność">
        <x-slot:lead>Realna drukarnia w centrum Katowic. Odbiór osobisty, konkretne godziny i kontakt do ludzi, nie formularza.</x-slot:lead>
        Z Katowic.<br>Dla Twojej pracy i Twojej firmy.
      </x-concept.section-heading>

      <div class="cc-local-grid">
        <div class="cc-local-card reveal">
          <div class="cc-local-fact">
            <i class="fas fa-location-dot" aria-hidden="true"></i>
            <div><strong>Punkt odbioru</strong><em>ul. Bankowa 11, 40-007 Katowice</em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-clock" aria-hidden="true"></i>
            <div><strong>Godziny otwarcia</strong><em>Pn–Pt: 8:00–16:00 · Sobota: 9:00–15:00</em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-phone" aria-hidden="true"></i>
            <div><strong>Telefon</strong><em>502 293 849</em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-envelope" aria-hidden="true"></i>
            <div><strong>Email</strong><em>biuro@copycabana.pl</em></div>
          </div>
          <p class="cc-local-note">Nie pokazujemy wycenionych opinii ani liczników stron. Pokażemy je, gdy będą prawdziwe.</p>
          <a href="{{ route('services.diploma') }}" class="btn-magenta inline-block mt-4" style="width:fit-content">Sprawdź konfigurator pracy</a>
        </div>

        <div class="cc-local-photo reveal reveal-delay-1">
          <img src="{{ asset('images/carousel-6.jpg') }}" alt="Drukarnia CopyCabana w Katowicach — oprawione prace">
        </div>
      </div>
    </div>
  </section>

  {{-- 08 FAQ --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading label="FAQ">
        <x-slot:lead>Najczęstsze pytania przed wysłaniem pliku. Odpowiadamy prosto — bez grafomańskiego języka.</x-slot:lead>
        Zanim wyślesz plik.
      </x-concept.section-heading>

      <x-concept.faq-list :items="[
        ['q' => 'W jakim formacie wysłać plik?', 'a' => 'Najbezpieczniej kompletny PDF. Przed zamówieniem sprawdź stronnicowanie, kolejność i numerację stron.'],
        ['q' => 'Czy mogę wydrukować dokument dwustronnie?', 'a' => 'Tak. W konfiguratorze wybierzesz druk jednostronny lub dwustronny — z krótkim opisem, co to oznacza w praktyce.'],
        ['q' => 'Jak szybko będzie gotowe zamówienie?', 'a' => 'Standardowo rozmawiamy o realizacji w 24h. Konkretny termin pokażemy po wyborze pliku, oprawy i metody dostawy.'],
        ['q' => 'Czy mogę odebrać je w Katowicach?', 'a' => 'Tak. Odbiór osobisty działa w punkcie przy ul. Bankowej 11.'],
        ['q' => 'Czy wysyłacie zamówienia?', 'a' => 'Tak — paczkomat i kurier są opcjami roboczymi. Integrację i koszty potwierdzimy przed produkcją.'],
        ['q' => 'Czy zobaczę cenę przed złożeniem zamówienia?', 'a' => 'Tak, to punkt wyjścia tego projektu. Cena i termin mają być widoczne w podsumowaniu, zanim cokolwiek zatwierdzisz.'],
        ['q' => 'Co jeśli mój PDF ma błąd?', 'a' => 'Zamiast suchego komunikatu pokażemy przyczynę i następny możliwy krok. Kontrola techniczna nie obejmuje treści pracy ani wymagań wydziału.']
      ]" />
    </div>
  </section>

  {{-- 09 Final CTA --}}
  <x-concept.closing-cta heading="Masz już plik?"
    :items="[
      ['href' => route('services.diploma'), 'title' => 'Praca dyplomowa', 'note' => 'druk + oprawa + odbiór', 'icon' => 'fa-graduation-cap'],
      ['href' => route('druk-pdf'), 'title' => 'Dokument PDF', 'note' => 'szybki wydruk', 'icon' => 'fa-file-pdf'],
      ['href' => route('services.business'), 'title' => 'Druk dla firmy', 'note' => 'wizytówki, ulotki, banery', 'icon' => 'fa-building']
    ]" />
</main>
@endsection
