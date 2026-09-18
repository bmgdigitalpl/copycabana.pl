@extends('layouts.main')

@section('title', 'Druk dla firm — konfigurator zleceń | CopyCabana')
@section('description', 'Wizytówki, ulotki, plakaty, banery, rollupy i dokumenty w jednym zamówieniu. Konfigurator druku dla firm — CopyCabana, Katowice.')

@section('content')
<script>
  window.copyCabanaB2bCatalog = {{ Illuminate\Support\Js::from($b2bCatalog) }};
  window.copyCabanaB2bDeliveries = {{ Illuminate\Support\Js::from($b2bDeliveries) }};
</script>
<main class="cc-page">
  {{-- Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-container--wide cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Druk dla firm</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Wiele produktów, <em>jedna wycena.</em></h1>
        <p class="reveal reveal-delay-2">Wizytówki, ulotki, plakaty, banery, rollupy i dokumenty dla firm oraz agencji. Układasz wiele pozycji w jednym zapytaniu, a my wyceniamy całość.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="#produkty" @click.prevent="ccGo('#produkty')" class="btn-magenta inline-block">Zacznij od produktu <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
          <a href="{{ route('contact') }}" class="btn-outline-light inline-block">Napisz o niestandardowym zleceniu</a>
        </div>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-hero-stack" aria-hidden="true">
          <div class="cc-hero-card cc-hero-card--single">
            <img src="{{ asset('images/hero-new.webp') }}" alt="">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Configurator --}}
  <div x-data="ccB2bConfigurator()" class="cc-container cc-container--wide cc-config cc-config-page">
    <div class="cc-config-main">
      <div class="cc-steps">

        {{-- Step 01 — Produkty --}}
        <section class="cc-step" id="produkty">
          <div class="cc-step-head">
            <span class="cc-step-num">01</span>
            <div><h2>Wybierz pozycje do zamówienia.</h2><p>Zaczyna się od produktu. Dodajemy każdą pozycję osobno, więc nic się nie nadpisze.</p></div>
          </div>

          <div class="cc-b2b-categories" aria-label="Kategorie usług">
            <template x-for="category in categories()" :key="category">
              <button type="button" class="cc-b2b-category" :class="activeCategory === category ? 'is-active' : ''" @click="activeCategory = category" x-text="categoryLabel(category)"></button>
            </template>
          </div>

          <div class="cc-b2b-grid">
            <template x-for="p in visibleProducts()" :key="p.id">
               <button type="button" class="cc-b2b-card"
                       :class="selected === p.id ? 'is-selected' : ''"
                       @click="selectProduct(p.id)">
                 <span class="cc-b2b-check"><i class="fas fa-check" aria-hidden="true"></i></span>
                 <template x-if="p.image">
                   <span class="cc-b2b-card-image"><img :src="p.image" :alt="p.name" loading="lazy"></span>
                 </template>
                 <i :class="'fas ' + p.icon" class="cc-b2b-icon" aria-hidden="true"></i>
                <span class="cc-b2b-card-body">
                  <strong x-text="p.name"></strong>
                  <p x-text="p.desc"></p>
                </span>
              </button>
            </template>
          </div>

          {{-- Draft: params for the selected product --}}
          <template x-if="selected">
            <div class="cc-draft-panel" x-transition.opacity.duration.200ms>
              <div class="cc-draft-head">
                <strong x-text="selectedProduct().name"></strong>
                <button type="button" class="cc-toggle-link" @click="resetDraft()">Anuluj wybór</button>
              </div>

              <div class="cc-params">
                <template x-for="param in selectedProduct().params" :key="param.key">
                  <div class="cc-param-block">
                    <template x-if="param.type === 'chips'">
                      <div>
                        <label x-text="param.label"></label>
                        <div class="cc-chip-row">
                          <template x-for="option in param.values" :key="option.value">
                            <button type="button" class="cc-chip" :class="isParam(param.key, option.value) ? 'is-active' : ''"
                                    @click="setParam(param.key, option.value)" x-text="option.label"></button>
                          </template>
                        </div>
                      </div>
                    </template>
                    <template x-if="param.type === 'number'">
                      <div>
                        <label x-text="param.label"></label>
                        <div class="cc-qty">
                          <button type="button" @click="params[param.key] = Math.max(param.min, (Number(params[param.key]) || param.min) - 1)" aria-label="Mniej"><i class="fas fa-minus" aria-hidden="true"></i></button>
                          <strong x-text="params[param.key]"></strong>
                          <button type="button" @click="params[param.key] = Math.min(param.max, (Number(params[param.key]) || param.min) + 1)" aria-label="Więcej"><i class="fas fa-plus" aria-hidden="true"></i></button>
                        </div>
                      </div>
                    </template>
                  </div>
                </template>
              </div>

              {{-- Project upload (optional) --}}
              <div class="cc-b2b-file">
                <div x-data="ccDropzone('b2b-' + selected, { endpoint: '/api/v1/b2b/uploads', extensions: ['pdf', 'ai', 'cdr', 'png', 'jpg', 'jpeg'], invalidMessage: 'Wybierz PDF, AI, CDR, PNG lub JPG.' })"
                     class="cc-dropzone cc-dropzone--compact"
                     :class="status === 'invalid' ? 'is-invalid' : (dragging && status === 'empty' ? 'is-dragover' : '')"
                     @dragover.prevent="dragenter"
                     @dragleave.prevent="dragleave"
                     @drop.prevent="drop"
                     @cc-file="onFile($event.detail)">

                  <template x-if="status === 'empty'">
                    <div class="cc-dropzone-inner">
                      <i class="cc-dropzone-icon fas fa-file-upload" aria-hidden="true"></i>
                      <h3>Dodaj projekt (opcjonalnie)</h3>
                       <p>PDF, AI, CDR, PNG albo JPG. Maksymalnie 20 MB.</p>
                      <div class="cc-dropzone-actions">
                        <label class="btn-magenta" style="cursor:pointer">
                          <i class="fas fa-folder-open mr-2" aria-hidden="true"></i>Wybierz plik
                          <input type="file" accept=".pdf,.ai,.cdr,.png,.jpg,.jpeg,application/pdf" class="sr-only" @change="change($event)">
                        </label>
                      </div>
                    </div>
                  </template>

                  <template x-if="status === 'uploading' || status === 'analyzing'">
                    <div class="cc-dropzone-inner">
                      <span class="cc-spinner" aria-hidden="true"></span>
                      <h3 x-text="status === 'uploading' ? 'Przesyłamy ' + name : 'Sprawdzamy plik'"></h3>
                    </div>
                  </template>

                  <template x-if="status === 'ready'">
                    <div class="cc-dropzone-inner">
                      <span class="cc-dropzone-success"><i class="fas fa-check" aria-hidden="true"></i></span>
                      <h3>Projekt dołączony</h3>
                      <p class="cc-file-name"><i class="fas fa-file-pdf" aria-hidden="true"></i><span x-text="name"></span></p>
                    </div>
                  </template>

                  <template x-if="status === 'invalid'">
                    <div class="cc-dropzone-inner">
                      <i class="cc-dropzone-icon fas fa-triangle-exclamation" aria-hidden="true"></i>
                      <h3>Nieobsługiwany format</h3>
                      <p>Wybierz PDF, AI, Cdr, PNG lub JPG.</p>
                    </div>
                  </template>
                </div>
                <span class="cc-b2b-file-hint">Plik zostanie dołączony do zapytania i będzie dostępny tylko dla pracowni.</span>
              </div>

              {{-- Help toggle --}}
              <button type="button" class="cc-toggle-link" @click="helpWanted = !helpWanted">
                <i class="fas fa-circle-question mr-2" aria-hidden="true"></i>
                <span x-text="helpWanted ? 'Projekt przygotuje pracownia — zaznaczone' : 'Potrzebuję pomocy z projektem'"></span>
              </button>
              <template x-if="helpWanted">
                <p class="cc-help-note" x-transition.opacity.duration.200ms>Zaznaczone: projekt wykonamy i dopasujemy do formatu. To wpłynie na wycenę.</p>
              </template>

              {{-- Commit --}}
              <div class="cc-draft-actions">
                <div class="btn-row">
                  <button type="button" class="btn-magenta" @click="commitItem()">Dodaj pozycję do zamówienia <i class="fas fa-plus ml-2" aria-hidden="true"></i></button>
                  <button type="button" class="btn-geel" @click="go('#pozycje')">Przejdź do pozycji</button>
                </div>
              </div>
            </div>
          </template>
        </section>

        {{-- Step 02 — Pozycje --}}
        <section class="cc-step" id="pozycje">
          <div class="cc-step-head">
            <span class="cc-step-num">02</span>
            <div><h2>Zobacz, co już masz w zamówieniu.</h2><p>Każda pozycja trzyma własne ustawienia. Usuń, czego nie chcesz.</p></div>
          </div>

          <div x-show="items.length === 0" class="cc-items-empty">
            <p>Na razie pusto.<br>Wróć do pierwszego kroku i dodaj pozycje.</p>
          </div>

          <div x-show="items.length" class="cc-items-list" x-transition.opacity.duration.200ms>
            <template x-for="(item, index) in items" :key="item.id">
              <div class="cc-item-row">
                <i :class="'fas ' + item.icon" class="cc-item-icon" aria-hidden="true"></i>
                <div class="cc-item-meta">
                  <strong x-text="item.name"></strong>
                  <small x-text="itemSummary(item)"></small>
                  <small x-show="item.file" x-text="'Plik: ' + item.file"></small>
                  <small x-show="item.helpWanted">Projekt do wykonania przez pracownię</small>
                </div>
                <button type="button" class="cc-item-remove" @click="removeItem(index)">Usuń</button>
              </div>
            </template>
          </div>
        </section>

        {{-- Step 03 — Odbiór --}}
        <section class="cc-step" id="odbior">
          <div class="cc-step-head">
            <span class="cc-step-num">03</span>
            <div><h2>Kiedy i gdzie odbierzesz zamówienie?</h2><p>Odbiór w Katowicach albo wysyłka. Doręczenie wyceniamy osobno od produkcji.</p></div>
          </div>

          <div class="cc-step-grid cc-step-grid--3">
            @foreach ($b2bDeliveries as $delivery)
              <label class="cc-option">
                <input type="radio" name="b2b-odbior" value="{{ $delivery['id'] }}" x-model="delivery">
                <span class="cc-option-body">
                  <span class="cc-option-check"><i class="fas fa-check" aria-hidden="true"></i></span>
                  <span class="cc-option-main">
                     <strong>{{ $delivery['name'] }}</strong>
                     <em>{{ $delivery['hint'] }}</em>
                   </span>
                    <span class="cc-option-price">{{ number_format((float) $delivery['price'], 2, ',', ' ') }} zł</span>
                 </span>
               </label>
            @endforeach
          </div>

           <x-concept.inpost-picker prefix="b2b" />

           <template x-if="delivery === 'courier'">
             <div class="cc-field-grid" x-transition.opacity.duration.200ms>
               <div class="cc-field"><label for="b2b-courier-address">Adres dostawy</label><input id="b2b-courier-address" type="text" x-model="courierAddress.address" placeholder="Ulica i numer"></div>
               <div class="cc-field"><label for="b2b-courier-city">Miasto</label><input id="b2b-courier-city" type="text" x-model="courierAddress.city" placeholder="Katowice"></div>
               <div class="cc-field"><label for="b2b-courier-post-code">Kod pocztowy</label><input id="b2b-courier-post-code" type="text" x-model="courierAddress.post_code" placeholder="40-007"></div>
             </div>
           </template>

           <div class="cc-field">
            <label for="b2b-data">Potrzebuję najpóźniej</label>
            <input id="b2b-data" type="date" x-model="byDate" :min="new Date().toISOString().split('T')[0]">
          </div>

          <template x-if="deliveryLate()">
            <p class="cc-warning" x-transition.opacity.duration.200ms x-text="deliveryLate()"></p>
          </template>
          <p class="cc-summary-note">Produkcję (np. druk 5–7 dni roboczych) zawsze potwierdzimy w wycenie — <strong>tego terminu nie zgadujemy</strong>.</p>
        </section>

      </div>
    </div>

    {{-- Sticky summary (desktop) --}}
    <aside class="cc-summary" aria-label="Podsumowanie zamówienia">
      <div class="cc-summary-card">
        <div class="cc-summary-head">
          <strong><i class="fas fa-clipboard-list mr-2 text-magenta" aria-hidden="true"></i>Twoje zamówienie</strong>
          <ul>
             <li x-text="items.length + (items.length === 1 ? ' pozycja' : items.length > 1 && items.length < 5 ? ' pozycje' : ' pozycji')"></li>
             <li x-text="deliveryName() || 'odbiór nie wybrany'"></li>
             <li x-show="delivery === 'parcel' && parcelLocker" x-text="lockerSummary()"></li>
           </ul>
        </div>
        <template x-if="items.length === 0">
          <div class="cc-price-row"><span>Pozycje</span><strong class="cc-price-status">dodaj przynajmniej jedną</strong></div>
        </template>
        <template x-for="item in items" :key="item.id">
          <div class="cc-price-row">
            <span x-text="item.name"></span>
            <strong class="cc-price-status" x-text="itemSummary(item)"></strong>
          </div>
        </template>
        <div class="cc-summary-term"><span>Produkcja</span><strong>po wycenie</strong></div>
        <div class="cc-summary-total"><span>Razem</span><strong>wyceny po kontakcie</strong></div>
        <div class="cc-summary-actions">
          <div class="cc-field-grid">
            <div class="cc-field">
              <label for="b2b-imie">Imię i nazwisko</label>
              <input id="b2b-imie" type="text" x-model="company.person" placeholder="Jan Kowalski">
            </div>
            <div class="cc-field">
              <label for="b2b-firma">Nazwa firmy</label>
              <input id="b2b-firma" type="text" x-model="company.name" placeholder="Nazwa firmy">
            </div>
            <div class="cc-field">
              <label for="b2b-email">E-mail</label>
              <input id="b2b-email" type="email" x-model="company.email" placeholder="jan@firma.pl">
            </div>
            <div class="cc-field">
              <label for="b2b-telefon">Telefon (opcjonalnie)</label>
              <input id="b2b-telefon" type="tel" x-model="company.phone" placeholder="502 000 000">
            </div>
          </div>

          <div class="cc-field">
            <label class="cc-toggle">
              <input type="checkbox" x-model="company.invoice">
              <span class="cc-toggle-track" aria-hidden="true"></span>
              <span>Potrzebuję faktury VAT</span>
            </label>
          </div>

          <template x-if="company.invoice">
            <div class="cc-field" x-transition.opacity.duration.200ms>
              <label for="b2b-nip">NIP</label>
              <input id="b2b-nip" type="text" x-model="company.nip" placeholder="0000000000">
            </div>
          </template>

          <label class="cc-consent">
            <input type="checkbox" x-model="privacyAccepted">
            <span>Akceptuję <a href="{{ route('privacy') }}" target="_blank" rel="noopener">politykę prywatności</a> i zgadzam się na przetwarzanie danych w celu przygotowania wyceny.</span>
          </label>

          <button type="button" class="btn-magenta" @click="submitQuoteRequest()" :disabled="submitting || submitted"><span x-text="submitting ? 'Wysyłamy...' : (submitted ? 'Zapytanie wysłane' : 'Wyślij wniosek o wycenę')"></span> <i class="fas fa-paper-plane ml-2" aria-hidden="true"></i></button>
          <p class="cc-warning" x-show="submitError" x-text="submitError"></p>
          <p class="cc-summary-demo" x-show="submitted">Dziękujemy. Odpowiemy po analizie zakresu i plików.</p>
        </div>
      </div>
    </aside>

    {{-- Mobile order bar (shares configurator state) --}}
    <div class="cc-mobile-bar">
      <div class="cc-mobile-bar-total">
        <span>Pozycje</span>
        <strong x-text="items.length"></strong>
      </div>
      <button type="button" class="btn-magenta" @click="submitQuoteRequest()" :disabled="submitting || submitted"><span x-text="submitted ? 'Wysłano' : 'Wyślij wycenę'"></span></button>
      <p class="cc-mobile-bar-message" x-show="submitError" x-text="submitError"></p>
    </div>
  </div>
</main>
@endsection
