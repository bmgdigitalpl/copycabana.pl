@extends('layouts.main')

@section('title', 'Drukarnia CopyCabana w Katowicach — prace dyplomowe, druk dla firm')
@section('description', 'Prace dyplomowe z oprawą i materiały dla firm. Konfigurator online — CopyCabana, Katowice, ul. Bankowa 11.')

@section('content')
<main class="cc-page">
  {{-- 01 Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <h1 class="cc-hero-title reveal reveal-delay-1">{{ $cms['hero']['title_before'] }}<br><em>{{ $cms['hero']['title_emphasis'] }}</em></h1>
        @if(! empty($cms['hero']['semantic_intro']))
          <p class="cc-hero-hint reveal reveal-delay-2"><i class="fas fa-circle-info" aria-hidden="true"></i> {{ $cms['hero']['semantic_intro'] }}</p>
        @endif
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="{{ $cms['hero']['primary_url'] }}" class="btn-magenta inline-block"><i class="fas {{ $cms['hero']['primary_icon'] }} mr-2" aria-hidden="true"></i>{{ $cms['hero']['primary_label'] }} <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
          <a href="{{ $cms['hero']['secondary_url'] }}" class="btn-geel inline-block"><i class="fas {{ $cms['hero']['secondary_icon'] }} mr-2" aria-hidden="true"></i>{{ $cms['hero']['secondary_label'] }}</a>
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
          <div class="hero-stack" style="height: 100%; align-content: center;" aria-label="Najważniejsze usługi CopyCabana">
            @foreach($cms['hero-cards']['items'] as $card)
              @php($cardClass = 'stack-card stack-card--'.($card['variant'] ?? 'cyan'))
              @if(! empty($card['url']))
                <a href="{{ $card['url'] }}" class="{{ $cardClass }} stack-card--linked"><strong>{{ $card['title'] }}</strong><p>{{ $card['body'] }}</p></a>
              @else
                <article class="{{ $cardClass }}"><strong>{{ $card['title'] }}</strong><p>{{ $card['body'] }}</p></article>
              @endif
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- 02 Service slider --}}
  <x-concept.marquee :items="[
    ['label' => 'Prace dyplomowe', 'href' => route('services.diploma')],
    ['label' => 'Wizytówki', 'href' => route('services.business', ['product' => 'wizytowki']).'#produkty'],
    ['label' => 'Ulotki', 'href' => route('services.business', ['product' => 'ulotki']).'#produkty'],
    ['label' => 'Plakaty', 'href' => route('services.business', ['product' => 'plakaty']).'#produkty'],
    ['label' => 'Banery', 'href' => route('services.business', ['product' => 'banery']).'#produkty'],
    ['label' => 'Rollupy', 'href' => route('services.business', ['product' => 'rollupy']).'#produkty'],
  ]" />

  {{-- 03 Trust --}}
  <section class="cc-section cc-section--light">
    <div class="cc-container">
      <x-concept.section-heading>
        {{ $cms['why']['heading'] }}
      </x-concept.section-heading>

      <div class="cc-trust-layout">
        <figure class="cc-trust-photo reveal reveal-delay-1">
          <img src="{{ asset('images/jarek-jacek.png') }}" alt="Jarek i Jacek z CopyCabana w drukarni w Katowicach">
        </figure>

        <div class="cc-trust-copy reveal reveal-delay-2">
          <p class="cc-trust-lead">{{ $cms['why']['lead_1'] }}</p>

          <p class="cc-trust-lead">{{ $cms['why']['lead_2'] }}</p>

          <ul class="cc-trust-list" aria-label="Najważniejsze powody, żeby wybrać CopyCabana">
            @foreach($cms['why']['items'] as $item)
              <li class="cc-trust-item @if(! empty($item['variant'])) cc-trust-item--{{ $item['variant'] }} @endif">
                <span class="cc-need-icon"><i class="fas {{ $item['icon'] }}" aria-hidden="true"></i></span>
                <div class="cc-need-body">
                  <h3>{{ $item['title'] }}</h3>
                  <p>{{ $item['body'] }}</p>
                </div>
              </li>
            @endforeach
          </ul>

          <div class="cc-trust-cta">
            @if(! empty($cms['why']['cta_primary_url']))
              <a href="{{ $cms['why']['cta_primary_url'] }}" class="btn-magenta inline-block">{{ $cms['why']['cta_primary_label'] }} <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
            @endif
            @if(! empty($cms['why']['cta_secondary_url']))
              <a href="{{ $cms['why']['cta_secondary_url'] }}" class="btn-geel inline-block">{{ $cms['why']['cta_secondary_label'] }}</a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- 04 Animated process --}}
  <section class="cc-section cc-section--dark cc-process" data-cc-process>
    <div class="cc-container">
      <x-concept.section-heading dark>
        {{ $cms['process']['heading'] }}
      </x-concept.section-heading>

      <div class="cc-process-steps">
        @foreach($cms['process']['items'] as $step)
          <article class="cc-process-step"><span class="num">{{ $step['number'] }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['body'] }}</p></article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- 05 Gallery --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading>
        {{ $cms['services']['heading'] }}
      </x-concept.section-heading>

      <div class="cc-gallery">
        @foreach ($cms['services']['items'] as $item)
          @if(! empty($item['url']))
            <div class="cc-gallery-card reveal">
              <a class="cc-gallery-media" href="{{ $item['url'] }}">
                <img src="{{ $item['image_url'] }}" alt="{{ $item['alt'] ?? $item['title'] }}">
                <span class="cc-gallery-title"><i class="fas {{ $item['icon'] ?? 'fa-print' }}" aria-hidden="true"></i>{{ $item['title'] }}</span>
              </a>
              <div class="cc-gallery-actions">
                <a class="btn-magenta cc-gallery-order" href="{{ $item['url'] }}"><i class="fas fa-cart-shopping mr-2" aria-hidden="true"></i>Zamów <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
                <button type="button" class="cc-gallery-help" aria-label="Zapytaj o {{ $item['accusative'] ?? $item['title'] }}" @click="$dispatch('open-service-chat', { title: @js($item['title']), topic: @js($item['accusative'] ?? $item['title']), questions: @js($item['questions'] ?? []) })">
                  <i class="fas fa-question" aria-hidden="true"></i>
                </button>
              </div>
            </div>
          @else
            <figure class="cc-gallery-card reveal">
              <img src="{{ $item['image_url'] }}" alt="{{ $item['alt'] ?? $item['title'] }}">
              <figcaption><span><i class="fas {{ $item['icon'] ?? 'fa-print' }}" aria-hidden="true"></i>{{ $item['title'] }}</span></figcaption>
            </figure>
          @endif
        @endforeach
      </div>
    </div>

    <x-concept.chat-widget />
  </section>

  {{-- 06 FAQ --}}
  <section class="cc-section cc-section--muted">
    <div class="cc-container">
      <x-concept.section-heading :label="$cms['faq']['label']">
        <x-slot:lead>{{ $cms['faq']['lead'] }}</x-slot:lead>
        {{ $cms['faq']['heading'] }}
      </x-concept.section-heading>

      <x-concept.faq-list :items="$cms['faq']['items']" />
    </div>
  </section>

  {{-- 07 Contact hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">{{ $cms['contact']['overline'] }}</p>
        <h2 class="cc-hero-title reveal reveal-delay-1">{{ $cms['contact']['title_before'] }}<em>{{ $cms['contact']['title_emphasis'] }}</em></h2>
        <p class="reveal reveal-delay-2">{{ $cms['contact']['body'] }}</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="{{ $cms['contact']['phone_url'] }}" class="btn-magenta inline-block"><i class="fas fa-phone mr-2" aria-hidden="true"></i>{{ $cms['contact']['phone_label'] }}</a>
          <a href="{{ $cms['contact']['email_url'] }}" class="btn-outline-light inline-block">{{ $cms['contact']['email_label'] }}</a>
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

  {{-- 08 Final CTA --}}
  <x-concept.closing-cta heading="Masz już plik?"
    :items="[
      ['href' => route('services.diploma'), 'title' => 'Praca dyplomowa', 'note' => 'druk + oprawa + odbiór', 'icon' => 'fa-graduation-cap'],
      ['href' => route('services.business'), 'title' => 'Druk dla firmy', 'note' => 'wizytówki, ulotki, banery', 'icon' => 'fa-building']
    ]" />
</main>
@endsection
