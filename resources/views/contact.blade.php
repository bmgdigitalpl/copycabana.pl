<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kontakt — CopyCabana Drukarnia Katowice</title>
  <meta name="description" content="Skontaktuj się z drukarnią CopyCabana w Katowicach. Telefon, email, mapy — ul. Bankowa 11.">

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
    <h1>Kontakt</h1>
    <div class="underline"></div>
  </section>

  <!-- ========== CONTACT SECTION ========== -->
  <main class="section-wit py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-2 gap-12">

        <!-- Left: Map -->
        <div class="reveal">
          <div class="rounded-xl overflow-hidden shadow-lg h-full min-h-[400px]">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2561.0!2d19.0294!3d50.2601!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4716ce1b1b1b1b1b%3A0x1b1b1b1b1b1b1b1b!2sBankowa%2011%2C%2040-007%20Katowice!5e0!3m2!1spl!2spl!4v1700000000000!5m2!1spl!2spl"
              width="100%"
              height="100%"
              style="border:0; min-height: 400px;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
        </div>

        <!-- Right: Info Cards + Form -->
        <div>
          <!-- Contact Cards -->
          <div class="grid sm:grid-cols-2 gap-4 mb-8">
            <!-- Phone -->
            <div class="contact-card reveal">
              <div class="icon"><i class="fas fa-phone"></i></div>
              <div>
                <h4>Telefon</h4>
                <p><a href="tel:502293849">502 293 849</a></p>
                <p><a href="tel:504939094">504 939 094</a></p>
              </div>
            </div>
            <!-- Email -->
            <div class="contact-card reveal reveal-delay-1">
              <div class="icon"><i class="fas fa-envelope"></i></div>
              <div>
                <h4>Email</h4>
                <p><a href="mailto:biuro@copycabana.pl">biuro@copycabana.pl</a></p>
              </div>
            </div>
            <!-- Hours -->
            <div class="contact-card reveal reveal-delay-2">
              <div class="icon"><i class="fas fa-clock"></i></div>
              <div>
                <h4>Godziny otwarcia</h4>
                <p>Pn–Pt: 8:00–16:00</p>
                <p>Sobota: 9:00–15:00</p>
              </div>
            </div>
            <!-- Address -->
            <div class="contact-card reveal reveal-delay-3">
              <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <h4>Adres</h4>
                <p>ul. Bankowa 11<br>40-007 Katowice</p>
              </div>
            </div>
          </div>

          <!-- Contact Form -->
          <div class="bg-white border border-lichtgrijs rounded-xl p-6 sm:p-8 reveal">
            <h3 class="text-xl font-semibold text-donkerblauw mb-4">Napisz do nas</h3>
            <form x-data="{ submitted: false }" @submit.prevent="submitted = true" class="space-y-4">
              <div class="grid sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Imię</label>
                  <input type="text" class="form-input" placeholder="Jan Kowalski" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input type="email" class="form-input" placeholder="jan@example.com" required>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefon (opcjonalnie)</label>
                <input type="tel" class="form-input" placeholder="500 000 000">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Wiadomość</label>
                <textarea class="form-input" rows="5" placeholder="Opisz swoje zamówienie..." required></textarea>
              </div>

              <template x-if="!submitted">
                <button type="submit" class="btn-magenta w-full sm:w-auto">
                  Wyślij wiadomość <i class="fas fa-paper-plane ml-2"></i>
                </button>
              </template>
              <template x-if="submitted">
                <div class="flex items-center gap-2 text-groen font-semibold py-3">
                  <i class="fas fa-check-circle text-xl"></i>
                  Dziękujemy! Odpowiemy najszybciej jak to możliwe.
                </div>
              </template>
            </form>
          </div>
        </div>

      </div>
    </div>
  </main>

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
