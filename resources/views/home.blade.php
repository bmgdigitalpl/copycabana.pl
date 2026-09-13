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
        <h1 class="cc-hero-title reveal reveal-delay-1">Od pliku do<br><em>gotowego wydruku.</em></h1>
        <p class="reveal reveal-delay-2">Prace dyplomowe, dokumenty PDF i materiały firmowe. Wybierz usługę, ustaw szczegóły i poznaj kolejne kroki.</p>
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
            <p>Od 2002 roku pomagamy przygotowywać prace dyplomowe, dokumenty i materiały firmowe.</p>
          </div>
        </article>

        <article class="cc-need-card cc-need-card--yellow reveal reveal-delay-1">
          <span class="cc-need-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
          <div class="cc-need-body">
            <h3>Tysiące zadowolonych klientów</h3>
            <p>Obsługujemy studentów, klientów indywidualnych i firmy, które wracają do nas z kolejnymi zleceniami.</p>
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
      <x-concept.section-heading label="Jak to działa" dark align="center">
        <x-slot:lead>Od pliku do gotowego wydruku. Wybierasz, my dbamy o resztę.</x-slot:lead>
        Od pliku do gotowego wydruku.
      </x-concept.section-heading>

      <div class="cc-process-steps">
        <article class="cc-process-step"><span class="num">01</span><h3>Dodajesz plik</h3><p>Przeciągasz PDF. Sprawdzamy liczbę stron i podstawowe informacje.</p></article>
        <article class="cc-process-step"><span class="num">02</span><h3>Wybierasz ustawienia</h3><p>Kolor, druk jednostronny lub dwustronny, liczba egzemplarzy i oprawa.</p></article>
        <article class="cc-process-step"><span class="num">03</span><h3>Sprawdzasz cenę</h3><p>Podsumowanie pokazuje, z czego składa się kwota. Bez czekania na odpowiedź.</p></article>
        <article class="cc-process-step"><span class="num">04</span><h3>Wybierasz odbiór</h3><p>Odbiór w Katowicach, paczkomat albo kurier. Termin produkcji oddzielamy od doręczenia.</p></article>
        <article class="cc-process-step"><span class="num">05</span><h3>My drukujemy</h3><p>Dostajesz jasne zlecenie, a My przygotowujemy Twój druk.</p></article>
      </div>
    </div>
  </section>

  {{-- 05 Gallery --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading label="Galeria">
        <x-slot:lead>Wybrane realizacje z drukarni w Katowicach: od prac dyplomowych po materiały reklamowe.</x-slot:lead>
        Zobacz, co u nas powstaje.
      </x-concept.section-heading>

      <div class="cc-gallery">
        @foreach ([
          ['src' => 'images/carousel-6.jpg', 'alt' => 'Oprawione prace dyplomowe', 'title' => 'Prace dyplomowe z oprawą'],
          ['src' => 'images/produkty/ulotki.png', 'alt' => 'Ulotki reklamowe', 'title' => 'Ulotki'],
          ['src' => 'images/produkty/plakaty.png', 'alt' => 'Plakaty drukowane', 'title' => 'Plakaty'],
          ['src' => 'images/produkty/banery.png', 'alt' => 'Banery reklamowe', 'title' => 'Banery'],
          ['src' => 'images/produkty/rollupy.png', 'alt' => 'Rollupy reklamowe', 'title' => 'Rollupy'],
          ['src' => 'images/produkty/rysunki-plany-mapycad.png', 'alt' => 'Rysunki techniczne, plany i mapy CAD', 'title' => 'Rysunki, plany, CAD'],
        ] as $item)
          <figure class="cc-gallery-card reveal">
            <img src="{{ asset($item['src']) }}" alt="{{ $item['alt'] }}">
            <figcaption>{{ $item['title'] }}</figcaption>
          </figure>
        @endforeach
      </div>
    </div>
  </section>

  {{-- 06 Local trust --}}
  <section class="cc-section cc-section--light">
    <div class="cc-container">
      <x-concept.section-heading label="Lokalność">
        <x-slot:lead>Drukarnia przy ul. Bankowej 11. Możesz przyjść, zadzwonić albo umówić się osobiście.</x-slot:lead>
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
