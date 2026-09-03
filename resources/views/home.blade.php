<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Druk i oprawa prac dyplomowych w Katowicach — CopyCabana</title>
  <meta name="description" content="CopyCabana w Katowicach: druk i oprawa prac dyplomowych w 24h oraz druk cyfrowy, offsetowy i wielkoformatowy dla firm.">
  <link rel="icon" href="images/favicon.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&amp;family=Caveat:wght@700&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            donkerblauw: '#063A60', blauw: '#00456F', geel: '#FFED00', wit: '#F5F5F5',
            lichtgrijs: '#D9DDE1', magenta: '#D51A70', groen: '#7FBF45', paars: '#6B3FA0'
          },
          fontFamily: {
            heading: ['Archivo', 'sans-serif'], body: ['Inter', 'sans-serif'], logo: ['Caveat', 'cursive']
          }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="css/dynamic-local-service.css">
  <link rel="stylesheet" href="css/dynamic-site.css">
  <script type="application/ld+json">
  {
    "@@context": "https://schema.org",
    "@@type": "LocalBusiness",
    "name": "CopyCabana",
    "url": "https://www.copycabana.pl/",
    "telephone": ["502293849", "504939094"],
    "email": "biuro@copycabana.pl",
    "address": { "@@type": "PostalAddress", "streetAddress": "Bankowa 11", "addressLocality": "Katowice", "postalCode": "40-007", "addressCountry": "PL" },
    "openingHours": ["Mo-Fr 08:00-16:00", "Sa 09:00-15:00"]
  }
  </script>
