@props(['items' => []])

<div class="cc-marquee" aria-hidden="true">
  <div class="cc-marquee-fade cc-marquee-fade--left"></div>
  <div class="cc-marquee-track">
    @foreach ($items as $item)
      <span><i class="fas fa-circle"></i>{{ $item }}</span>
    @endforeach
    @foreach ($items as $item)
      <span><i class="fas fa-circle"></i>{{ $item }}</span>
    @endforeach
  </div>
  <div class="cc-marquee-fade cc-marquee-fade--right"></div>
</div>
