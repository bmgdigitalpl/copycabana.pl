<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'CopyCabana — drukarnia w Katowicach')</title>
  <meta name="description" content="@yield('description', 'CopyCabana — druk cyfrowy, offsetowy i wielkoformatowy w Katowicach.')">
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
</head>
<body class="concept-dynamic" x-data="{ mobileNav: false }">
  <div class="urgent-strip">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm font-bold sm:px-6 lg:px-8">
      <p><i class="fas fa-bolt mr-2"></i>Druk i oprawa prac dyplomowych w 24h</p>
      <p>Katowice, Bankowa 11</p>
    </div>
  </div>

  <header class="border-b border-donkerblauw/10 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
      <a href="{{ route('home') }}" class="font-logo text-3xl text-donkerblauw no-underline">CopyCabana</a>
      <nav class="hidden items-center gap-8 md:flex">
        <a href="{{ route('products.index') }}" class="nav-small">Produkty</a>
        <a href="{{ route('services.diploma') }}" class="nav-small">Dla studentów</a>
        <a href="{{ route('services.business') }}" class="nav-small">Dla firm</a>
        <a href="{{ route('portfolio') }}" class="nav-small">Realizacje</a>
        <a href="{{ route('contact') }}" class="nav-small">Kontakt</a>
        <a href="{{ route('cart') }}" class="header-phone no-underline"><i class="fas fa-shopping-cart mr-2"></i>Koszyk</a>
      </nav>
      <button @click="mobileNav = true" class="border-none bg-transparent text-xl text-donkerblauw md:hidden" aria-label="Otwórz menu"><i class="fas fa-bars"></i></button>
    </div>
  </header>

  <div class="mobile-nav-overlay" :class="{ 'open': mobileNav }" @click="mobileNav = false"></div>
  <div class="mobile-nav" :class="{ 'open': mobileNav }">
    <button @click="mobileNav = false" class="close-btn" aria-label="Zamknij menu"><i class="fas fa-times"></i></button>
    <div class="mt-12">
      <a href="{{ route('home') }}">Strona główna</a>
      <a href="{{ route('products.index') }}">Produkty</a>
      <a href="{{ route('services.diploma') }}">Dla studentów</a>
      <a href="{{ route('services.business') }}">Dla firm</a>
      <a href="{{ route('portfolio') }}">Realizacje</a>
      <a href="{{ route('faq') }}">FAQ</a>
      <a href="{{ route('contact') }}">Kontakt</a>
      <a href="{{ route('cart') }}">Koszyk</a>
    </div>
  </div>

  @yield('content')

  <footer class="dynamic-footer bg-white py-10">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
      <p class="text-sm text-slate-500">CopyCabana - druk cyfrowy, offsetowy i wielkoformatowy w centrum Katowic.</p>
      <div class="footer-nav">
        <a href="{{ route('products.index') }}">Produkty</a>
        <a href="{{ route('services.diploma') }}">Dla studentów</a>
        <a href="{{ route('services.business') }}">Dla firm</a>
        <a href="{{ route('faq') }}">FAQ</a>
        <a href="{{ route('contact') }}">Kontakt</a>
        <a href="{{ route('cart') }}">Koszyk</a>
      </div>
    </div>
  </footer>

  <script src="{{ asset('data/prices.js') }}"></script>
  <script src="{{ asset('js/cart.js') }}"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
