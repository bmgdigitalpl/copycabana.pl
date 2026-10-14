@props(['prefix' => 'inpost'])

@push('head')
  <link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css">
@endpush

@push('scripts')
  <script src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>
@endpush

<template x-if="delivery === 'parcel'">
  <div class="cc-inpost-picker" x-transition.opacity.duration.200ms>
    <div class="cc-inpost-head">
      <span class="cc-inpost-icon" aria-hidden="true"><i class="fas fa-box-archive"></i></span>
      <div>
        <strong id="{{ $prefix }}-paczkomat-label">Wybierz paczkomat InPost</strong>
        <p class="cc-inpost-help">Wpisz adres w wyszukiwarce na mapie i kliknij wybrany paczkomat.</p>
      </div>
    </div>

    {{-- The Geowidget SDK overwrites its mount target's className once it decides on a
         layout (e.g. adds "mobile"), so the re-skin in concept.css hooks off this wrapper
         instead of the target div itself, which the SDK never touches. --}}
    <div class="cc-inpost-map">
      <div
        id="{{ $prefix }}-easypack-map"
        role="group"
        aria-labelledby="{{ $prefix }}-paczkomat-label"
        x-init="ccMountInpostMap('{{ $prefix }}-easypack-map', '{{ $prefix }}')"
      ></div>
    </div>

    <template x-if="lockerError">
      <p class="cc-warning" x-text="lockerError"></p>
    </template>

    <p class="cc-inpost-selected" :class="parcelLocker ? 'is-selected' : ''" aria-live="polite">
      <i class="fas" :class="parcelLocker ? 'fa-circle-check' : 'fa-circle-info'" aria-hidden="true"></i>
      <span x-show="!parcelLocker">Nie wybrano jeszcze paczkomatu.</span>
      <span x-show="parcelLocker" x-cloak>Wybrany paczkomat: <strong x-text="lockerSummary()"></strong></span>
    </p>
  </div>
</template>
