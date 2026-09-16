<section
  class="cc-cookie-consent"
  data-cookie-consent
  data-policy-version="{{ config('privacy.cookies_policy_version') }}"
  data-analytics-id="{{ config('services.google.analytics_id') }}"
  aria-label="Ustawienia cookies"
  hidden
>
  <div class="cc-cookie-consent__text">
    <p class="cc-cookie-consent__eyebrow">Prywatność</p>
    <h2>Spokojnie, tylko cookies.</h2>
    <p>Używamy niezbędnych cookies do działania strony. Analityczne włączymy tylko, jeśli się zgodzisz.</p>
    <a href="{{ route('cookies') }}">Polityka cookies</a>
  </div>

  <div class="cc-cookie-consent__choices" data-cookie-consent-choices hidden>
    <label class="cc-cookie-consent__toggle cc-cookie-consent__toggle--locked">
      <span>
        <strong>Niezbędne</strong>
        <small>Logowanie, koszyk, bezpieczeństwo.</small>
      </span>
      <input type="checkbox" checked disabled>
    </label>

    <label class="cc-cookie-consent__toggle">
      <span>
        <strong>Analityczne</strong>
        <small>Google Analytics po zgodzie.</small>
      </span>
      <input type="checkbox" data-cookie-analytics-toggle>
    </label>
  </div>

  <div class="cc-cookie-consent__actions">
    <button type="button" class="cc-cookie-consent__button cc-cookie-consent__button--primary" data-cookie-accept>Akceptuję</button>
    <button type="button" class="cc-cookie-consent__button" data-cookie-reject>Odrzucam</button>
    <button type="button" class="cc-cookie-consent__link" data-cookie-customize>Dostosuj</button>
    <button type="button" class="cc-cookie-consent__button cc-cookie-consent__button--primary" data-cookie-save hidden>Zapisz wybór</button>
  </div>
</section>
