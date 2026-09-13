@props(['items' => []])

<div class="cc-marquee" aria-hidden="true">
  <div class="cc-marquee-fade cc-marquee-fade--left"></div>
  <div class="cc-marquee-track">
    @foreach ([1, 2] as $group)
      <div class="cc-marquee-group">
        @foreach ([1, 2] as $repeat)
          @foreach ($items as $item)
            <span><i class="fas fa-circle"></i>{{ $item }}</span>
          @endforeach
        @endforeach
      </div>
    @endforeach
  </div>
  <div class="cc-marquee-fade cc-marquee-fade--right"></div>
</div>
