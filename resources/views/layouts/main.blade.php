<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'CopyCabana — drukarnia w Katowicach')</title>
  <meta name="description" content="@yield('description', 'CopyCabana — druk cyfrowy i oprawa prac w Katowicach. Prace dyplomowe i materiały dla firm.')">
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
  <link rel="stylesheet" href="{{ asset('css/concept.css') }}?v={{ filemtime(public_path('css/concept.css')) }}">
</head>
<body class="concept-dynamic concept-site {{ request()->routeIs('test.fonts') ? 'font-test-root' : '' }}" x-data="{ mobileNav: false }">
  <header class="cc-header">
    <div class="cc-container cc-header-inner">
      <a href="{{ route('home') }}" class="font-logo no-underline cc-logo">CopyCabana</a>
      <nav class="cc-nav" aria-label="Główna nawigacja">
        <a href="{{ route('home') }}">Start</a>
        <a href="{{ route('services.diploma') }}">Prace dyplomowe</a>
        <a href="{{ route('services.business') }}">Dla firm</a>
      </nav>
      <div class="cc-header-actions">
        <a href="{{ route('cart') }}" class="cc-header-icon" aria-label="Koszyk">
          <i class="fas fa-cart-shopping" aria-hidden="true"></i>
          <span class="cart-badge cc-cart-badge" aria-live="polite" style="display:none">0</span>
        </a>
        @auth
          @if(auth()->user()->isCustomer())
            <a href="{{ route('customer.dashboard') }}" class="cc-header-icon" aria-label="Moje konto"><i class="fas fa-user" aria-hidden="true"></i></a>
          @endif
        @else
          <a href="{{ route('customer.login') }}" class="cc-header-icon" aria-label="Zaloguj"><i class="fas fa-user" aria-hidden="true"></i></a>
        @endauth
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
      <a href="{{ route('home') }}">Start</a>
        <a href="{{ route('services.diploma') }}">Prace dyplomowe</a>
        <a href="{{ route('services.business') }}">Druk dla firm</a>
        <a href="{{ route('cart') }}">Koszyk</a>
      @auth
        @if(auth()->user()->isCustomer())
          <a href="{{ route('customer.dashboard') }}">Moje konto</a>
        @else
          <a href="{{ route('dashboard') }}">Panel administracyjny</a>
        @endif
      @else
        <a href="{{ route('customer.login') }}">Zaloguj</a>
      @endauth
    </div>
  </div>

  @yield('content')

  <footer class="cc-footer">
    <div class="cc-container cc-footer-inner">
      <a href="{{ route('home') }}" class="font-logo no-underline cc-logo">CopyCabana</a>
      <p>Drukarnia w centrum Katowic. Konfigurator prac dyplomowych i materiałów dla firm.</p>
      <nav class="cc-footer-nav">
        <a href="{{ route('services.diploma') }}">Prace dyplomowe</a>
        <a href="{{ route('services.business') }}">Druk dla firm</a>
        <a href="{{ route('privacy') }}">Polityka prywatności</a>
        <a href="{{ route('cookies') }}">Polityka cookies</a>
      </nav>
      <p class="cc-footer-note">CopyCabana · ul. Bankowa 11, 40-007 Katowice · biuro@copycabana.pl</p>
    </div>
  </footer>

  @include('components.cookie-consent')

  <script src="{{ asset('js/cart.js') }}"></script>
  <script src="{{ asset('js/concept.js') }}"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
