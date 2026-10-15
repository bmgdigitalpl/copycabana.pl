@props(['preview', 'colors', 'university' => 'us', 'title' => 'Praca magisterska'])

{{-- Cover on the home page with a row of colour swatches. Self-contained Alpine state, unlike x-concept.cover-preview, which needs ccConfigurator. --}}
@php
  $zones = $preview['zones'];
  $font = $preview['font'];
  $fontStack = ($font ? "'".$font['family']."', " : '')."'Century Schoolbook', 'Roboto Slab', serif";
  $aspect = $preview['aspect']['width'] / $preview['aspect']['height'];
  $logo = $preview['logos'][$university] ?? null;
@endphp

@if ($font)
  @push('head')
    <style>@font-face { font-family: '{{ $font['family'] }}'; src: url('{{ $font['url'] }}') format('{{ $font['format'] }}'); font-weight: 700; font-display: swap; }</style>
  @endpush
@endif

<div class="cc-cover-preview cc-cover-preview--home"
     style="--cc-cover-aspect: {{ round($aspect, 4) }}"
     x-data="{
       colors: {{ Illuminate\Support\Js::from($colors) }},
       foil: {{ Illuminate\Support\Js::from($preview['foils']['gold'] ?? null) }},
       current: 0,
       timer: null,
       color() { return this.colors[this.current]; },
       isLight() {
         const channels = [1, 3, 5].map((start) => {
           const value = parseInt(this.color().hex.slice(start, start + 2), 16) / 255;
           return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
         });
         return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2] > 0.3;
       },
       fill() {
         return this.isLight() || !this.foil
           ? { backgroundColor: this.isLight() ? '#111827' : '#D4AF37', backgroundImage: 'none' }
           : { backgroundColor: '#D4AF37', backgroundImage: this.foil };
       },
       textStyle() {
         return this.isLight() || !this.foil
           ? { color: this.isLight() ? '#111827' : '#D4AF37', backgroundImage: 'none' }
           : { color: 'transparent', backgroundImage: this.foil, backgroundClip: 'text', webkitBackgroundClip: 'text' };
       },
       pick(index) { this.stop(); this.current = index; },
       stop() { clearInterval(this.timer); this.timer = null; },
       start() {
         if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
         this.timer = setInterval(() => { this.current = (this.current + 1) % this.colors.length; }, 3200);
       }
     }"
     x-init="start()">
  <div class="cc-cover-stage"
       style="--cc-cover-font: {{ $fontStack }}; background-size: {{ $preview['textureSize'] }}% auto"
       :style="{ backgroundColor: color().hex, backgroundImage: color().texture ? 'url(' + color().texture + ')' : 'none' }"
       role="img"
       :aria-label="'Okładka pracy dyplomowej, kolor: ' + color().label">
    <div class="cc-cover-zone cc-cover-zone--logo" style="{{ $zones['logo']['style'] ?? '' }}">
      @if ($logo)
        <span class="cc-cover-logo" :style="{ '--cc-logo': 'url({{ $logo }})', ...fill() }"></span>
      @endif
    </div>

    <div class="cc-cover-zone cc-cover-text" style="{{ $zones['title']['style'] ?? '' }}" :style="textStyle()">
      <span>{{ $title }}</span>
    </div>
  </div>

  <div class="cc-home-swatches" role="group" aria-label="Kolor okładki">
    <template x-for="(item, index) in colors" :key="item.key">
      <button type="button"
              class="cc-home-swatch"
              :class="{ 'is-active': index === current }"
              :style="{ backgroundColor: item.hex }"
              :aria-label="item.label"
              :aria-pressed="index === current ? 'true' : 'false'"
              @click="pick(index)"></button>
    </template>
  </div>

  <p class="cc-cover-caption"><strong x-text="color().label"></strong> · jeden z kolorów okładek w konfiguratorze</p>
</div>
