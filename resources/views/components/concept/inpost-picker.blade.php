@props(['prefix' => 'inpost'])

@push('head')
  <link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css">
@endpush

@push('scripts')
  <script src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>
@endpush

<template x-if="delivery === 'parcel'">
  <div class="cc-inpost-picker" x-transition.opacity.duration.200ms>
    <div class="cc-field">
      <label id="{{ $prefix }}-paczkomat-label">Wybierz paczkomat InPost</label>
      <p class="cc-inpost-help">Znajdź swój adres w wyszukiwarce na mapie i kliknij paczkomat, aby go wybrać.</p>
      {{-- The Geowidget SDK overwrites its mount target's className once it decides on a
           layout (e.g. adds "mobile"), so the re-skin in concept.css hooks off this wrapper
           instead of the target div itself, which the SDK never touches. --}}
      <div class="cc-inpost-map">
        <div
          id="{{ $prefix }}-easypack-map"
          aria-labelledby="{{ $prefix }}-paczkomat-label"
          x-init="ccMountInpostMap('{{ $prefix }}-easypack-map', '{{ $prefix }}')"
        ></div>
      </div>
    </div>

    <template x-if="lockerError">
      <p class="cc-warning" x-text="lockerError"></p>
    </template>

    <template x-if="parcelLocker">
      <p class="cc-inpost-selected">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        <span>Wybrany paczkomat: <strong x-text="lockerSummary()"></strong></span>
      </p>
    </template>
  </div>
</template>
