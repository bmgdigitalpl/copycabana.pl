<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>O nas — CopyCabana Drukarnia Katowice</title>
  <meta name="description" content="Poznaj CopyCabana — drukarnia w Katowicach z ponad 20-letnim doświadczeniem w poligrafii.">

  <link rel="icon" href="images/favicon.png" type="image/png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&amp;family=Caveat:wght@700&amp;family=Inter:wght@400;500;600;700&amp;family=Montserrat:wght@300;400;600;700;800&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            donkerblauw: '#063A60',
            blauw: '#00456F',
            geel: '#FFED00',
            wit: '#F5F5F5',
            lichtgrijs: '#D9D9DD',
            magenta: '#D51A70',
            groen: '#7FBF45',
            paars: '#6B3FA0',
          },
          fontFamily: {
            heading: ['Montserrat', 'sans-serif'],
            body: ['Montserrat', 'sans-serif'],
            logo: ['Caveat', 'cursive'],
          },
        },
      },
    }
  </script>
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="css/dynamic-local-service.css">
  <link rel="stylesheet" href="css/dynamic-site.css">
</head>
<body class="concept-dynamic" x-data="{ mobileNav: false }">

  <!-- ========== NAVBAR ========== -->
  <nav id="navbar" class="navbar fixed top-0 left-0 right-0 z-50 bg-donkerblauw/95 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
      <a href="index.html" class="font-logo text-2xl text-geel no-underline">CopyCabana</a>
      <div class="hidden md:flex items-center gap-8">
        <a href="index.html" class="nav-link text-white text-sm no-underline">Home</a>
        <a href="produkty.html" class="nav-link text-white text-sm no-underline">Produkty</a>
        <a href="o-nas.html" class="nav-link text-white text-sm no-underline">O nas</a>
        <a href="kontakt.html" class="nav-link text-white text-sm no-underline">Kontakt</a>
        <a href="koszyk.html" class="nav-link text-white text-sm no-underline relative">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-badge absolute -top-2 -right-3 bg-magenta text-white text-xs w-5 h-5 rounded-full flex items-center justify-center" style="display:none">0</span>
        </a>
      </div>
      <button @click="mobileNav = true" class="md:hidden text-white text-xl bg-transparent border-none cursor-pointer">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </nav>

  <!-- ========== MOBILE NAV ========== -->
  <div class="mobile-nav-overlay" :class="{ 'open': mobileNav }" @click="mobileNav = false"></div>
  <div class="mobile-nav" :class="{ 'open': mobileNav }">
    <button @click="mobileNav = false" class="close-btn"><i class="fas fa-times"></i></button>
    <div class="mt-12">
      <a href="index.html">Home</a>
      <a href="produkty.html">Produkty</a>
      <a href="o-nas.html">O nas</a>
      <a href="kontakt.html">Kontakt</a>
      <a href="koszyk.html">Koszyk</a>
    </div>
  </div>

  <!-- ========== PAGE HEADER ========== -->
  <section class="page-header mt-16">
    <h1>O nas</h1>
    <div class="underline"></div>
  </section>

  <!-- ========== STORY SECTION ========== -->
  <main class="section-wit py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="reveal">
        <h2 class="text-3xl font-light text-donkerblauw mb-6 text-center">Drukarnia Katowice z ponad 20-letnim doświadczeniem</h2>
        <div class="space-y-4 text-gray-600 leading-relaxed text-center">
          <p>
            Działając od 2002 roku — początkowo jako niewielki punkt ksero, na przestrzeni lat nabyliśmy dużo doświadczenia w branży, dzięki czemu klienci zawsze są zadowoleni z efektów naszej pracy.
          </p>
          <p>
            Nastawienie na stały rozwój i poznawanie najnowszych technik poligraficznych pozwoliło nam poszerzyć naszą ofertę na przykład o druk offsetowy. Wcześniej obsługiwaliśmy głównie klientów z uczelni wyższych, oferując tradycyjne ksero czy oprawę i bindowanie prac dyplomowych.
          </p>
          <p>
            Obecnie dzięki znacznie poszerzonemu zakresowi usług poligraficznych naszej katowickiej drukarni, na współpracę z nami decydują się również agencje reklamowe oraz różne przedsiębiorstwa.
          </p>
          <p>
            Jesteśmy w stanie zrealizować różne projekty zarówno w małym, jak i dużym nakładzie (dzięki technologii offsetowej). Jakość gotowej realizacji jest doskonała dzięki pracy na nowoczesnym specjalistycznym sprzęcie.
          </p>
        </div>
      </div>
    </div>
  </main>

  <!-- ========== VALUES SECTION ========== -->
  <section class="section-lichtgrijs py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-center text-3xl font-light text-donkerblauw mb-10 reveal">Nasze wartości</h2>
      <div class="grid sm:grid-cols-3 gap-6">
        <!-- Quality -->
        <div class="value-card reveal">
          <div class="icon bg-magenta/10 text-magenta"><i class="fas fa-award"></i></div>
          <h3>Jakość</h3>
          <p>Pracujemy na nowoczesnym sprzęcie, aby każdy produkt prezentował się profesjonalnie i spełniał najwyższe standardy.</p>
        </div>
        <!-- Speed -->
        <div class="value-card reveal reveal-delay-1">
          <div class="icon bg-groen/10 text-groen"><i class="fas fa-bolt"></i></div>
          <h3>Szybkość</h3>
          <p>Oprawa i druk prac dyplomowych w 24h. Realizujemy zamówienia terminowo, niezależnie od skali.</p>
        </div>
        <!-- Flexibility -->
        <div class="value-card reveal reveal-delay-2">
          <div class="icon bg-paars/10 text-paars"><i class="fas fa-handshake"></i></div>
          <h3>Elastyczność</h3>
          <p>Dopasowujemy ofertę do realnych potrzeb klienta — od prostych wydruków po zaawansowane materiały reklamowe.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== TIMELINE ========== -->
  <section class="section-wit py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-center text-3xl font-light text-donkerblauw mb-10 reveal">Nasza historia</h2>
      <div class="timeline">
        <div class="timeline-item reveal">
          <div class="year">2002</div>
          <h4>Początki jako punkt ksero</h4>
          <p>Start działalności w centrum Katowic — obsługa klientów indywidualnych i studentów z okolicznych uczelni.</p>
        </div>
        <div class="timeline-item reveal reveal-delay-1">
          <div class="year">2008</div>
          <h4>Poszerzenie oferty</h4>
          <p>Dodanie usług poligraficznych: druk cyfrowy, wizytówki, ulotki i oprawa prac dyplomowych.</p>
        </div>
        <div class="timeline-item reveal reveal-delay-2">
          <div class="year">2014</div>
          <h4>Wejście w druk offsetowy</h4>
          <p>Inwestycja w technologię offsetową — możliwość realizacji dużych nakładów dla firm i agencji reklamowych.</p>
        </div>
        <div class="timeline-item reveal reveal-delay-3">
          <div class="year">2018</div>
          <h4>Druk wielkoformatowy</h4>
          <p>Rozszerzenie parku maszynowego o druk wielkoformatowy: banery, billboardy, rollupy, fototapety.</p>
        </div>
        <div class="timeline-item reveal reveal-delay-4">
          <div class="year">Dziś</div>
          <h4>Kompleksowa obsługa poligraficzna</h4>
          <p>Pełen zakres usług drukarskich — od ksero po zaawansowane projekty reklamowe dla klientów z całego Śląska.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== CTA BANNER ========== -->
  <section class="section-donkerblauw py-12">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h2 class="text-2xl sm:text-3xl font-light text-white mb-4 reveal">Skontaktuj się z nami</h2>
      <p class="text-white/70 mb-6 reveal reveal-delay-1">Pomożemy dobrać najlepsze rozwiązanie dla Twojego projektu.</p>
      <a href="kontakt.html" class="btn-geel inline-block reveal reveal-delay-2">
        Napisz do nas <i class="fas fa-arrow-right ml-2"></i>
      </a>
    </div>
  </section>

  <!-- ========== FOOTER ========== -->
  <footer class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <div>
          <a href="index.html" class="font-logo text-2xl text-geel no-underline block mb-4">CopyCabana</a>
          <div class="flex flex-col gap-2">
            <a href="index.html">Home</a>
            <a href="produkty.html">Produkty</a>
            <a href="o-nas.html">O nas</a>
            <a href="kontakt.html">Kontakt</a>
          </div>
        </div>
        <div>
          <h4 class="text-geel font-semibold text-sm mb-4">Popularne produkty</h4>
          <div class="flex flex-col gap-2">
            <a href="produkt.html?slug=wizytowki">Wizytówki</a>
            <a href="produkt.html?slug=ulotki">Ulotki</a>
            <a href="produkt.html?slug=plakaty">Plakaty</a>
            <a href="produkt.html?slug=banery">Banery</a>
            <a href="produkt.html?slug=oprawa_prac">Oprawa prac</a>
          </div>
        </div>
        <div>
          <h4 class="text-geel font-semibold text-sm mb-4">Kontakt</h4>
          <div class="flex flex-col gap-2 text-white/75 text-sm">
            <p><i class="fas fa-map-marker-alt mr-2 text-geel"></i>ul. Bankowa 11, 40-007 Katowice</p>
            <p><i class="fas fa-phone mr-2 text-geel"></i><a href="tel:502293849">502 293 849</a> / <a href="tel:504939094">504 939 094</a></p>
            <p><i class="fas fa-envelope mr-2 text-geel"></i><a href="mailto:biuro@copycabana.pl">biuro@copycabana.pl</a></p>
            <p><i class="fas fa-clock mr-2 text-geel"></i>Pn–Pt 8:00–16:00, Sob 9:00–15:00</p>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="max-w-7xl mx-auto px-4">&copy; 2024 CopyCabana.pl — Wszelkie prawa zastrzeżone.</div>
    </div>
  </footer>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="data/prices.js"></script>
  <script src="js/cart.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
