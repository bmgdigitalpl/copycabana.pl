@props(['label' => null, 'dark' => false, 'align' => 'left'])

<div class="cc-section-head reveal {{ $dark ? 'cc-section-head--dark' : '' }} cc-section-head--{{ $align }}">
  @if ($label)
    <p class="cc-section-label">{{ $label }}</p>
  @endif
  <h2 class="cc-section-title">{{ $slot }}</h2>
  @if (isset($lead))
    <p class="cc-section-lead">{{ $lead }}</p>
  @endif
</div>