</head>
<body class="concept-dynamic bg-wit text-slate-800" x-data="{ mobileNav: false }">
  <div class="urgent-strip">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm font-bold sm:px-6 lg:px-8">
      <p><i class="fas fa-bolt mr-2"></i>Druk i oprawa prac dyplomowych w 24h</p>
      <p>Wyślij pracę mailem, odbierz w Katowicach, zapłać wygodnie.</p>
    </div>
  </div>

  <header class="border-b border-donkerblauw/10 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
      <a href="index.html" class="font-logo text-3xl text-donkerblauw no-underline">CopyCabana</a>
      <nav class="hidden items-center gap-8 md:flex">
        <a href="#uslugi" class="nav-small">Usługi</a>
        <a href="#jak-zamowic" class="nav-small">Jak zamówić</a>
        <a href="kontakt.html" class="nav-small">Kontakt</a>
        <a href="koszyk.html" class="header-phone no-underline"><i class="fas fa-shopping-cart mr-2"></i>Koszyk <span class="cart-badge ml-2" style="display:none">0</span></a>
      </nav>
      <button @click="mobileNav = true" class="border-none bg-transparent text-xl text-donkerblauw md:hidden" aria-label="Otwórz menu"><i class="fas fa-bars"></i></button>
    </div>
  </header>

  <div class="mobile-nav-overlay" :class="{ 'open': mobileNav }" @click="mobileNav = false"></div>
  <div class="mobile-nav" :class="{ 'open': mobileNav }">
    <button @click="mobileNav = false" class="close-btn" aria-label="Zamknij menu"><i class="fas fa-times"></i></button>
    <div class="mt-12">
      <a href="#uslugi" @click="mobileNav = false">Usługi</a>
      <a href="#jak-zamowic" @click="mobileNav = false">Jak zamówić</a>
      <a href="produkty.html">Produkty</a>
      <a href="kontakt.html">Kontakt</a>
      <a href="koszyk.html">Koszyk</a>
    </div>
  </div>

  <main>
    <section class="dynamic-hero">
      <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-20">
        <div class="hero-copy">
          <p class="label-strip">CopyCabana | Katowice</p>
          <h1 class="mt-4 max-w-2xl font-heading text-5xl font-extrabold uppercase leading-[0.95] text-donkerblauw sm:text-6xl">Druk i oprawa prac dyplomowych w 24h</h1>
          <p class="mt-6 max-w-xl text-lg leading-8 text-slate-700">Wyślij pracę mailem i zapłać przy odbiorze kartą, Blikiem lub gotówką. Realizujemy też druk cyfrowy, offsetowy i wielkoformatowy, od wizytówek po billboardy.</p>
          <div class="mt-8 flex flex-wrap gap-4">
            <a href="produkt.html?slug=oprawa_prac" class="btn-magenta inline-block">Skonfiguruj oprawę</a>
            <a href="#uslugi" class="btn-geel inline-block">Zobacz produkty</a>
            <a href="kontakt.html" class="btn-outline-light inline-block">Skontaktuj się</a>
          </div>
        </div>
        <div class="hero-stack">
          <article class="stack-card stack-card-main"><strong>24h</strong><p>Oprawa prac dyplomowych w twardej i miękkiej oprawie.</p></article>
          <article class="stack-card"><strong>Mail</strong><p>Wyślij plik i ustal szczegóły bez zbędnego czekania.</p></article>
          <article class="stack-card stack-card-accent"><strong>Katowice</strong><p>Odbiór w centrum miasta przy ul. Bankowej 11.</p></article>
          <article class="stack-card"><strong>Dla firm</strong><p>Wizytówki, ulotki, plakaty, rollupy, banery i więcej.</p></article>
        </div>
      </div>
    </section>

    <section class="benefit-belt">
      <div class="mx-auto grid max-w-7xl gap-4 px-4 py-5 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div><i class="fas fa-envelope"></i><span>Wyślij plik mailem</span></div>
        <div><i class="fas fa-credit-card"></i><span>Płatność przy odbiorze</span></div>
        <div><i class="fas fa-print"></i><span>Druk cyfrowy i offsetowy</span></div>
        <div><i class="fas fa-map-marker-alt"></i><span>Katowice, ul. Bankowa 11</span></div>
      </div>
    </section>

    <section id="uslugi" class="bg-white py-20">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="section-intro">
          <p class="section-kicker">Usługi</p>
          <h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Kompleksowa obsługa poligraficzna w Katowicach</h2>
          <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600">Od wizytówek po billboardy. Realizujemy projekty w małym i dużym nakładzie dla klientów indywidualnych, firm i agencji reklamowych.</p>
        </div>
        <div class="quick-grid">
          <a href="produkt.html?slug=oprawa_prac" class="quick-card quick-card-feature no-underline"><span>24h</span><h3>Oprawa prac</h3><p>Twarda i miękka oprawa prac dyplomowych.</p><strong class="quick-card-cta">Skonfiguruj i wyceń →</strong></a>
          <a href="produkt.html?slug=wizytowki" class="quick-card no-underline"><h3>Wizytówki</h3><p>Profesjonalne wizytówki dla Twojej firmy.</p></a>
          <a href="produkt.html?slug=ulotki" class="quick-card no-underline"><h3>Ulotki</h3><p>Ulotki reklamowe w różnych formatach.</p></a>
          <a href="produkt.html?slug=plakaty" class="quick-card no-underline"><h3>Plakaty</h3><p>Plakaty od A3 do B1.</p></a>
          <a href="produkt.html?slug=rollupy" class="quick-card no-underline"><h3>Rollupy</h3><p>Systemy wystawiennicze i grafiki na wymiar.</p></a>
          <a href="produkt.html?slug=banery" class="quick-card no-underline"><h3>Banery</h3><p>Banery wielkoformatowe na zamówienie.</p></a>
        </div>
        <div class="mt-10 text-center"><a href="produkty.html" class="btn-magenta inline-block">Zobacz całą ofertę <i class="fas fa-arrow-right ml-2"></i></a></div>
      </div>
    </section>

    <section id="jak-zamowic" class="process-section">
      <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="section-intro process-intro"><p class="section-kicker text-geel">Jak zamówić</p><h2 class="font-heading text-4xl font-bold uppercase text-white">Szybko, prosto, bez tarcia</h2><p class="mt-4 max-w-2xl text-base leading-7 text-white/70">Obsługujemy proste wydruki i większe realizacje reklamowe. Proces jest prosty od pierwszego kontaktu.</p></div>
        <div class="process-grid">
          <article><span>01</span><h3>Wyślij plik</h3><p>Wyślij pracę lub opisz zamówienie mailowo albo telefonicznie.</p></article>
          <article><span>02</span><h3>Potwierdzimy szczegóły</h3><p>Ustalimy termin, format, rodzaj druku i wykończenia.</p></article>
          <article><span>03</span><h3>Odbierz i zapłać</h3><p>Gotowe zamówienie odbierzesz wygodnie w Katowicach.</p></article>
        </div>
      </div>
    </section>

    <section class="bg-[#f7f8fa] py-20">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="section-intro"><p class="section-kicker">Popularne produkty</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Produkty, o które pytają najczęściej</h2></div>
        <div class="featured-row">
          <a href="produkt.html?slug=wizytowki" class="featured-card no-underline"><img src="images/produkty/product-02.png" alt="Wizytówki"><div><span class="badge badge-groen">Dostępne</span><h3>Wizytówki</h3><p>Profesjonalne wizytówki w różnych formatach i wykończeniach.</p></div></a>
          <a href="produkt.html?slug=banery" class="featured-card no-underline"><img src="images/produkty/product-06.png" alt="Banery"><div><span class="badge badge-groen">Dostępne</span><h3>Banery</h3><p>Banery wielkoformatowe z oczkami i wykończeniem.</p></div></a>
          <a href="produkt.html?slug=billboardy" class="featured-card no-underline"><img src="images/produkty/product-07.png" alt="Billboardy"><div><span class="badge badge-geel">Na zamówienie</span><h3>Billboardy</h3><p>Reklama zewnętrzna w dużym formacie na Śląsku.</p></div></a>
        </div>
      </div>
    </section>

    <section class="about-compact bg-white py-20">
      <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
        <img src="images/carousel-6.jpg" alt="Drukarnia CopyCabana" class="about-shot">
        <div><p class="section-kicker">O firmie</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Drukarnia w Katowicach od 2002 roku</h2><p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">Zaczynaliśmy jako niewielki punkt ksero. Dziś łączymy doświadczenie z drukiem cyfrowym, offsetowym i wielkoformatowym, obsługując klientów indywidualnych, firmy i agencje reklamowe.</p><a href="o-nas.html" class="btn-magenta mt-7 inline-block">Poznaj nas <i class="fas fa-arrow-right ml-2"></i></a></div>
      </div>
    </section>

    <section id="kontakt" class="contact-splash">
      <div class="mx-auto grid max-w-7xl gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
        <div><p class="section-kicker text-geel">Kontakt</p><h2 class="font-heading text-4xl font-bold uppercase text-white">Masz plik lub pomysł na druk?</h2><p class="mt-5 max-w-2xl text-base leading-8 text-white/72">Napisz lub zadzwoń. Pomożemy dobrać rozwiązanie i termin realizacji, od prac dyplomowych po większe projekty reklamowe.</p></div>
        <div class="contact-stack"><a href="tel:502293849" class="contact-pill no-underline"><i class="fas fa-phone"></i>502 293 849</a><a href="mailto:biuro@copycabana.pl" class="contact-pill no-underline"><i class="fas fa-envelope"></i>biuro@copycabana.pl</a><a href="kontakt.html" class="btn-geel inline-block text-center">Napisz do nas</a></div>
      </div>
    </section>
  </main>

  <footer class="dynamic-footer bg-white py-10">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8"><p class="text-sm text-slate-500">CopyCabana - druk cyfrowy, offsetowy i wielkoformatowy w centrum Katowic.</p><div class="footer-nav"><a href="produkty.html">Produkty</a><a href="o-nas.html">O nas</a><a href="kontakt.html">Kontakt</a><a href="koszyk.html">Koszyk</a></div></div>
  </footer>

  <script src="data/prices.js"></script>
  <script src="js/cart.js"></script>
  <script src="js/app.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
