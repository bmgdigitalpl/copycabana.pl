@extends('layouts.main')

@section('title', 'Druk dla firm — konfigurator zleceń | CopyCabana')
@section('description', 'Wizytówki, ulotki, plakaty, banery, rollupy i dokumenty w jednym zamówieniu. Konfigurator druku dla firm — CopyCabana, Katowice.')

@section('content')
<main class="cc-page">
  {{-- Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Druk dla firm</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Zamów taki druk, <em>jakiego potrzebujesz.</em></h1>
        <p class="reveal reveal-delay-2">Wizytówki, ulotki, plakaty, banery, rollupy i dokumenty. Układasz wiele pozycji w jednym zamówieniu, a my wyceniamy całość — bez ukrytych narzutów.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="#produkty" @click.prevent="ccGo('#produkty')" class="btn-magenta inline-block">Zacznij od produktu <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></a>
          <a href="#pomoc" @click.prevent="ccGo('#pomoc')" class="btn-outline-light inline-block">Masz niestandardowe zlecenie?</a>
        </div>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-hero-stack" aria-hidden="true">
          <div class="cc-hero-card cc-hero-card--b">
            <img src="{{ asset('images/produkty/product-03.png') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--a">
            <img src="{{ asset('images/produkty/product-02.png') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--tag">Druk dla firm</div>
        </div>
      </div>
    </div>
  </section>

  <div class="cc-container cc-mt" style="padding-top:1.5rem">
    <x-concept.demo-notice>Wersja demonstracyjna — ceny ustala pracownia po kontakcie, bez ukrytych narzutów</x-concept.demo-notice>
  </div>

  {{-- Configurator --}}
  <div x-data="ccB2bConfigurator()" class="cc-container cc-config cc-config-page">
    <div class="cc-config-main">
      <div class="cc-steps">

        {{-- Step 01 — Produkty --}}
        <section class="cc-step" id="produkty">
          <div class="cc-step-head">
            <span class="cc-step-num">01</span>
            <div><h2>Wybierz pozycje do zamówienia.</h2><p>Zaczyna się od produktu. Dodajemy każdą pozycję osobno, więc nic się nie nadpisze.</p></div>
          </div>

          <div class="cc-b2b-grid">
            <template x-for="p in products" :key="p.id">
              <button type="button" class="cc-b2b-card"
                      :class="selected === p.id ? 'is-selected' : ''"
                      @click="selectProduct(p.id)">
                <span class="cc-b2b-check"><i class="fas fa-check" aria-hidden="true"></i></span>
                <i :class="'fas ' + p.icon" class="cc-b2b-icon" aria-hidden="true"></i>
                <strong x-text="p.name"></strong>
                <p x-text="p.desc"></p>
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
                          <template x-for="opt in param.options" :key="opt">
                            <button type="button" class="cc-chip" :class="isParam(param.key, opt) ? 'is-active' : ''"
                                    @click="setParam(param.key, opt)" x-text="opt"></button>
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
                <div x-data="ccDropzone('b2b-' + selected, { pages: 0, colored: 0 })"
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
                      <p>PDF, AI lub Cdr. Możesz wysłać później.</p>
                      <div class="cc-dropzone-actions">
                        <label class="btn-magenta" style="cursor:pointer">
                          <i class="fas fa-folder-open mr-2" aria-hidden="true"></i>Wybierz plik
                          <input type="file" accept=".pdf,.ai,.cdr,.png,.jpg,.jpeg,application/pdf" class="sr-only" @change="change($event)">
                        </label>
                        <button type="button" class="brand-upload-button" @click="simulate()">Symuluj plik</button>
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
                <span class="cc-b2b-file-hint">Plik w tej wersji nie jest nigdzie wysyłany — istnieje tylko w wersji demo.</span>
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
            @foreach (['pickup', 'parcel', 'courier'] as $d)
              <label class="cc-option">
                <input type="radio" name="b2b-odbior" value="{{ $d }}" x-model="delivery">
                <span class="cc-option-body">
                  <span class="cc-option-check"><i class="fas fa-check" aria-hidden="true"></i><em>Wybrano</em></span>
                  <span class="cc-option-main">
                    <strong>{{ ['pickup' => 'Odbiór w Katowicach', 'parcel' => 'Paczkomat', 'courier' => 'Kurier'][$d] }}</strong>
                    <em>{{ ['pickup' => 'ul. Bankowa 11, 40-007 Katowice', 'parcel' => 'demo — integracja do potwierdzenia', 'courier' => 'demo — integracja do potwierdzenia'][$d] }}</em>
                  </span>
                  <span class="cc-option-price">{{ ['pickup' => '0,00 zł', 'parcel' => '12,00 zł', 'courier' => '18,00 zł'][$d] }}</span>
                </span>
              </label>
            @endforeach
          </div>

          <div class="cc-field">
            <label for="b2b-data">Potrzebuję najpóźniej</label>
            <input id="b2b-data" type="date" x-model="byDate" :min="new Date().toISOString().split('T')[0]">
          </div>

          <template x-if="deliveryLate()">
            <p class="cc-warning" x-transition.opacity.duration.200ms x-text="deliveryLate()"></p>
          </template>
          <p class="cc-summary-note">Produkcję (e.g. druk 5–7 dni roboczych) zawsze potwierdzimy w wycenie — <strong>tego terminu nie zgadujemy</strong>.</p>
        </section>

        {{-- Step 04 — Dane firmowe --}}
        <section class="cc-step" id="dane">
          <div class="cc-step-head">
            <span class="cc-step-num">04</span>
            <div><h2>Dokąd wysłać wycenę?</h2><p>Demo — nic nie jest wysyłane.</p></div>
          </div>

          <div class="cc-field-grid">
            <div class="cc-field">
              <label for="b2b-imie">Imię i nazwisko</label>
              <input id="b2b-imie" type="text" x-model="company.person" placeholder="Jan Kowalski">
            </div>
            <div class="cc-field">
              <label for="b2b-email">E-mail</label>
              <input id="b2b-email" type="email" x-model="company.email" placeholder="jan@firma.pl">
            </div>
            <div class="cc-field">
              <label for="b2b-telefon">Telefon</label>
              <input id="b2b-telefon" type="tel" x-model="company.phone" placeholder="502 000 000">
            </div>
            <div class="cc-field">
              <label for="b2b-firma">Nazwa firmy</label>
              <input id="b2b-firma" type="text" x-model="company.name" placeholder="Nazwa firmy">
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
        </section>

        {{-- Step 05 — Pomoc / niestandardowe zlecenie --}}
        <section class="cc-step" id="pomoc">
          <div class="cc-step-head">
            <span class="cc-step-num">05</span>
            <div><h2>Nie widzisz swojego druku?</h2><p>Opisz zlecenie prostymi słowami — pracownia doradzi i naciągnie format.</p></div>
          </div>

          <div class="cc-brief">
            <template x-if="!briefOpen">
              <div>
                <h3>Mam niestandardowe zlecenie</h3>
                <p>Plakaty w nietypowym formacie, naklejki, koperty, przeszklenia, podklady pod stół — zaczynamy od rozmowy.</p>
                <label class="btn-geel" style="cursor:pointer" @click="toggleBrief()">Opisz zlecenie <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></label>
              </div>
            </template>

            <template x-if="briefOpen">
              <form @submit.prevent="sendBrief()">
                <h3>Opisz zlecenie</h3>
                <label for="brief-type">Rodzaj</label>
                <select id="brief-type" x-model="brief.type">
                  @foreach (['Naklejki i etykiety', 'Koperty', 'Teczki', 'Podkładki', 'Bramki i konstrukcje', 'Coś zupełnie innego'] as $t)
                    <option>{{ $t }}</option>
                  @endforeach
                </select>
                <label for="brief-desc">Opis</label>
                <textarea id="brief-desc" rows="3" x-model="brief.desc" placeholder="Co to jest, w jakim formacie, jaka ilość, jaki deadline…"></textarea>
                <label for="brief-qty">Ilość</label>
                <input id="brief-qty" type="text" x-model="brief.qty" placeholder="np. 500 sztuk">
                <label for="brief-date">Kiedy potrzebne</label>
                <input id="brief-date" type="date" x-model="brief.date" :min="new Date().toISOString().split('T')[0]">
                <button type="submit" class="btn-magenta">Wyślij opis (demo) <i class="fas fa-paper-plane ml-2" aria-hidden="true"></i></button>
                <p class="cc-brief-note" x-show="briefSent" x-text="'W wersji demonstracyjnej nic nie zostało wysłane.'"></p>
              </form>
            </template>
          </div>
        </section>

        {{-- Step 06 — Podsumowanie --}}
        <section class="cc-step" id="podsumowanie">
          <div class="cc-step-head">
            <span class="cc-step-num">06</span>
            <div><h2>Sprawdź wniosek o wycenę.</h2><p>Pozycje możesz zmienić — do wyceny wraca gotowa lista.</p></div>
          </div>

          <div class="cc-summary-card cc-summary-full">
            <div class="cc-summary-head">
              <strong><i class="fas fa-clipboard-list mr-2 text-magenta" aria-hidden="true"></i>Wniosek o wycenę</strong>
              <ul>
                <li x-text="items.length + (items.length === 1 ? ' pozycja' : items.length > 1 && items.length < 5 ? ' pozycje' : ' pozycji')"></li>
                <li x-text="deliveryName() || 'odbiór nie wybrany'"></li>
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
          </div>

          <div class="cc-final-actions">
            <button type="button" class="btn-magenta" disabled>Wyślij wniosek o wycenę (demo) <i class="fas fa-paper-plane ml-2" aria-hidden="true"></i></button>
            <p class="cc-summary-demo">Wersja demonstracyjna. Nie wysyła plików i nie składa zamówień.</p>
          </div>
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
          <button type="button" class="btn-magenta" @click="go('#podsumowanie')">Przejdź do podsumowania <i class="fas fa-arrow-down ml-2" aria-hidden="true"></i></button>
          <p class="cc-summary-demo">Wersja demonstracyjna — wycena po kontakcie.</p>
        </div>
      </div>
    </aside>

    {{-- Mobile order bar (shares configurator state) --}}
    <div class="cc-mobile-bar">
      <div class="cc-mobile-bar-total">
        <span>Pozycje</span>
        <strong x-text="items.length"></strong>
      </div>
      <button type="button" class="btn-magenta" @click="go('#podsumowanie')">Podsumowanie <i class="fas fa-arrow-up ml-2" aria-hidden="true"></i></button>
    </div>
  </div>
</main>
@endsection
