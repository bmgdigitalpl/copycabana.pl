@props(['group' => '', 'value' => '', 'model' => '', 'label' => '', 'price' => null, 'hint' => ''])

<label class="cc-option cc-binding-option">
  <input type="radio" name="{{ $group }}" value="{{ $value }}" x-model="{{ $model }}">
  <span class="cc-option-body cc-binding-body">
    <span class="cc-option-check"><i class="fas fa-check" aria-hidden="true"></i></span>
    <span class="cc-binding-image-placeholder" aria-hidden="true">
      <i class="fas fa-image"></i>
      <span>Miejsce na zdjęcie</span>
    </span>
    <span class="cc-binding-meta">
      <strong>{{ $label }}</strong>
      <em>{{ $hint }}</em>
      @if ($price !== null)
        <span class="cc-option-price">{{ $price }}</span>
      @endif
    </span>
    {{ $slot }}
  </span>
</label>
