@props(['prefix' => 'inpost'])

<template x-if="delivery === 'parcel'">
  <div class="cc-inpost-picker" x-transition.opacity.duration.200ms>
    <div class="cc-field">
      <label for="{{ $prefix }}-paczkomat">Wybierz paczkomat InPost</label>
      <div class="cc-inpost-search">
        <input
          id="{{ $prefix }}-paczkomat"
          type="text"
          x-model="lockerSearch"
          @keydown.enter.prevent="searchLockers()"
          placeholder="np. 40-007 lub Katowice"
          autocomplete="postal-code">
        <button type="button" class="btn-geel" @click="searchLockers()" :disabled="lockerLoading">
          <span x-show="!lockerLoading">Szukaj</span>
          <span x-show="lockerLoading">Szukamy...</span>
          <i class="fas fa-magnifying-glass ml-2" aria-hidden="true"></i>
        </button>
      </div>
      <p class="cc-inpost-help">Wpisz kod pocztowy albo miasto, aby znaleźć najbliższe punkty.</p>
    </div>

    <template x-if="lockerError">
      <p class="cc-warning" x-text="lockerError"></p>
    </template>

    <template x-if="lockers.length">
      <div class="cc-inpost-results" role="radiogroup" aria-label="Dostępne paczkomaty InPost">
        <template x-for="point in lockers" :key="point.name">
          <button
            type="button"
            class="cc-inpost-point"
            :class="{ 'is-selected': parcelLocker && parcelLocker.name === point.name }"
            role="radio"
            :aria-checked="parcelLocker && parcelLocker.name === point.name ? 'true' : 'false'"
            @click="pickLocker(point)">
            <span class="cc-inpost-point-mark" aria-hidden="true"><i class="fas fa-location-dot"></i></span>
            <span class="cc-inpost-point-copy">
              <strong x-text="point.name"></strong>
              <span x-text="point.address + (point.city ? ', ' + point.city : '')"></span>
              <small x-show="point.opening_hours" x-text="point.opening_hours"></small>
            </span>
            <i class="cc-inpost-point-check fas fa-check" aria-hidden="true"></i>
          </button>
        </template>
      </div>
    </template>

    <template x-if="parcelLocker">
      <p class="cc-inpost-selected">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        <span>Wybrany paczkomat: <strong x-text="lockerSummary()"></strong></span>
      </p>
    </template>
  </div>
</template>
