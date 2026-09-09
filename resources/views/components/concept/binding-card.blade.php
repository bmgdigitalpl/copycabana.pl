@props(['group' => '', 'value' => '', 'model' => '', 'label' => '', 'thickness' => 'soft', 'price' => null, 'hint' => ''])

<label class="cc-option cc-binding-option">
  <input type="radio" name="{{ $group }}" value="{{ $value }}" x-model="{{ $model }}">
  <span class="cc-option-body cc-binding-body">
    <span class="cc-option-check"><i class="fas fa-check" aria-hidden="true"></i><em>Wybrano</em></span>
    <span class="cc-book cc-book--{{ $thickness }}" aria-hidden="true">
      <span class="cc-book-cover"></span>
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
