<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'CopyCabana — koncepcja nowej strony')</title>
  <meta name="description" content="@yield('description', 'Koncepcja nowej strony CopyCabana: konfigurator druku prac dyplomowych, dokumentów PDF i materiałów dla firm.')">
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&amp;family=Roboto:wght@400;500;700&amp;family=Roboto+Slab:wght@500;600;700&amp;display=swap" rel="stylesheet">
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
            heading: ['Roboto Slab', 'serif'], body: ['Roboto', 'sans-serif'], logo: ['Caveat', 'cursive']
          }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dynamic-local-service.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dynamic-site.css') }}">
  <link rel="stylesheet" href="{{ asset('css/concept.css') }}">
</head>
<body class="concept-dynamic concept-site" x-data="{ mobileNav: false }">
  <div class="cc-ribbon" role="note">
    <div class="cc-container">
      <i class="fas fa-flask"></i>
      <span><strong>Koncepcja CopyCabana</strong> — eksperymentalny design. Nie przesyła plików ani nie składa zamówień.</span>
      <a href="{{ route('brand') }}" class="cc-ribbon-link"><i class="fas fa-arrow-left"></i> Wróć do /brand</a>
    </div>
  </div>

  <header class="cc-header">
    <div class="cc-container cc-header-inner">
      <a href="{{ route('concept.home') }}" class="font-logo no-underline cc-logo">CopyCabana</a>
      <span class="cc-badge">strona-koncepcja</span>
      <nav class="cc-nav" aria-label="Koncepcja strony">
        <a href="{{ route('concept.home') }}">Start</a>
        <a href="{{ route('concept.thesis') }}">Prace dyplomowe</a>
        <a href="{{ route('concept.pdf') }}">Druk PDF</a>
        <a href="{{ route('concept.b2b') }}">Dla firm</a>
      </nav>
      <div class="cc-header-actions">
        <a href="{{ route('home') }}" class="cc-link-outline">Strona główna</a>
        <button @click="mobileNav = true" class="cc-burger" aria-label="Otwórz menu">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>
  </header>

  <div class="mobile-nav-overlay" :class="{ 'open': mobileNav }" @click="mobileNav = false"></div>
  <div class="mobile-nav" :class="{ 'open': mobileNav }">
    <button @click="mobileNav = false" class="close-btn" aria-label="Zamknij menu"><i class="fas fa-times"></i></button>
    <div class="mt-12">
      <a href="{{ route('concept.home') }}">Start</a>
      <a href="{{ route('concept.thesis') }}">Prace dyplomowe</a>
      <a href="{{ route('concept.pdf') }}">Druk PDF</a>
      <a href="{{ route('concept.b2b') }}">Druk dla firm</a>
      <a href="{{ route('brand') }}">→ /brand</a>
      <a href="{{ route('home') }}">→ Strona główna (produkcja)</a>
    </div>
  </div>

  @yield('content')

  <footer class="cc-footer">
    <div class="cc-container cc-footer-inner">
      <a href="{{ route('concept.home') }}" class="font-logo no-underline cc-logo">CopyCabana</a>
      <p>Koncepcja nowej strony — projekt UX i frontendu. Do recenzji przed migracją na produkcyjne adresy.</p>
      <nav class="cc-footer-nav">
        <a href="{{ route('concept.thesis') }}">Prace dyplomowe</a>
        <a href="{{ route('concept.pdf') }}">Druk PDF</a>
        <a href="{{ route('concept.b2b') }}">Druk dla firm</a>
        <a href="{{ route('brand') }}">/brand</a>
        <a href="{{ route('home') }}">Strona główna</a>
      </nav>
      <p class="cc-footer-note">CopyCabana · ul. Bankowa 11, 40-007 Katowice · biuro@copycabana.pl</p>
    </div>
  </footer>

  <script src="{{ asset('js/concept.js') }}"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
