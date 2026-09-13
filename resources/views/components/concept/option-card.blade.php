@props(['group' => '', 'value' => '', 'model' => '', 'label' => '', 'price' => null, 'hint' => ''])

<label {{ $attributes->merge(['class' => 'cc-option']) }}>
  <input type="radio" name="{{ $group }}" value="{{ $value }}" x-model="{{ $model }}">
  <span class="cc-option-body">
    <span class="cc-option-check"><i class="fas fa-check" aria-hidden="true"></i><em>Wybrano</em></span>
    <span class="cc-option-main">
      <strong>{{ $label }}</strong>
      @if ($hint)
        <em>{{ $hint }}</em>
      @endif
    </span>
    @if ($price !== null)
      <span class="cc-option-price">{{ $price }}</span>
    @endif
    {{ $slot }}
  </span>
</label>
