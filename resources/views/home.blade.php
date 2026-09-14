@extends('layouts.main')

@section('title', 'Drukarnia CopyCabana w Katowicach — prace dyplomowe, dokumenty PDF, druk dla firm')
@section('description', 'Prace dyplomowe z oprawą, dokumenty PDF i materiały dla firm. Konfigurator online — CopyCabana, Katowice, ul. Bankowa 11.')

@section('content')
<main class="cc-page">
  {{-- 01 Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <h1 class="cc-hero-title reveal reveal-delay-1">Od pliku do<br><em>gotowego wydruku.</em></h1>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="{{ route('services.diploma') }}" class="btn-magenta inline-block">Skonfiguruj druk <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
          <a href="{{ route('services.business') }}" class="btn-geel inline-block">Druk dla firm</a>
        </div>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        @php($heroVariant = 'cards')
        @if($heroVariant === 'image')
          <div class="cc-hero-stack" aria-hidden="true">
            <div class="cc-hero-card cc-hero-card--single">
              <img src="{{ asset('images/hero-new.webp') }}" alt="">
            </div>
          </div>
        @else
          <div class="hero-stack" style="height: 100%; align-content: center;" aria-hidden="true">
            <article class="stack-card stack-card-main"><strong>24h</strong><p>Druk i oprawa prac dyplomowych. Dostępność terminu potwierdzamy dla Twojego pliku i wybranej oprawy.</p></article>
            <article class="stack-card"><strong>PDF</strong><p>Prześlij dokument mailem. Podaj liczbę egzemplarzy oraz druk kolorowy lub czarno-biały.</p></article>
            <article class="stack-card stack-card-accent"><strong>Katowice</strong><p>Odbiór w centrum miasta przy ul. Bankowej 11.</p></article>
            <article class="stack-card"><strong>Dla firm</strong><p>Wizytówki, ulotki, plakaty, rollupy, banery i więcej.</p></article>
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- 02 Service slider --}}
  <x-concept.marquee :items="['Prace dyplomowe', 'Druk PDF', 'Wizytówki', 'Ulotki', 'Plakaty', 'Banery', 'Rollupy', 'Oprawa']" />

  {{-- 03 Trust --}}
  <section class="cc-section cc-section--light">
    <div class="cc-container">
      <x-concept.section-heading>
        Dlaczego CopyCabana.
      </x-concept.section-heading>

      <div class="cc-need-grid">
        <article class="cc-need-card reveal">
          <span class="cc-need-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
          <div class="cc-need-body">
            <h3>22 lata doświadczenia</h3>
            <p>Od 2002 roku rozwijamy się od punktu ksero w drukarnię obsługującą studentów, klientów indywidualnych, firmy i agencje.</p>
          </div>
        </article>

        <article class="cc-need-card cc-need-card--yellow reveal reveal-delay-1">
          <span class="cc-need-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
          <div class="cc-need-body">
            <h3>Tysiące zadowolonych klientów</h3>
            <p>Realizujemy druk cyfrowy, offsetowy i wielkoformatowy: od dokumentów po materiały reklamowe.</p>
          </div>
        </article>

        <article class="cc-need-card cc-need-card--blue reveal reveal-delay-2">
          <span class="cc-need-icon"><i class="fas fa-location-dot" aria-hidden="true"></i></span>
          <div class="cc-need-body">
            <h3>Drukarnia na miejscu</h3>
            <p>Znajdziesz nas przy ul. Bankowej 11 w Katowicach. Odbierz zamówienie osobiście albo wybierz wysyłkę.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  {{-- 04 Animated process --}}
  <section class="cc-section cc-section--dark cc-process" data-cc-process>
    <div class="cc-container">
      <x-concept.section-heading dark>
        Od pliku do gotowego wydruku.
      </x-concept.section-heading>

      <div class="cc-process-steps">
        <article class="cc-process-step"><span class="num">01</span><h3>Konfigurujesz pracę</h3><p>Dodajesz plik, wybierasz ustawienia i od razu widzisz cenę.</p></article>
        <article class="cc-process-step"><span class="num">02</span><h3>Wybierasz odbiór</h3><p>Odbiór w Katowicach, paczkomat albo kurier. Termin produkcji oddzielamy od doręczenia.</p></article>
        <article class="cc-process-step"><span class="num">03</span><h3>My drukujemy</h3><p>Dostajesz jasne zlecenie, a my przygotowujemy Twój druk.</p></article>
      </div>
    </div>
  </section>

  {{-- 05 Gallery --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading>
        USŁUGI
      </x-concept.section-heading>

      <div class="cc-gallery">
        @foreach ($services as $item)
          <figure class="cc-gallery-card reveal">
            <img src="{{ $item['image'] }}" alt="{{ $item['alt'] }}">
            <figcaption>
              <span>{{ $item['title'] }}</span>
              <a href="{{ $item['href'] }}" class="btn-magenta cc-gallery-order">Zamów <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
            </figcaption>
          </figure>
        @endforeach
      </div>
    </div>
  </section>

  {{-- 06 Contact hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Kontakt</p>
        <h2 class="cc-hero-title reveal reveal-delay-1">Jesteśmy w Katowicach.<em>Napisz albo zadzwoń.</em></h2>
        <p class="reveal reveal-delay-2">Masz plik, pytanie o termin albo niestandardowe zlecenie? Odezwij się do drukarni przy ul. Bankowej 11.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="tel:502293849" class="btn-magenta inline-block"><i class="fas fa-phone mr-2" aria-hidden="true"></i>502 293 849</a>
          <a href="mailto:biuro@copycabana.pl" class="btn-outline-light inline-block">biuro@copycabana.pl</a>
        </div>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-local-map cc-contact-map">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2561.0!2d19.0294!3d50.2601!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4716ce1b1b1b1b1b%3A0x1b1b1b1b1b1b1b1b!2sBankowa%2011%2C%2040-007%20Katowice!5e0!3m2!1spl!2spl!4v1700000000000!5m2!1spl!2spl"
            title="Mapa dojazdu do CopyCabana przy ul. Bankowej 11 w Katowicach"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>
  </section>

  {{-- 07 FAQ --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading label="FAQ">
        <x-slot:lead>Najczęstsze pytania przed drukiem. Odpowiadamy prosto, tak jak przy ladzie w drukarni.</x-slot:lead>
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

  {{-- 08 Final CTA --}}
  <x-concept.closing-cta heading="Masz już plik?"
    :items="[
      ['href' => route('services.diploma'), 'title' => 'Praca dyplomowa', 'note' => 'druk + oprawa + odbiór', 'icon' => 'fa-graduation-cap'],
      ['href' => route('druk-pdf'), 'title' => 'Dokument PDF', 'note' => 'szybki wydruk', 'icon' => 'fa-file-pdf'],
      ['href' => route('services.business'), 'title' => 'Druk dla firmy', 'note' => 'wizytówki, ulotki, banery', 'icon' => 'fa-building']
    ]" />
</main>
@endsection
