@props(['preview', 'variant' => 'inline'])

{{-- Live cover preview. Reads the ccConfigurator Alpine state (cover, colors, texts), so it only works inside it. --}}
@php
  $zones = $preview['zones'];
  $font = $preview['font'];
  $fontStack = ($font ? "'".$font['family']."', " : '')."'Roboto Slab', serif";
  $aspect = $preview['aspect']['width'] / $preview['aspect']['height'];
@endphp

@if ($font && $variant === 'aside')
  @push('head')
    <style>@font-face { font-family: '{{ $font['family'] }}'; src: url('{{ $font['url'] }}') format('{{ $font['format'] }}'); font-display: swap; }</style>
  @endpush
@endif

<div class="cc-cover-preview cc-cover-preview--{{ $variant }}" style="--cc-cover-aspect: {{ round($aspect, 4) }}">
  <div class="cc-cover-stage"
       style="--cc-cover-font: {{ $fontStack }}"
       :class="{ 'is-placeholder': !coverPhotoUrl(), 'is-mapping': coverMapping }"
       :style="{ backgroundColor: coverColorHex() }"
       role="img"
       :aria-label="'Podgląd okładki, kolor: ' + coverColorName()">
    <template x-if="coverPhotoUrl()">
      <img class="cc-cover-photo" :src="coverPhotoUrl()" alt="">
    </template>

    <div class="cc-cover-zone cc-cover-zone--logo" data-zone="logo" style="{{ $zones['logo']['style'] ?? '' }}" x-show="coverMapping || cover === 'standard'">
      @if ($zones['logo']['tint'] ?? false)
        <template x-if="coverLogoUrl()">
          <span class="cc-cover-logo" :style="{ '--cc-logo': 'url(' + coverLogoUrl() + ')', backgroundColor: imprintHex() }"></span>
        </template>
      @else
        <template x-if="coverLogoUrl()">
          <img :src="coverLogoUrl()" alt="">
        </template>
      @endif
      <template x-if="!coverLogoUrl()">
        <span class="cc-cover-logo-placeholder" :style="{ color: imprintHex(), borderColor: imprintHex() }" x-text="university.toUpperCase()"></span>
      </template>
    </div>

    <div class="cc-cover-zone cc-cover-text" data-zone="title" style="{{ $zones['title']['style'] ?? '' }}" :style="{ color: imprintHex() }" x-show="coverMapping || cover === 'standard'">
      <span x-text="coverTitleName()"></span>
    </div>

    <div class="cc-cover-zone cc-cover-text" data-zone="custom" style="{{ $zones['custom']['style'] ?? '' }}" :style="{ color: imprintHex() }" x-show="coverMapping || cover === 'custom'" x-effect="measureCoverOverflow($el, coverText, cover)">
      <span x-text="coverText || 'Własny napis'"></span>
    </div>
  </div>

  <p class="cc-cover-caption"><strong x-text="coverColorName()"></strong> · podgląd poglądowy</p>
</div>
