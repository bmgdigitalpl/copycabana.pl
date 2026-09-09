@extends('layouts.main')

@section('title', 'Prace dyplomowe — druk i oprawa | CopyCabana')
@section('description', 'Kompletny PDF pracy dyplomowej, wybór oprawy, okładki i odbioru. Konfigurator druku — CopyCabana, Katowice.')

@section('content')
<main class="cc-page">
  {{-- Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Druk i oprawa prac dyplomowych</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Praca napisana.<em>Teraz druk i oprawa.</em></h1>
        <p class="reveal reveal-delay-2">Dodaj PDF, wybierz sposób druku i oprawę. Przed zamówieniem sprawdzisz pełną cenę oraz dostępny termin odbioru lub dostawy.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="#plik" @click.prevent="ccGo('#plik')" class="btn-magenta inline-block">Dodaj pracę w PDF <i class="fas fa-upload ml-2" aria-hidden="true"></i></a>
          <a href="#oprawa" @click.prevent="ccGo('#oprawa')" class="btn-outline-light inline-block">Najpierw sprawdź oprawy</a>
        </div>
        <p class="cc-hero-hint reveal reveal-delay-4"><i class="fas fa-circle-info" aria-hidden="true"></i> Zazwyczaj realizujemy zamówienia w 24h. Potwierdzenie zależy od pliku, oprawy i obciążenia — pokażemy je w podsumowaniu.</p>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-hero-stack" aria-hidden="true">
          <div class="cc-hero-card cc-hero-card--b">
            <img src="{{ asset('images/carousel-6.jpg') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--a">
            <div class="cc-hero-mini-report" aria-hidden="true">
              <span>PDF</span>
              <strong>84 strony</strong>
              <p>12 kolorowych · 2 egzemplarze</p>
            </div>
          </div>
          <div class="cc-hero-card cc-hero-card--tag">Druk + oprawa</div>
        </div>
      </div>
    </div>
  </section>

  <div class="cc-container cc-mt" style="padding-top:1.5rem">
    <x-concept.demo-notice>Wersja demonstracyjna konfiguratora</x-concept.demo-notice>
  </div>

  {{-- Configurator --}}
  <div x-data="ccConfigurator()" class="cc-container cc-config cc-config-page">
    <div class="cc-config-main">
      <div class="cc-steps">
        <section class="cc-step" id="plik">
          <div class="cc-step-head">
            <span class="cc-step-num">01</span>
            <div><h2>Zacznij od swojej pracy.</h2><p>Dodaj jeden kompletny PDF. Sprawdź, czy zawiera wszystkie strony we właściwej kolejności.</p></div>
          </div>

          <div x-data="ccDropzone('thesis', { pages: 84, colored: 12 })"
               class="cc-dropzone"
               :class="status === 'invalid' ? 'is-invalid' : (dragging && status === 'empty' ? 'is-dragover' : '')"
               @dragover.prevent="dragenter"
               @dragleave.prevent="dragleave"
               @drop.prevent="drop"
               @cc-file="onFile($event.detail)">

            <template x-if="status === 'empty'">
              <div class="cc-dropzone-inner">
                <i class="cc-dropzone-icon fas fa-file-pdf" aria-hidden="true"></i>
                <h3>Przeciągnij PDF albo wybierz plik</h3>
                <p>Format PDF, limit strony do ustalenia przed produkcją.<br>Demo — pliki nie są wysyłane nigdzie.</p>
                <div class="cc-dropzone-actions">
                  <label class="btn-magenta" style="cursor:pointer">
                    <i class="fas fa-folder-open mr-2" aria-hidden="true"></i>Wybierz plik PDF
                    <input type="file" accept=".pdf,application/pdf" class="sr-only" @change="change($event)">
                  </label>
                  <button type="button" class="brand-upload-button" @click="simulate()">Symuluj analizę PDF</button>
                </div>
              </div>
            </template>

            <template x-if="status === 'uploading'">
              <div class="cc-dropzone-inner">
                <i class="cc-dropzone-icon fas fa-cloud-arrow-up" aria-hidden="true"></i>
                <h3 x-text="'Przesyłamy ' + name"></h3>
                <div class="cc-dropzone-progress"><span></span></div>
                <p>Pozostań na tej stronie. Plik jest przeliczany lokalnie.</p>
              </div>
            </template>

            <template x-if="status === 'analyzing'">
              <div class="cc-dropzone-inner">
                <span class="cc-spinner" aria-hidden="true"></span>
                <h3>Analizujemy dokument</h3>
                <p>Sprawdzamy liczbę stron, orientację i strony kolorowe.</p>
              </div>
            </template>

            <template x-if="status === 'ready'">
              <div class="cc-dropzone-inner">
                <span class="cc-dropzone-success"><i class="fas fa-check" aria-hidden="true"></i></span>
                <h3>PDF gotowy do konfiguracji</h3>
                <p class="cc-file-name"><i class="fas fa-file-pdf" aria-hidden="true"></i><span x-text="name"></span></p>
                <div class="cc-file-report">
                  <div><span>Strony</span><strong x-text="pages"></strong></div>
                  <div><span>Kolorowe</span><strong x-text="colored"></strong></div>
                  <div><span>Status</span><strong>OK</strong></div>
                </div>
                <div class="cc-dropzone-actions">
                  <button type="button" class="btn-geel" @click="go('#druk')">Wybieram druk <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></button>
                </div>
              </div>
            </template>

            <template x-if="status === 'invalid'">
              <div class="cc-dropzone-inner">
                <i class="cc-dropzone-icon fas fa-triangle-exclamation" aria-hidden="true"></i>
                <h3>To nie wygląda na PDF</h3>
                <p class="cc-dropzone-error" x-text="'Plik: ' + name"></p>
                <p>Wybierz plik w formacie PDF albo skontaktuj się z nami w sprawie innego formatu.</p>
                <div class="cc-dropzone-actions">
                  <label class="btn-magenta" style="cursor:pointer">
                    Wybierz inny plik
                    <input type="file" accept=".pdf,application/pdf" class="sr-only" @change="change($event)">
                  </label>
                </div>
              </div>
            </template>
          </div>
        </section>

        {{-- Step 02 — Druk --}}
        <section class="cc-step" id="druk">
          <div class="cc-step-head">
            <span class="cc-step-num">02</span>
            <div><h2>Wybierz, jak wydrukujemy strony.</h2><p>Ustaw kolor, strony kartki i liczbę egzemplarzy.</p></div>
          </div>

          <p class="cc-step-label">Kolor</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="druk-kolor" value="bw" model="print.color" label="czarno-biały" hint="Standard dla prac dyplomowych." />
            <x-concept.option-card group="druk-kolor" value="color" model="print.color" label="kolor" hint="Strony kolorowe drukowane w kolorze." />
          </div>

          <p class="cc-step-label">Strony kartki</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="druk-strony" value="simplex" model="print.sided" label="jednostronnie" hint="Każda strona PDF na osobnej kartce." />
            <x-concept.option-card group="druk-strony" value="duplex" model="print.sided" label="dwustronnie" hint="Druk po obu stronach kartki. Oszczędza papier i objętość." />
          </div>

          <p class="cc-step-label">Liczba egzemplarzy</p>
          <div class="cc-qty">
            <button type="button" @click="print.copies = print.copies > 1 ? print.copies - 1 : 1" aria-label="Mniej egzemplarzy"><i class="fas fa-minus" aria-hidden="true"></i></button>
            <strong x-text="print.copies"></strong>
            <button type="button" @click="print.copies = print.copies < 10 ? print.copies + 1 : 10" aria-label="Więcej egzemplarzy"><i class="fas fa-plus" aria-hidden="true"></i></button>
          </div>

          <p class="cc-step-micro">Potrzebujesz różnych opraw do każdego egzemplarza? Zgłoś to wcześniej — w konfiguratorze ustawiamy jedną oprawę dla całego nakładu.</p>
        </section>

        {{-- Step 03 — Oprawa --}}
        <section class="cc-step" id="oprawa">
          <div class="cc-step-head">
            <span class="cc-step-num">03</span>
            <div><h2>Tak będzie wyglądała gotowa praca.</h2><p>Porównaj dostępne oprawy i wybierz wariant zgodny z wymaganiami wydziału.</p></div>
          </div>

          <div class="cc-step-grid cc-step-grid--3">
            @php
              $options = [
                ['id' => 'soft', 'label' => 'Oprawa miękka', 'hint' => 'Klasyczna broszura. Częsty standard wydziałów.', 'price' => '0,00 zł'],
                ['id' => 'channel', 'label' => 'Oprawa kanałowa', 'hint' => 'Klejony blok, równy grzbiet.', 'price' => '25,00 zł'],
                ['id' => 'hard', 'label' => 'Oprawa twarda', 'hint' => 'Sztywna oprawa. Premium w obronie.', 'price' => '50,00 zł'],
              ];
            @endphp
            @foreach ($options as $b)
              <x-concept.binding-card
                group="oprawa"
                value="{{ $b['id'] }}"
                model="binding"
                thickness="{{ $b['id'] }}"
                label="{{ $b['label'] }}"
                hint="{{ $b['hint'] }}"
                price="{{ $b['price'] }}" />
            @endforeach
          </div>

          <p class="cc-step-micro">Nazwy techniczne podajemy w szczegółach. Cena oprawy jest przykładowa i może się zmienić przed produkcją.</p>
        </section>

        {{-- Step 04 — Okładka --}}
        <section class="cc-step" id="okladka">
          <div class="cc-step-head">
            <span class="cc-step-num">04</span>
            <div><h2>Dodaj napis na okładce.</h2><p>Napis dotyczy zewnętrznej okładki. Nie zmienia strony tytułowej w PDF.</p></div>
          </div>

          <div class="cc-step-grid cc-step-grid--3">
            @foreach (['none', 'standard', 'custom'] as $i => $c)
              <x-concept.option-card
                group="okladka"
                value="{{ $c }}"
                model="cover"
                label="{{ ['none' => 'Bez napisu', 'standard' => 'Standardowy napis', 'custom' => 'Własny napis'][$c] }}"
                hint="{{ ['none' => 'Czysta okładka.', 'standard' => 'Wzór do akceptacji przed produkcją.', 'custom' => 'Dokładna treść, jaką wpiszesz.'][$c] }}"
                price="{{ ['none' => '0,00 zł', 'standard' => '15,00 zł', 'custom' => '10,00 zł'][$c] }}" />
            @endforeach
          </div>

          <template x-if="cover === 'custom'">
            <div class="cc-field" x-transition.opacity.duration.200ms>
              <label for="okladka-tekst">Treść napisu na okładce</label>
              <input id="okladka-tekst" type="text" x-model="coverText" placeholder="np. tytuł pracy · imię i nazwisko">
            </div>
          </template>

          <p class="cc-step-micro" x-show="cover === 'standard'">Przykład standardowego napisu: „Tytuł pracy dyplomowej — Imię Nazwisko”.</p>

          <div class="cc-thesis-preview" x-transition.opacity.duration.200ms>
            <div class="cc-thesis"
              :class="['cc-thesis--' + coverColor, 'cc-thesis--' + binding]"
              aria-hidden="true">
              <span class="cc-thesis-spine"></span>
              <span class="cc-thesis-cover">
                <em class="cc-thesis-degree" x-text="activeVariant().degree"></em>
                <span class="cc-thesis-center">
                  <strong class="cc-thesis-title" x-text="activeVariant().title"></strong>
                  <span class="cc-thesis-rule"></span>
                  <span class="cc-thesis-author" x-text="activeVariant().author"></span>
                </span>
              </span>
              <span class="cc-thesis-pages"></span>
            </div>

            <div class="cc-thesis-controls">
              <div class="cc-thesis-control">
                <p class="cc-thesis-label">Kolor okładki</p>
                <div class="cc-swatches" role="group" aria-label="Kolor okładki">
                  <template x-for="c in coverColors" :key="c.id">
                    <button type="button"
                            class="cc-swatch"
                            :class="{ 'is-active': coverColor === c.id }"
                            :style="'background:' + c.hex"
                            :aria-label="'Kolor okładki: ' + c.id"
                            @click="coverColor = c.id"></button>
                  </template>
                </div>
              </div>

              <div class="cc-thesis-control">
                <p class="cc-thesis-label">Przykładowe napisy</p>
                <div class="cc-title-chips" role="group" aria-label="Przykładowe napisy na okładce">
                  <template x-for="(v, i) in titleVariants" :key="i">
                    <button type="button"
                            class="cc-title-chip"
                            :class="{ 'is-active': titleVariant === i }"
                            x-text="v.degree"
                            @click="titleVariant = i"></button>
                  </template>
                </div>
              </div>

              <p class="cc-step-micro">Podgląd poglądowy — pokazuje, jak wygląda gotowa praca. Grubość i ułożenie grzbietu zmieniają się razem z wybraną oprawą w kroku 03.</p>
            </div>
          </div>
        </section>

        {{-- Step 05 — Odbiór --}}
        <section class="cc-step" id="odbiór">
          <div class="cc-step-head">
            <span class="cc-step-num">05</span>
            <div><h2>Kiedy i gdzie odbierzesz pracę?</h2><p>Porównaj odbiór w Katowicach i dostępne opcje wysyłki. Termin produkcji i doręczenie podajemy osobno.</p></div>
          </div>

          <div class="cc-step-grid cc-step-grid--3">
            @foreach (['pickup', 'parcel', 'courier'] as $i => $d)
              <x-concept.option-card
                group="odbior"
                value="{{ $d }}"
                model="delivery"
                label="{{ ['pickup' => 'Odbiór w Katowicach', 'parcel' => 'Paczkomat', 'courier' => 'Kurier'][$d] }}"
                hint="{{ ['pickup' => 'ul. Bankowa 11, 40-007 Katowice', 'parcel' => 'demo — integracja do potwierdzenia', 'courier' => 'demo — integracja do potwierdzenia'][$d] }}"
                price="{{ ['pickup' => '0,00 zł', 'parcel' => '12,00 zł', 'courier' => '18,00 zł'][$d] }}" />
            @endforeach
          </div>

          <div class="cc-field">
            <label for="odbior-data">Potrzebuję pracy najpóźniej</label>
            <input id="odbior-data" type="date" x-model="byDate" :min="new Date().toISOString().split('T')[0]">
          </div>

          <template x-if="deliveryLate()">
            <p class="cc-warning" x-transition.opacity.duration.200ms x-text="deliveryLate()"></p>
          </template>
        </section>

        {{-- Step 06 — Dane --}}
        <section class="cc-step" id="dane">
          <div class="cc-step-head">
            <span class="cc-step-num">06</span>
            <div><h2>Podaj dane do zamówienia.</h2><p>Na ten e-mail wyślemy potwierdzenie i informacje o realizacji. Demo — nic nie jest wysyłane.</p></div>
          </div>

          <div class="cc-field-grid">
            <div class="cc-field">
              <label for="dane-imie">Imię i nazwisko</label>
              <input id="dane-imie" type="text" x-model="form.name" placeholder="Jan Kowalski">
            </div>
            <div class="cc-field">
              <label for="dane-email">E-mail</label>
              <input id="dane-email" type="email" x-model="form.email" placeholder="jan@example.com">
            </div>
            <div class="cc-field">
              <label for="dane-telefon">Telefon (opcjonalnie)</label>
              <input id="dane-telefon" type="tel" x-model="form.phone" placeholder="502 000 000">
            </div>
          </div>

          <div class="cc-field">
            <label class="cc-toggle">
              <input type="checkbox" x-model="form.invoice">
              <span class="cc-toggle-track" aria-hidden="true"></span>
              <span>Potrzebuję faktury</span>
            </label>
          </div>

          <template x-if="form.invoice">
            <div class="cc-field-grid" x-transition.opacity.duration.200ms>
              <div class="cc-field">
                <label for="dane-firma">Nazwa firmy</label>
                <input id="dane-firma" type="text" x-model="form.company" placeholder="Nazwa spółki">
              </div>
              <div class="cc-field">
                <label for="dane-nip">NIP</label>
                <input id="dane-nip" type="text" x-model="form.nip" placeholder="0000000000">
              </div>
            </div>
          </template>
        </section>

        {{-- Step 07 — Podsumowanie --}}
        <section class="cc-step" id="podsumowanie">
          <div class="cc-step-head">
            <span class="cc-step-num">07</span>
            <div><h2>Sprawdź wszystko przed drukiem.</h2><p>Każdą sekcję możesz zmienić — kliknij w odpowiednią pozycję.</p></div>
          </div>

          <div class="cc-final-review">
            <div class="cc-review-block">
              <span>Plik</span>
              <strong x-text="file.name || 'nie dodano pliku'"></strong>
              <small x-show="file.pages"><template x-text="file.pages"></template> stron · <template x-text="file.colored"></template> kolorowych</small>
              <button type="button" class="cc-review-edit" @click="go('#plik')">Zmień <i class="fas fa-pen" aria-hidden="true"></i></button>
            </div>
            <div class="cc-review-block">
              <span>Druk</span>
              <strong x-text="print.color === 'bw' ? 'czarno-biały' : 'kolor'"></strong>
              <small x-text="print.sided === 'simplex' ? 'jednostronnie' : 'dwustronnie'"></small>
              <button type="button" class="cc-review-edit" @click="go('#druk')">Zmień <i class="fas fa-pen" aria-hidden="true"></i></button>
            </div>
            <div class="cc-review-block">
              <span>Oprawa</span>
              <strong x-text="bindingName() || 'nie wybrano'"></strong>
              <small><template x-text="print.copies"></template> egzemplarz(e)</small>
              <button type="button" class="cc-review-edit" @click="go('#oprawa')">Zmień <i class="fas fa-pen" aria-hidden="true"></i></button>
            </div>
            <div class="cc-review-block">
              <span>Okładka</span>
              <strong x-text="coverName() || 'nie wybrano'"></strong>
              <small x-show="cover === 'custom'" x-text="coverText || '—'"></small>
              <button type="button" class="cc-review-edit" @click="go('#okladka')">Zmień <i class="fas fa-pen" aria-hidden="true"></i></button>
            </div>
            <div class="cc-review-block">
              <span>Odbiór / dostawa</span>
              <strong x-text="deliveryName() || 'nie wybrano'"></strong>
              <small x-text="dateLbl()"></small>
              <button type="button" class="cc-review-edit" @click="go('#odbiór')">Zmień <i class="fas fa-pen" aria-hidden="true"></i></button>
            </div>
          </div>

          <div class="cc-summary-card cc-summary-full">
            <div class="cc-price-row"><span>Druk</span><strong x-text="fmt(printTotal())"></strong></div>
            <div class="cc-price-row"><span>Oprawa</span><strong x-text="fmt(bindingPrice())"></strong></div>
            <div class="cc-price-row"><span>Personalizacja (okładka)</span><strong x-text="fmt(coverPrice())"></strong></div>
            <template x-if="deliveryPrice() !== null">
              <div class="cc-price-row"><span>Dostawa</span><strong x-text="fmt(deliveryPrice())"></strong></div>
            </template>
            <template x-if="deliveryPrice() === null">
              <div class="cc-price-row"><span>Dostawa</span><strong class="cc-price-status">nie wybrano</strong></div>
            </template>
            <footer class="cc-summary-total"><span>Razem brutto</span><strong x-text="fmt(total())"></strong></footer>
          </div>

          <div class="cc-final-actions">
            <button type="button" class="btn-magenta" disabled>
              Zobacz przykładowe podsumowanie <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
            </button>
            <p class="cc-summary-demo">Wersja demonstracyjna. Nie przesyła plików ani nie składa zamówień.<br>Po wdrożeniu ten przycisk przejdzie do wyceny lub płatności.</p>
          </div>
        </section>
      </div>
    </div>

    {{-- Sticky summary (desktop) --}}
    <aside class="cc-summary" aria-label="Podsumowanie zamówienia">
      <div class="cc-summary-card">
        <div class="cc-summary-head">
          <strong><i class="fas fa-file-pdf mr-2 text-magenta" aria-hidden="true"></i>Twoja praca</strong>
          <ul>
            <li x-text="file.name || 'nie dodano pliku'"></li>
            <template x-if="file.pages"><li><template x-text="file.pages"></template> stron · <template x-text="file.colored"></template> kolorowych</li></template>
            <li><template x-text="print.copies"></template> egzemplarz(e) · <span x-text="bindingName() || 'oprawa?'"></span></li>
          </ul>
        </div>

        <template x-if="!file.name">
          <div class="cc-price-row"><span>Druk</span><strong class="cc-price-status">po analizie PDF</strong></div>
        </template>
        <template x-if="file.name">
          <div class="cc-price-row"><span>Druk</span><strong x-text="fmt(printTotal())"></strong></div>
        </template>
        <div class="cc-price-row"><span>Oprawa</span><strong x-text="fmt(bindingPrice())"></strong></div>
        <div class="cc-price-row"><span>Personalizacja</span><strong x-text="fmt(coverPrice())"></strong></div>
        <template x-if="deliveryPrice() !== null">
          <div class="cc-price-row"><span>Dostawa</span><strong x-text="fmt(deliveryPrice())"></strong></div>
        </template>
        <template x-if="deliveryPrice() === null">
          <div class="cc-price-row"><span>Dostawa</span><strong class="cc-price-status">nie wybrano</strong></div>
        </template>

        <div class="cc-summary-term"><span>Termin</span><strong x-text="dateLbl()"></strong></div>

        <div class="cc-summary-total"><span>Razem brutto</span><strong x-text="fmt(total())"></strong></div>

        <div class="cc-summary-actions">
          <button type="button" class="btn-magenta" @click="go('#podsumowanie')">Przejdź do podsumowania <i class="fas fa-arrow-down ml-2" aria-hidden="true"></i></button>
          <p class="cc-summary-demo">Wersja demonstracyjna — ceny poglądowe, nie obiegają one żadnego zamówienia.</p>
        </div>
      </div>
    </aside>

    {{-- Mobile order bar (shares configurator state) --}}
    <div class="cc-mobile-bar">
      <div class="cc-mobile-bar-total">
        <span>Razem brutto</span>
        <strong x-text="fmt(total())"></strong>
      </div>
      <button type="button" class="btn-magenta" @click="go('#podsumowanie')">Podsumowanie <i class="fas fa-arrow-up ml-2" aria-hidden="true"></i></button>
    </div>
  </div>
</main>
@endsection
