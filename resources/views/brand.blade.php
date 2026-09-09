@extends('layouts.site')

@section('title', 'CopyCabana Digital Playground — język wizualny marki')
@section('description', 'Interaktywny branding playground CopyCabana: typografia, kolory, CTA, karty, upload, stepper, sekcje, trust i motion.')

@section('content')
<main class="brand-playground" data-brand-playground>
  <section class="brand-hero">
    <div class="brand-orb brand-orb-magenta"></div>
    <div class="brand-orb brand-orb-yellow"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-28">
      <div class="brand-hero-copy reveal">
        <p class="brand-label">CopyCabana Digital Playground</p>
        <h1>Marka, która drukuje <span class="brand-word-static">z charakterem.</span></h1>
        <p>To nie jest sprzedażowy landing. To żywy katalog języka wizualnego: typografia, kolory, ruch, upload, karty, social proof i sekcje, z których później składamy lepsze funnele.</p>
        <div class="brand-actions">
          <a href="#brand-buttons" class="btn-magenta inline-block">Zobacz interakcje <i class="fas fa-arrow-right ml-2"></i></a>
          <a href="#brand-upload" class="btn-geel inline-block">Testuj upload</a>
        </div>
      </div>
      <div class="brand-print-stage reveal reveal-delay-2" data-tilt-card>
        <div class="brand-paper brand-paper-front">
          <span>PDF</span>
          <strong>86 stron</strong>
          <p>12 kolorowych</p>
        </div>
        <div class="brand-paper brand-paper-middle">druk</div>
        <div class="brand-paper brand-paper-back">oprawa</div>
      </div>
    </div>
  </section>

  <section class="brand-hero-slider" aria-label="CopyCabana motion samples" data-brand-slider>
    <div class="brand-slider-fade brand-slider-fade-left"></div>
    <div class="brand-slider-track">
      <span><i class="fas fa-file-pdf"></i> PDF do druku</span>
      <span><i class="fas fa-book-open"></i> oprawa pracy</span>
      <span><i class="fas fa-layer-group"></i> wizytówki premium</span>
      <span><i class="fas fa-bolt"></i> realizacja 24h</span>
      <span><i class="fas fa-map-marker-alt"></i> odbiór Katowice</span>
      <span><i class="fas fa-droplet"></i> strony kolorowe</span>
      <span><i class="fas fa-truck-fast"></i> dostawa</span>
      <span><i class="fas fa-shield-halved"></i> prywatność plików</span>
      <span><i class="fas fa-file-pdf"></i> PDF do druku</span>
      <span><i class="fas fa-book-open"></i> oprawa pracy</span>
      <span><i class="fas fa-layer-group"></i> wizytówki premium</span>
      <span><i class="fas fa-bolt"></i> realizacja 24h</span>
      <span><i class="fas fa-map-marker-alt"></i> odbiór Katowice</span>
      <span><i class="fas fa-droplet"></i> strony kolorowe</span>
      <span><i class="fas fa-truck-fast"></i> dostawa</span>
      <span><i class="fas fa-shield-halved"></i> prywatność plików</span>
    </div>
    <div class="brand-slider-fade brand-slider-fade-right"></div>
  </section>

  <section class="brand-section brand-typo" id="brand-typography">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">01 Typography</p>
      <div class="brand-type-grid">
        <div>
          <h2>Duże hasła. Krótkie decyzje.</h2>
          <p class="brand-lead">CopyCabana mówi prosto: wybierz produkt, wyślij PDF, poznaj cenę i termin.</p>
        </div>
        <div class="brand-type-stack">
          <p class="brand-sample-h1">Wgraj PDF</p>
          <p class="brand-sample-h2">Wybierz druk i oprawę</p>
          <p class="brand-sample-h3">Gotowe do odbioru w Katowicach</p>
          <span>Label / status / microcopy</span>
          <p>Plik przeszedł kontrolę techniczną. Nie sprawdzamy treści ani wymagań wydziału.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="brand-section brand-colors">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">02 Color System</p>
      <h2>Kolory pracują w UI, nie leżą jako swatche.</h2>
      <div class="brand-color-grid">
        <article class="brand-color-card brand-color-navy"><span>Donkerblauw</span><strong>#063A60</strong><p>Tło, nagłówek, zaufanie.</p></article>
        <article class="brand-color-card brand-color-blue"><span>Blauw</span><strong>#00456F</strong><p>Głębia i gradienty.</p></article>
        <article class="brand-color-card brand-color-magenta"><span>Magenta</span><strong>#D51A70</strong><p>Akcja i energia.</p></article>
        <article class="brand-color-card brand-color-yellow"><span>Geel</span><strong>#FFED00</strong><p>Uwaga, status, termin.</p></article>
        <article class="brand-color-card brand-color-green"><span>Groen</span><strong>#7FBF45</strong><p>Sukces i gotowość.</p></article>
      </div>
    </div>
  </section>

  <section class="brand-section" id="brand-buttons">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">03 Buttons & interactions</p>
      <h2>CTA jak zatwierdzenie produkcji.</h2>
      <div class="brand-button-grid">
        <a href="#brand-stepper" class="btn-magenta inline-block">Złóż zamówienie <i class="fas fa-arrow-right ml-2"></i></a>
        <a href="#brand-pricing" class="btn-geel inline-block">Wycena bez pliku</a>
        <a href="#brand-trust" class="btn-outline-light inline-block">Sprawdź drukarnię</a>
        <button class="brand-icon-button" type="button" aria-label="Zapisz konfigurację"><i class="fas fa-bookmark"></i></button>
        <button class="brand-upload-button" type="button"><i class="fas fa-cloud-arrow-up"></i> Wybierz plik PDF</button>
        <button class="brand-loading-button" type="button"><span></span> Analizujemy PDF</button>
        <button class="brand-success-button" type="button"><i class="fas fa-check"></i> Plik gotowy</button>
      </div>
    </div>
  </section>

  <section class="brand-section brand-dark">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label text-geel">04 Cards</p>
      <h2>Karty do usług, produktów, proofu i koszyka.</h2>
      <div class="brand-card-grid brand-card-showcase">
        <article class="brand-card brand-service-card"><span class="card-icon"><i class="fas fa-graduation-cap"></i></span><p class="brand-card-kicker">Service card</p><h3>Prace dyplomowe</h3><p>Krótka ścieżka: PDF, druk, oprawa i odbiór w Katowicach.</p><strong>Wydrukuj i opraw</strong></article>
        <article class="brand-card brand-product-card"><div class="brand-card-image"><img src="{{ asset('images/produkty/product-02.png') }}" alt="Wizytówki"></div><p class="brand-card-kicker">Product card</p><h3>Wizytówki</h3><p>Zdjęcie produktu, crop marks i spokojny premium shadow.</p></article>
        <article class="brand-card brand-quote-card"><i class="fas fa-quote-left"></i><p>„Szybko, konkretnie i bez stresu przed oddaniem pracy.”</p><strong>Studentka UŚ</strong></article>
        <article class="brand-card brand-stat-card"><p class="brand-card-kicker">Stat card</p><strong data-brand-counter="1200000">0</strong><span>wydrukowanych stron</span></article>
        <article class="brand-card brand-pricing-card"><p class="brand-card-kicker">Pricing card</p><span>od</span><strong>25 zł</strong><p>Oprawa miękka. Druk i dostawa pokazane jako osobne pozycje.</p><div><small>druk PDF</small><small>po analizie</small></div></article>
        <article class="brand-card brand-summary-card"><p class="brand-card-kicker">Order summary</p><h3>Podsumowanie</h3><ul><li><span>PDF</span><strong>86 stron</strong></li><li><span>Kolor</span><strong>12 stron</strong></li><li><span>Oprawa</span><strong>twarda</strong></li></ul><footer>Gotowe do wyceny</footer></article>
      </div>
    </div>
  </section>

  <section class="brand-section brand-section-gallery">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">05 Section styles</p>
      <h2>Te same klocki w różnych temperaturach.</h2>
      <div class="brand-section-samples">
        <article class="brand-surface brand-surface-clean"><h3>Clean</h3><p>Jasny blok do instrukcji i FAQ.</p></article>
        <article class="brand-surface brand-surface-brand"><h3>Brand</h3><p>Magenta + żółty do głównych decyzji.</p></article>
        <article class="brand-surface brand-surface-dark"><h3>Dark</h3><p>Granat na proces, zaufanie i checkout.</p></article>
        <article class="brand-surface brand-surface-editorial"><h3>86 stron. 12 kolorowych. 1 termin.</h3></article>
        <article class="brand-surface brand-surface-split"><img src="{{ asset('images/carousel-6.jpg') }}" alt="Drukarnia CopyCabana"><div><h3>Split screen</h3><p>Obraz procesu plus konkretna decyzja.</p></div></article>
      </div>
    </div>
  </section>

  <section class="brand-section brand-process" id="brand-process">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">06 Animated process</p>
      <h2>PDF → konfiguracja → druk → dostawa.</h2>
      <div class="brand-process-line">
        <article class="is-active"><span>01</span><h3>PDF</h3><p>Klient dodaje plik.</p></article>
        <article><span>02</span><h3>Konfiguracja</h3><p>System pokazuje opcje i koszt.</p></article>
        <article><span>03</span><h3>Druk</h3><p>Produkcja ma jasne parametry.</p></article>
        <article><span>04</span><h3>Dostawa</h3><p>Termin nie miesza się z wysyłką.</p></article>
      </div>
    </div>
  </section>

  <section class="brand-section" id="brand-upload">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
      <div>
        <p class="brand-label">07 Upload states</p>
        <h2>Upload jako centralny element marki.</h2>
        <p class="brand-lead">Ten komponent ma później obsłużyć drag & drop, analizę PDF, błędy i wynik wyceny. Na playgroundzie pokazuje stany bez backendu.</p>
      </div>
      <div class="brand-upload-card" data-brand-upload>
        <div class="brand-dropzone">
          <i class="fas fa-file-pdf"></i>
          <h3 data-upload-title>Przeciągnij PDF albo wybierz plik</h3>
          <p data-upload-status>Format PDF, limit do ustalenia przed produkcją.</p>
          <button class="brand-upload-button" type="button" data-upload-next>Symuluj upload</button>
        </div>
        <div class="brand-file-report">
          <div><span>Strony</span><strong>86</strong></div>
          <div><span>Kolor</span><strong>12</strong></div>
          <div><span>Status</span><strong data-upload-badge>Oczekuje</strong></div>
        </div>
      </div>
    </div>
  </section>

  <section class="brand-section brand-dark" id="brand-stepper">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label text-geel">08 Order stepper</p>
      <h2>Plik → druk → oprawa → dostawa → płatność.</h2>
      <div class="brand-stepper" data-brand-stepper>
        <div class="brand-stepper-nav">
          <button type="button" data-brand-step="0" class="is-active">Plik</button>
          <button type="button" data-brand-step="1">Druk</button>
          <button type="button" data-brand-step="2">Oprawa</button>
          <button type="button" data-brand-step="3">Dostawa</button>
          <button type="button" data-brand-step="4">Płatność</button>
        </div>
        <div class="brand-stepper-panels">
          <article data-brand-panel="0" class="is-active"><h3>Dodaj PDF</h3><p>Przesyłanie, analiza i komunikaty błędów.</p></article>
          <article data-brand-panel="1"><h3>Wybierz druk</h3><p>Jednostronnie, dwustronnie, kolor i papier.</p></article>
          <article data-brand-panel="2"><h3>Wybierz oprawę</h3><p>Zdjęcia, limity i podgląd napisu.</p></article>
          <article data-brand-panel="3"><h3>Termin i odbiór</h3><p>Odbiór w Katowicach albo dostawa.</p></article>
          <article data-brand-panel="4"><h3>Zamawiam i płacę</h3><p>Pełna kwota i zachowane zamówienie po błędzie płatności.</p></article>
        </div>
      </div>
    </div>
  </section>

  <section class="brand-section brand-image-showcase">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">09 Image / print showcase</p>
      <h2>Zdjęcia mają wyglądać jak gotowy produkt.</h2>
      <div class="brand-print-grid">
        <img src="{{ asset('images/carousel-6.jpg') }}" alt="Oprawione prace" data-tilt-card>
        <img src="{{ asset('images/produkty/product-03.png') }}" alt="Ulotki" data-tilt-card>
        <img src="{{ asset('images/produkty/product-06.png') }}" alt="Banery" data-tilt-card>
      </div>
    </div>
  </section>

  <section class="brand-section brand-testimonials">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">10 Testimonials</p>
      <h2>Proof, który nie wygląda jak doklejona opinia.</h2>
      <div class="brand-marquee" aria-label="Przykładowe opinie">
        <span>Terminowość</span><span>Jakość oprawy</span><span>Pomoc przy PDF</span><span>Odbiór w Katowicach</span><span>Powtarzalne zamówienia</span>
      </div>
      <div class="brand-testimonial-grid">
        <blockquote>„Dostałam jasną informację, co obejmuje cena i kiedy praca będzie gotowa.”</blockquote>
        <blockquote>„W firmie wracamy po te same ustawienia, bo proces jest przewidywalny.”</blockquote>
        <blockquote class="brand-big-quote">„Masz PDF? Resztą zajmiemy się my.”</blockquote>
      </div>
    </div>
  </section>

  <section class="brand-section brand-trust" id="brand-trust">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">11 Trust architecture</p>
      <h2>Wiarygodność tam, gdzie pojawia się ryzyko.</h2>
      <div class="brand-trust-grid">
        <article><i class="fas fa-location-dot"></i><strong>Katowice, Bankowa 11</strong><span>Realny punkt odbioru</span></article>
        <article><i class="fas fa-star"></i><strong>Google reviews</strong><span>Miejsce na prawdziwe opinie</span></article>
        <article><i class="fas fa-truck-fast"></i><strong>Odbiór / dostawa</strong><span>Oddzielamy termin produkcji od doręczenia</span></article>
        <article><i class="fas fa-lock"></i><strong>Prywatność plików</strong><span>Komunikat przy uploadzie</span></article>
      </div>
    </div>
  </section>

  <section class="brand-section" id="brand-pricing">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
      <div>
        <p class="brand-label">12 Pricing</p>
        <h2>Cena pokazana bez fałszywej pewności.</h2>
        <p class="brand-lead">Playground pokazuje sposób prezentacji ceny: założenia, warianty, brutto i jasny status wyceny.</p>
      </div>
      <div class="brand-price-panel">
        <div><span>Oprawa</span><strong>35,00 zł</strong></div>
        <div><span>Druk</span><strong>po analizie PDF</strong></div>
        <div><span>Dostawa</span><strong>do wyboru</strong></div>
        <footer><span>Razem</span><strong>pełna cena przed płatnością</strong></footer>
      </div>
    </div>
  </section>

  <section class="brand-section brand-dark">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
      <div>
        <p class="brand-label text-geel">13 FAQ styles</p>
        <h2>Minimalny accordion.</h2>
        <div class="faq-list brand-faq-mini">
          <details open><summary>Czy system liczy kolorowe strony?</summary><p>Docelowo tak, ale klasyfikacja musi zgadzać się z produkcją.</p></details>
          <details><summary>Czy klient musi zakładać konto?</summary><p>Nie. Konto może pojawić się po zakupie jako wygoda.</p></details>
        </div>
      </div>
      <div class="brand-faq-editorial">
        <span>Duży editorial FAQ</span>
        <h3>Co jeśli PDF ma błąd?</h3>
        <p>Nie ukrywamy problemu w czerwonym komunikacie. Pokazujemy przyczynę, rozwiązanie i następny możliwy krok.</p>
      </div>
    </div>
  </section>

  <section class="brand-section brand-b2b">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <p class="brand-label">14 B2B</p>
      <div class="brand-b2b-panel">
        <div><h2>Druk dla firm bez chaosu maili.</h2><p>Gotowy projekt, powtarzalne ustawienia, faktura i termin realizacji w jednym, spokojniejszym języku.</p></div>
        <div class="brand-logo-grid"><span>Wizytówki</span><span>Ulotki</span><span>Rollupy</span><span>Banery</span></div>
      </div>
    </div>
  </section>

  <section class="brand-closing">
    <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8">
      <p class="brand-label text-geel">15 Closing CTA</p>
      <h2>Masz PDF? Resztą zajmiemy się my.</h2>
      <a href="mailto:biuro@copycabana.pl" class="btn-geel mt-8 inline-block">Wyślij plik do wyceny <i class="fas fa-arrow-right ml-2"></i></a>
    </div>
  </section>
</main>
@endsection
