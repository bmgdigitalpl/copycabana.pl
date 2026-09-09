@extends('layouts.main')

@section('title', 'Druk PDF — konfigurator dokumentów | CopyCabana')
@section('description', 'Wrzucasz PDF, ustawiasz kolor, strony kartki, wykończenie i odbiór. Konfigurator druku dokumentów — CopyCabana, Katowice.')

@section('content')
<main class="cc-page">
  {{-- Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Druk dokumentów PDF</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Dokument.<em>Wydruk bez narzutu.</em></h1>
        <p class="reveal reveal-delay-2">Materiały do nauki, instrukcje, umowy i codzienne dokumenty. Wrzucasz PDF, ustawiasz kolor, strony kartki i odbiór — resztę pokażemy jako jasną cenę.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="#plik" @click.prevent="ccGo('#plik')" class="btn-magenta inline-block">Dodaj dokument PDF <i class="fas fa-upload ml-2" aria-hidden="true"></i></a>
          <a href="#wykończenie" @click.prevent="ccGo('#wykończenie')" class="btn-geel inline-block">Zobacz wykończenie</a>
        </div>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-hero-stack" aria-hidden="true">
          <div class="cc-hero-card cc-hero-card--b">
            <img src="{{ asset('images/produkty/product-03.png') }}" alt="">
          </div>
          <div class="cc-hero-card cc-hero-card--a">
            <div class="cc-hero-mini-report" aria-hidden="true">
              <span>PDF</span>
              <strong>32 strony</strong>
              <p>druk dwustronny · czarno-biały</p>
            </div>
          </div>
          <div class="cc-hero-card cc-hero-card--tag">Wydruk PDF</div>
        </div>
      </div>
    </div>
  </section>

  <div class="cc-container cc-mt" style="padding-top:1.5rem">
    <x-concept.demo-notice>Wersja demonstracyjna konfiguratora</x-concept.demo-notice>
  </div>

  {{-- Configurator --}}
  <div x-data="ccPdfConfigurator()" class="cc-container cc-config cc-config-page">
    <div class="cc-config-main">
      <div class="cc-steps">

        {{-- Step 01 — Plik --}}
        <section class="cc-step" id="plik">
          <div class="cc-step-head">
            <span class="cc-step-num">01</span>
            <div><h2>Zacznij od swojego PDF.</h2><p>Dodaj kompletny dokument. Sprawdź strony, zanim zamówisz.</p></div>
          </div>

          <div x-data="ccDropzone('pdf', { pages: 32, colored: 4 })"
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
                <p>Format PDF. Demo — pliki nie są wysyłane nigdzie.</p>
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
                <p>Pozostań na tej stronie.</p>
              </div>
            </template>

            <template x-if="status === 'analyzing'">
              <div class="cc-dropzone-inner">
                <span class="cc-spinner" aria-hidden="true"></span>
                <h3>Analizujemy dokument</h3>
                <p>Sprawdzamy liczbę stron i strony kolorowe.</p>
              </div>
            </template>

            <template x-if="status === 'ready'">
              <div class="cc-dropzone-inner">
                <span class="cc-dropzone-success"><i class="fas fa-check" aria-hidden="true"></i></span>
                <h3>PDF gotowy</h3>
                <p class="cc-file-name"><i class="fas fa-file-pdf" aria-hidden="true"></i><span x-text="name"></span></p>
                <div class="cc-file-report">
                  <div><span>Strony</span><strong x-text="pages"></strong></div>
                  <div><span>Kolorowe</span><strong x-text="colored"></strong></div>
                  <div><span>Status</span><strong>OK</strong></div>
                </div>
                <div class="cc-dropzone-actions">
                  <button type="button" class="btn-geel" @click="go('#druk')">Wybieram ustawienia <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></button>
                </div>
              </div>
            </template>

            <template x-if="status === 'invalid'">
              <div class="cc-dropzone-inner">
                <i class="cc-dropzone-icon fas fa-triangle-exclamation" aria-hidden="true"></i>
                <h3>To nie wygląda na PDF</h3>
                <p>Wybierz plik w formacie PDF.</p>
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
            <div><h2>Wybierz, jak wydrukujemy strony.</h2><p>Kolor, strony kartki i liczba egzemplarzy.</p></div>
          </div>

          <p class="cc-step-label">Kolor</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="pdf-kolor" value="bw" model="print.color" label="czarno-biały" hint="Standard dla dokumentów." />
            <x-concept.option-card group="pdf-kolor" value="color" model="print.color" label="kolor" hint="Gdy liczy się grafika i zdjęcia." />
          </div>

          <p class="cc-step-label">Strony kartki</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="pdf-strony" value="simplex" model="print.sided" label="jednostronnie" hint="Każda strona PDF na osobnej kartce." />
            <x-concept.option-card group="pdf-strony" value="duplex" model="print.sided" label="dwustronnie" hint="Druk po obu stronach kartki." />
          </div>

          <p class="cc-step-label">Liczba egzemplarzy</p>
          <div class="cc-qty">
            <button type="button" @click="print.copies = print.copies > 1 ? print.copies - 1 : 1" aria-label="Mniej egzemplarzy"><i class="fas fa-minus" aria-hidden="true"></i></button>
            <strong x-text="print.copies"></strong>
            <button type="button" @click="print.copies = print.copies < 50 ? print.copies + 1 : 50" aria-label="Więcej egzemplarzy"><i class="fas fa-plus" aria-hidden="true"></i></button>
          </div>
        </section>

        {{-- Step 03 — Wykończenie --}}
        <section class="cc-step" id="wykończenie">
          <div class="cc-step-head">
            <span class="cc-step-num">03</span>
            <div><h2>Opakować dokumenty czy zostawić luźno?</h2><p>Opcjonalne wykończenie. Żadna opcja nie jest narzucana.</p></div>
          </div>

          <div class="cc-step-grid cc-step-grid--3">
            @foreach ([
              ['id' => 'none', 'label' => 'Bez wykończenia', 'hint' => 'Wydruk gotowy do odbioru.', 'price' => '0,00 zł'],
              ['id' => 'staples', 'label' => 'Spinanie zeszytowe', 'hint' => 'Dwa zszywki wzdłuż grzbietu.', 'price' => '4,00 zł'],
              ['id' => 'folder', 'label' => 'Teczka', 'hint' => 'Gładka teczka zamykana na gumkę.', 'price' => '8,00 zł'],
              ['id' => 'channel', 'label' => 'Oprawa kanałowa', 'hint' => 'Klejony blok dla większych dokumentów.', 'price' => '18,00 zł'],
            ] as $f)
              <x-concept.option-card group="pdf-finish" value="{{ $f['id'] }}" model="finish" label="{{ $f['label'] }}" hint="{{ $f['hint'] }}" price="{{ $f['price'] }}" />
            @endforeach
          </div>
          <p class="cc-step-micro">Ceny wykończenia są przykładowe i mogą się zmienić przed produkcją.</p>
        </section>

        {{-- Step 04 — Odbiór --}}
        <section class="cc-step" id="odbior">
          <div class="cc-step-head">
            <span class="cc-step-num">04</span>
            <div><h2>Kiedy i gdzie odbierzesz dokumenty?</h2><p>Odbiór w Katowicach albo wysyłka. Termin produkcji podajemy osobno od doręczenia.</p></div>
          </div>

          <div class="cc-step-grid cc-step-grid--3">
            @foreach (['pickup', 'parcel', 'courier'] as $d)
              <x-concept.option-card
                group="pdf-odbior"
                value="{{ $d }}"
                model="delivery"
                label="{{ ['pickup' => 'Odbiór w Katowicach', 'parcel' => 'Paczkomat', 'courier' => 'Kurier'][$d] }}"
                hint="{{ ['pickup' => 'ul. Bankowa 11, 40-007 Katowice', 'parcel' => 'demo — integracja do potwierdzenia', 'courier' => 'demo — integracja do potwierdzenia'][$d] }}"
                price="{{ ['pickup' => '0,00 zł', 'parcel' => '12,00 zł', 'courier' => '18,00 zł'][$d] }}" />
            @endforeach
          </div>

          <div class="cc-field">
            <label for="pdf-data">Potrzebuję najpóźniej</label>
            <input id="pdf-data" type="date" x-model="byDate" :min="new Date().toISOString().split('T')[0]">
          </div>

          <template x-if="deliveryLate()">
            <p class="cc-warning" x-transition.opacity.duration.200ms x-text="deliveryLate()"></p>
          </template>
        </section>

        {{-- Step 05 — Dane --}}
        <section class="cc-step" id="dane">
          <div class="cc-step-head">
            <span class="cc-step-num">05</span>
            <div><h2>Podaj dane do zamówienia.</h2><p>Demo — nic nie jest wysyłane.</p></div>
          </div>

          <div class="cc-field-grid">
            <div class="cc-field">
              <label for="pdf-imie">Imię i nazwisko</label>
              <input id="pdf-imie" type="text" x-model="form.name" placeholder="Jan Kowalski">
            </div>
            <div class="cc-field">
              <label for="pdf-email">E-mail</label>
              <input id="pdf-email" type="email" x-model="form.email" placeholder="jan@example.com">
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
                <label for="pdf-firma">Nazwa firmy</label>
                <input id="pdf-firma" type="text" x-model="form.company" placeholder="Nazwa spółki">
              </div>
              <div class="cc-field">
                <label for="pdf-nip">NIP</label>
                <input id="pdf-nip" type="text" x-model="form.nip" placeholder="0000000000">
              </div>
            </div>
          </template>
        </section>

        {{-- Step 06 — Podsumowanie --}}
        <section class="cc-step" id="podsumowanie">
          <div class="cc-step-head">
            <span class="cc-step-num">06</span>
            <div><h2>Sprawdź wszystko przed drukiem.</h2><p>Każdą pozycję możesz zmienić.</p></div>
          </div>

          <div class="cc-summary-card cc-summary-full">
            <div class="cc-summary-head">
              <strong x-text="file.name || 'nie dodano pliku'"></strong>
              <ul>
                <template x-if="file.pages"><li><template x-text="file.pages"></template> stron · <template x-text="file.colored"></template> kolorowych</li></template>
                <li x-text="(print.sided === 'simplex' ? 'jednostronnie' : 'dwustronnie') + ' · ' + print.copies + ' egz.'"></li>
              </ul>
            </div>
            <template x-if="!file.name">
              <div class="cc-price-row"><span>Druk</span><strong class="cc-price-status">po analizie PDF</strong></div>
            </template>
            <template x-if="file.name">
              <div class="cc-price-row"><span>Druk</span><strong x-text="fmt(printTotal())"></strong></div>
            </template>
            <div class="cc-price-row"><span>Wykończenie</span><strong x-text="fmt(finishPrice())"></strong></div>
            <template x-if="deliveryPrice() !== null">
              <div class="cc-price-row"><span>Dostawa</span><strong x-text="fmt(deliveryPrice())"></strong></div>
            </template>
            <template x-if="deliveryPrice() === null">
              <div class="cc-price-row"><span>Dostawa</span><strong class="cc-price-status">nie wybrano</strong></div>
            </template>
            <div class="cc-summary-term"><span>Termin</span><strong x-text="dateLbl()"></strong></div>
            <footer class="cc-summary-total"><span>Razem brutto</span><strong x-text="fmt(total())"></strong></footer>
          </div>

          <div class="cc-final-actions">
            <button type="button" class="btn-magenta" disabled>Zobacz przykładowe podsumowanie <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></button>
            <p class="cc-summary-demo">Wersja demonstracyjna. Nie przesyła plików ani nie składa zamówień.</p>
          </div>
        </section>
      </div>
    </div>

    {{-- Sticky summary (desktop) --}}
    <aside class="cc-summary" aria-label="Podsumowanie zamówienia">
      <div class="cc-summary-card">
        <div class="cc-summary-head">
          <strong><i class="fas fa-file-pdf mr-2 text-magenta" aria-hidden="true"></i>Twój dokument</strong>
          <ul>
            <li x-text="file.name || 'nie dodano pliku'"></li>
            <li x-text="(print.sided === 'simplex' ? 'jednostronnie' : 'dwustronnie') + ' · ' + print.copies + ' egz.'"></li>
          </ul>
        </div>
        <template x-if="!file.name">
          <div class="cc-price-row"><span>Druk</span><strong class="cc-price-status">po analizie PDF</strong></div>
        </template>
        <template x-if="file.name">
          <div class="cc-price-row"><span>Druk</span><strong x-text="fmt(printTotal())"></strong></div>
        </template>
        <div class="cc-price-row"><span>Wykończenie</span><strong x-text="fmt(finishPrice())"></strong></div>
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
          <p class="cc-summary-demo">Wersja demonstracyjna — ceny poglądowe.</p>
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
