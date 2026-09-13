@extends('layouts.main')

@section('title', 'Kontakt — CopyCabana Drukarnia Katowice')
@section('description', 'Skontaktuj się z drukarnią CopyCabana w Katowicach. Telefon, email, mapa — ul. Bankowa 11.')

@section('content')
<main class="cc-page">
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Kontakt</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Jesteśmy w Katowicach.<em>Napisz albo zadzwoń.</em></h1>
        <p class="reveal reveal-delay-2">Masz plik, pytanie o termin albo niestandardowe zlecenie? Odezwij się do drukarni przy ul. Bankowej 11.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="tel:502293849" class="btn-magenta inline-block"><i class="fas fa-phone mr-2" aria-hidden="true"></i>502 293 849</a>
          <a href="mailto:biuro@copycabana.pl" class="btn-outline-light inline-block">biuro@copycabana.pl</a>
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

  <section class="cc-section cc-section--light">
    <div class="cc-container">
      <x-concept.section-heading label="Dane kontaktowe">
        <x-slot:lead>Najważniejsze informacje w jednym miejscu. Jeśli piszesz o zamówieniu, dodaj format, nakład i termin.</x-slot:lead>
        Skontaktuj się z pracownią.
      </x-concept.section-heading>

      <div class="cc-local-grid">
        <div class="cc-local-card reveal">
          <div class="cc-local-fact">
            <i class="fas fa-phone" aria-hidden="true"></i>
            <div><strong>Telefon</strong><em><a href="tel:502293849">502 293 849</a> · <a href="tel:504939094">504 939 094</a></em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-envelope" aria-hidden="true"></i>
            <div><strong>Email</strong><em><a href="mailto:biuro@copycabana.pl">biuro@copycabana.pl</a></em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-clock" aria-hidden="true"></i>
            <div><strong>Godziny otwarcia</strong><em>Pn-Pt: 8:00-16:00 · Sobota: 9:00-15:00</em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-location-dot" aria-hidden="true"></i>
            <div><strong>Adres</strong><em>ul. Bankowa 11, 40-007 Katowice</em></div>
          </div>
          <div class="cc-local-fact">
            <i class="fas fa-building" aria-hidden="true"></i>
            <div><strong>Dane firmy</strong><em>LITEKST Jarosław Lipiec · NIP 6342412192</em></div>
          </div>
          <p class="cc-local-note">Najprościej: opisz format, nakład i termin albo dołącz gotowy plik.</p>
        </div>

        <form id="formularz" method="POST" action="{{ route('contact.store') }}" class="cc-local-card reveal reveal-delay-1">
          @csrf
          <div class="cc-step-head">
            <span class="cc-step-num">?</span>
            <div>
              <p class="cc-section-label">Formularz</p>
              <h2>Napisz do nas</h2>
              <p>Opisz sprawę, a wiadomość trafi bezpośrednio do naszego biura.</p>
            </div>
          </div>

          @if (session('contact_message_sent'))
            <p class="cc-help-note"><i class="fas fa-check-circle mr-2" aria-hidden="true"></i>Dziękujemy! Odpowiemy najszybciej jak to możliwe.</p>
          @endif

          <div class="cc-field-grid">
            <div class="cc-field">
              <label for="contact-name">Imię</label>
              <input id="contact-name" name="name" type="text" value="{{ old('name') }}" placeholder="Jan Kowalski" autocomplete="name" required @error('name') aria-invalid="true" @enderror>
              @error('name')<p class="cc-help-note" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="cc-field">
              <label for="contact-email">Email</label>
              <input id="contact-email" name="email" type="email" value="{{ old('email') }}" placeholder="jan@example.com" autocomplete="email" required @error('email') aria-invalid="true" @enderror>
              @error('email')<p class="cc-help-note" role="alert">{{ $message }}</p>@enderror
            </div>
          </div>

          <div class="cc-field">
            <label for="contact-phone">Telefon (opcjonalnie)</label>
            <input id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="500 000 000" autocomplete="tel" @error('phone') aria-invalid="true" @enderror>
            @error('phone')<p class="cc-help-note" role="alert">{{ $message }}</p>@enderror
          </div>

          <div class="cc-field">
            <label for="contact-message">Wiadomość</label>
            <textarea id="contact-message" name="message" rows="5" placeholder="Opisz swoje zamówienie..." required @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
            @error('message')<p class="cc-help-note" role="alert">{{ $message }}</p>@enderror
          </div>

          <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
          <button type="submit" class="btn-magenta cc-contact-submit">Wyślij wiadomość <i class="fas fa-paper-plane ml-2" aria-hidden="true"></i></button>
        </form>
      </div>
    </div>
  </section>
</main>
@endsection
