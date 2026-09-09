@props(['label' => '', 'status' => null, 'value' => ''])

<div class="cc-price-row">
  @if ($status)
    <span>{{ $label }}</span>
    <strong class="cc-price-status">{{ $status }}</strong>
  @else
    <span>{{ $label }}</span>
    <strong>{{ $value }}</strong>
  @endif
</div>
