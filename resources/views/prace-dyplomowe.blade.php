@extends('layouts.main')

@section('title', 'Prace dyplomowe — druk i oprawa | CopyCabana')
@section('description', 'Kompletny PDF pracy dyplomowej, wybór oprawy, okładki i odbioru. Konfigurator druku — CopyCabana, Katowice.')

@section('content')
<script>
  window.copyCabanaThesisPricing = @js($thesisPricing);
</script>
<main class="cc-page">
  {{-- Hero --}}
  <section class="cc-hero">
    <div class="cc-container cc-container--wide cc-hero-grid">
      <div class="cc-hero-copy">
        <p class="cc-hero-overline reveal">Druk i oprawa prac dyplomowych</p>
        <h1 class="cc-hero-title reveal reveal-delay-1">Praca napisana.<em>Teraz druk i oprawa.</em></h1>
        <p class="reveal reveal-delay-2">Dodaj kompletny PDF, wybierz druk oraz oprawę twardą, miękką lub kanałową. Przed zamówieniem sprawdzisz pełną cenę i dostępny termin odbioru lub dostawy.</p>
        <div class="cc-hero-actions reveal reveal-delay-3">
          <a href="#plik" @click.prevent="ccGo('#plik')" class="btn-magenta inline-block">Dodaj pracę w PDF <i class="fas fa-upload ml-2" aria-hidden="true"></i></a>
          <a href="#oprawa" @click.prevent="ccGo('#oprawa')" class="btn-outline-light inline-block">Najpierw sprawdź oprawy</a>
        </div>
        <p class="cc-hero-hint reveal reveal-delay-4"><i class="fas fa-circle-info" aria-hidden="true"></i> Zazwyczaj realizujemy zamówienia w 24h. Potwierdzenie zależy od pliku, oprawy i obciążenia — pokażemy je w podsumowaniu.</p>
      </div>

      <div class="cc-hero-visual reveal reveal-delay-2">
        <div class="cc-hero-stack" aria-hidden="true">
          <div class="cc-hero-card cc-hero-card--single">
            <img src="{{ $thesisProduct->imageUrl() }}" alt="">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Configurator --}}
  <div x-data="ccConfigurator()" class="cc-container cc-container--wide cc-config cc-config-page">
    <div class="cc-config-main">
      <div class="cc-steps">
        <section class="cc-step" id="plik">
          <div class="cc-step-head">
            <span class="cc-step-num">01</span>
            <div><h2>Zacznij od swojej pracy.</h2><p>Dodaj jeden kompletny PDF. Sprawdź, czy zawiera wszystkie strony we właściwej kolejności.</p></div>
          </div>

          <div x-data="ccDropzone('thesis')"
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
                 <p>Format PDF, maksymalnie {{ config('business.thesis.max_pages') }} stron i 20 MB.</p>
                <div class="cc-dropzone-actions">
                  <label class="btn-magenta" style="cursor:pointer">
                    <i class="fas fa-folder-open mr-2" aria-hidden="true"></i>Wybierz plik PDF
                    <input type="file" accept=".pdf,application/pdf" class="sr-only" @change="change($event)">
                  </label>
                </div>
              </div>
            </template>

            <template x-if="status === 'uploading'">
              <div class="cc-dropzone-inner">
                <i class="cc-dropzone-icon fas fa-cloud-arrow-up" aria-hidden="true"></i>
                <h3 x-text="'Przesyłamy ' + name"></h3>
                <div class="cc-dropzone-progress"><span></span></div>
                 <p>Pozostań na tej stronie. Plik jest przesyłany bezpiecznie do analizy.</p>
              </div>
            </template>

            <template x-if="status === 'analyzing'">
              <div class="cc-dropzone-inner">
                <span class="cc-spinner" aria-hidden="true"></span>
                <h3>Analizujemy dokument</h3>
                 <p>Sprawdzamy poprawność dokumentu i liczbę stron.</p>
              </div>
            </template>

            <template x-if="status === 'ready'">
              <div class="cc-dropzone-inner">
                <span class="cc-dropzone-success"><i class="fas fa-check" aria-hidden="true"></i></span>
                <h3>PDF gotowy do konfiguracji</h3>
                <p class="cc-file-name"><i class="fas fa-file-pdf" aria-hidden="true"></i><span x-text="name"></span></p>
                 <div class="cc-file-report">
                   <div><span>Strony</span><strong x-text="pages"></strong></div>
                    <div><span>Czarno-białe</span><strong x-text="bwPages"></strong></div>
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
                 <h3>Nie udało się przygotować pliku</h3>
                 <p class="cc-dropzone-error" x-text="message || ('Plik: ' + name)"></p>
                 <p>Wybierz poprawny, niezabezpieczony plik PDF i spróbuj ponownie.</p>
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
            <div><h2>Wybierz, jak wydrukujemy strony.</h2><p>Ustaw kolor i strony kartki.</p></div>
          </div>

          <p class="cc-step-label">Kolor</p>
           <div class="cc-step-grid">
             <x-concept.option-card group="druk-kolor" value="bw" model="print.color" label="wszystko czarno-białe" hint="Także wykryte strony kolorowe wydrukujemy w czerni i bieli." />
              <x-concept.option-card group="druk-kolor" value="mixed" model="print.color" label="kolorowe jako kolorowe" hint="Strony kolorowe drukujemy w kolorze, pozostałe czarno-biało." />
          </div>

          <p class="cc-step-label">Strony kartki</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="druk-strony" value="simplex" model="print.sided" label="jednostronnie" hint="Każda strona PDF na osobnej kartce." />
            <x-concept.option-card group="druk-strony" value="duplex" model="print.sided" label="dwustronnie" hint="Druk po obu stronach kartki. Oszczędza papier i objętość." />
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
            @foreach ($thesisPricing['bindings'] as $bindingKey => $b)
              <x-concept.binding-card
                group="oprawa"
                value="{{ $bindingKey }}"
                model="binding"
                label="{{ $b['label'] }}"
                hint="{{ $b['hint'] ?? '' }}"
                price="{{ number_format((float) $b['price'], 2, ',', ' ') }} zł" />
            @endforeach
          </div>

          <div class="cc-summary-copies">
            <div>
              <p class="cc-step-label">Liczba egzemplarzy</p>
              <p class="cc-step-micro">Ustaw nakład dla wybranej oprawy.</p>
            </div>
            <div class="cc-qty">
              <button type="button" @click="print.copies = print.copies > 1 ? print.copies - 1 : 1" aria-label="Mniej egzemplarzy"><i class="fas fa-minus" aria-hidden="true"></i></button>
              <strong x-text="print.copies"></strong>
              <button type="button" @click="print.copies = Math.min(maxCopies, print.copies + 1)" aria-label="Więcej egzemplarzy"><i class="fas fa-plus" aria-hidden="true"></i></button>
            </div>
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
            @foreach ($thesisPricing['covers'] as $c => $coverOption)
              <x-concept.option-card
                group="okladka"
                value="{{ $c }}"
                model="cover"
                label="{{ $coverOption['label'] }}"
                hint="{{ $coverOption['hint'] ?? '' }}"
                 price="{{ number_format((float) $coverOption['price'], 2, ',', ' ') }} zł" />
            @endforeach
          </div>

          <div class="cc-field-grid" x-show="cover === 'standard'" x-transition.opacity.duration.200ms>
            <div class="cc-field">
              <label for="uczelnia">Uczelnia</label>
              <select id="uczelnia" x-model="university">
                @foreach ($thesisPricing['universities'] as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="cc-field">
              <label for="napis-okladka">Napis na okładce</label>
              <select id="napis-okladka" x-model="coverTitle">
                @foreach ($thesisPricing['cover_titles'] as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="cc-field" x-show="cover === 'custom'" x-transition.opacity.duration.200ms>
            <label for="okladka-tekst">Treść napisu na okładce</label>
            <input id="okladka-tekst" type="text" x-model="coverText" placeholder="np. tytuł pracy · imię i nazwisko">
          </div>

          <div class="cc-field-grid" x-show="cover === 'standard' || cover === 'custom'" x-transition.opacity.duration.200ms>
            <div class="cc-field">
              <label for="kolor-napisu">Kolor napisu</label>
              <select id="kolor-napisu" x-model="imprintColor">
                @foreach ($thesisPricing['imprint_colors'] as $value => $color)
                  <option value="{{ $value }}">{{ $color['label'] }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <p class="cc-step-label">{{ $thesisPricing['cd']['label'] }}</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="cd" value="false" model="burnCd" label="Nie" hint="Bez dodatkowej kopii na płycie." price="0,00 zł" />
            <x-concept.option-card group="cd" value="true" model="burnCd" label="Tak" hint="Nagramy przesłany plik PDF na CD." price="{{ number_format((float) $thesisPricing['cd']['price'], 2, ',', ' ') }} zł" />
          </div>

          <p class="cc-step-label">Grawerowanie na grzbiecie</p>
          <div class="cc-step-grid">
            <x-concept.option-card group="grzbiet-grawer" value="false" model="spineEngraving" label="Nie" hint="Bez dodatkowego napisu na grzbiecie." price="0,00 zł" />
            <x-concept.option-card group="grzbiet-grawer" value="true" model="spineEngraving" label="Tak" hint="Imię i nazwisko podane w konfiguratorze wygrawerujemy na grzbiecie." price="{{ number_format((float) $thesisPricing['spine_engraving']['price'], 2, ',', ' ') }} zł" />
          </div>

          <div class="cc-field" x-show="spineEngraving === 'true'" x-transition.opacity.duration.200ms>
            <label for="grzbiet-imie-nazwisko">Imię i nazwisko na grzbiecie</label>
            <input id="grzbiet-imie-nazwisko" type="text" x-model="spineEngravingName" placeholder="np. Jan Kowalski">
          </div>

          <p class="cc-step-micro" x-show="cover === 'standard'">Przykład standardowego napisu: „Tytuł pracy dyplomowej — Imię Nazwisko”.</p>

          <div class="cc-thesis-preview" x-transition.opacity.duration.200ms>
            <div class="cc-thesis"
              :class="['cc-thesis--' + coverColor, 'cc-thesis--' + binding]"
              aria-hidden="true">
              <span class="cc-thesis-spine">
                <span class="cc-thesis-spine-name" x-show="spineEngraving === 'true'" :style="imprintStyle()" x-text="spineEngravingName || 'Imię Nazwisko'"></span>
              </span>
              <span class="cc-thesis-cover">
                <em class="cc-thesis-degree" x-show="cover !== 'none'" :style="imprintStyle()" x-text="coverHeading()"></em>
                <span class="cc-thesis-center" x-show="cover !== 'none'">
                  <strong class="cc-thesis-title" :style="imprintStyle()" x-text="activeVariant().title"></strong>
                  <span class="cc-thesis-rule"></span>
                  <span class="cc-thesis-author" :style="imprintStyle()" x-text="coverSubheading()"></span>
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
             @foreach (['pickup', 'parcel'] as $i => $d)
              <x-concept.option-card
                group="odbior"
                value="{{ $d }}"
                model="delivery"
                label="{{ ['pickup' => 'Odbiór w Katowicach', 'parcel' => 'Paczkomat', 'courier' => 'Kurier'][$d] }}"
                hint="{{ ['pickup' => 'ul. Bankowa 11, 40-007 Katowice', 'parcel' => 'Wybierz punkt z listy InPost', 'courier' => 'Dostawa pod wskazany adres'][$d] }}"
                 price="{{ number_format((float) config('business.shipping.'.$d), 2, ',', ' ') }} zł" />
            @endforeach
          </div>

          <x-concept.inpost-picker prefix="thesis" />

          <div class="cc-field">
            <label for="odbior-data">Potrzebuję pracy najpóźniej</label>
            <input id="odbior-data" type="date" x-model="byDate" :min="new Date().toISOString().split('T')[0]">
          </div>

          <template x-if="deliveryLate()">
            <p class="cc-warning" x-transition.opacity.duration.200ms x-text="deliveryLate()"></p>
          </template>
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
            <template x-if="file.pages"><li><template x-text="file.pages"></template> stron</li></template>
            <li><template x-text="print.copies"></template> egzemplarz(e) · <span x-text="bindingName() || 'oprawa?'"></span></li>
            <li x-show="cover !== 'none'" x-text="coverSummary() + ' · ' + imprintColorName()"></li>
            <li x-show="cover === 'standard'" x-text="universityName()"></li>
            <li x-show="spineEngraving === 'true'" x-text="'Grawer grzbietu: ' + (spineEngravingName || 'uzupełnij imię i nazwisko')"></li>
            <li x-text="burnCd === 'true' ? 'CD: tak' : 'CD: nie'"></li>
            <li x-show="delivery === 'parcel' && parcelLocker" x-text="lockerSummary()"></li>
          </ul>
        </div>

        <template x-if="!file.name">
          <div class="cc-price-row"><span>Druk</span><strong class="cc-price-status">po analizie PDF</strong></div>
        </template>
             <template x-if="file.name">
           <div class="cc-price-row"><span>Druk</span><strong x-text="fmt(quoteValue('print_total', printTotal()))"></strong></div>
         </template>
          <div class="cc-price-row"><span>Oprawa</span><strong x-text="fmt(quoteValue('binding_total', bindingPrice() * print.copies))"></strong></div>
          <div class="cc-price-row"><span>Personalizacja</span><strong x-text="fmt(quoteValue('cover_total', coverPrice() * print.copies))"></strong></div>
          <div class="cc-price-row"><span>Grawer grzbietu</span><strong x-text="fmt(quoteValue('spine_engraving_total', spineEngravingPrice()))"></strong></div>
          <div class="cc-price-row"><span>CD</span><strong x-text="fmt(quoteValue('cd_total', cdPrice()))"></strong></div>
          <template x-if="deliveryPrice() !== null">
            <div class="cc-price-row"><span>Dostawa</span><strong x-text="fmt(quoteValue('shipping_total', deliveryPrice()))"></strong></div>
        </template>
        <template x-if="deliveryPrice() === null">
          <div class="cc-price-row"><span>Dostawa</span><strong class="cc-price-status">nie wybrano</strong></div>
        </template>

        <div class="cc-summary-term"><span>Termin</span><strong x-text="dateLbl()"></strong></div>

        <div class="cc-summary-total"><span>Razem brutto</span><strong x-text="fmt(quoteValue('total', total()))"></strong></div>

        <div class="cc-summary-actions">
          <button type="button" class="btn-magenta" @click="addToCart()">
            <span>Dodaj do koszyka</span>
            <i class="fas fa-shopping-cart ml-2" aria-hidden="true"></i>
          </button>
          <p class="cc-summary-demo" x-show="quoteLoading">Potwierdzamy cenę na podstawie aktualnej konfiguracji.</p>
          <p class="cc-warning" x-show="quoteError || orderError" x-text="quoteError || orderError"></p>
          <p class="cc-summary-demo" x-show="quote">Cena potwierdzona przez serwer dla aktualnej konfiguracji.</p>
        </div>
      </div>
    </aside>

    {{-- Mobile order bar (shares configurator state) --}}
    <div class="cc-mobile-bar">
      <div class="cc-mobile-bar-total">
        <span>Razem brutto</span>
         <strong x-text="fmt(quoteValue('total', total()))"></strong>
      </div>
      <button type="button" class="btn-magenta" @click="addToCart()">Dodaj <i class="fas fa-shopping-cart ml-2" aria-hidden="true"></i></button>
      <p class="cc-mobile-bar-message" x-show="quoteError || orderError" x-text="quoteError || orderError"></p>
    </div>
  </div>
</main>
@endsection
