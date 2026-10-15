{{-- Shared <head> SEO: description, robots, canonical, Open Graph, Twitter card, favicons. Pages set it through @section('title'), ('description'), ('robots'), ('og_image'), ('og_type'). --}}
@php
  $seoTitle = ($seoTitleOverride ?? null) ?: trim($__env->yieldContent('title')) ?: 'CopyCabana — drukarnia w Katowicach';
  $seoDescription = ($seoDescriptionOverride ?? null) ?: trim($__env->yieldContent('description')) ?: 'CopyCabana — druk cyfrowy i oprawa prac w Katowicach. Prace dyplomowe i materiały dla firm.';
  $seoPrivateRoutes = ['cart', 'checkout.*', 'customer.*', 'quote-offers.*', 'local-payments.*', 'verification.*', 'login', 'logout', 'password.*', 'register', 'two-factor.*', 'dashboard', 'admin.*', 'brand', 'brand.*', 'test.*'];
  $seoRobots = ($forceRobots ?? null) ?: trim($__env->yieldContent('robots')) ?: (request()->routeIs(...$seoPrivateRoutes) ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1');
  $seoIndexable = ! str_contains($seoRobots, 'noindex');
  $seoUrl = url()->current();
  $seoImage = trim($__env->yieldContent('og_image')) ?: asset('images/og-default.png');
  $seoType = trim($__env->yieldContent('og_type')) ?: 'website';
@endphp
  <meta name="description" content="{{ $seoDescription }}">
  <meta name="robots" content="{{ $seoRobots }}">
@if ($seoIndexable)
  <link rel="canonical" href="{{ $seoUrl }}">
  <meta property="og:locale" content="pl_PL">
  <meta property="og:site_name" content="{{ config('business.company.name') }}">
  <meta property="og:type" content="{{ $seoType }}">
  <meta property="og:title" content="{{ $seoTitle }}">
  <meta property="og:description" content="{{ $seoDescription }}">
  <meta property="og:url" content="{{ $seoUrl }}">
  <meta property="og:image" content="{{ $seoImage }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="CopyCabana — drukarnia w Katowicach">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $seoTitle }}">
  <meta name="twitter:description" content="{{ $seoDescription }}">
  <meta name="twitter:image" content="{{ $seoImage }}">
@endif
  <meta name="theme-color" content="#063A60">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
  <link rel="icon" href="{{ asset('favicon-16x16.png') }}" type="image/png" sizes="16x16">
  <link rel="icon" href="{{ asset('favicon-48x48.png') }}" type="image/png" sizes="48x48">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
@stack('structured-data')
