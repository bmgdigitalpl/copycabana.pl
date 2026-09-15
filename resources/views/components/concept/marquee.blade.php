@props(['items' => []])

<div class="cc-marquee" aria-label="Szybki wybór usług">
  <div class="cc-marquee-fade cc-marquee-fade--left"></div>
  <div class="cc-marquee-track">
    @foreach ([1, 2] as $group)
      <div class="cc-marquee-group">
        @foreach ([1, 2] as $repeat)
          @foreach ($items as $item)
            @php
              $label = is_array($item) ? $item['label'] : $item;
              $href = is_array($item) ? ($item['href'] ?? null) : null;
            @endphp
            @if ($href)
              <a href="{{ $href }}"><i class="fas fa-circle" aria-hidden="true"></i>{{ $label }}</a>
            @else
              <span><i class="fas fa-circle" aria-hidden="true"></i>{{ $label }}</span>
            @endif
          @endforeach
        @endforeach
      </div>
    @endforeach
  </div>
  <div class="cc-marquee-fade cc-marquee-fade--right"></div>
</div>
