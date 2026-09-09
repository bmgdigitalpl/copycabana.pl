@props(['icon' => 'fa-file-lines', 'accent' => 'magenta', 'title' => '', 'href' => '#', 'cta' => 'Sprawdź'])

<article class="cc-need-card cc-need-card--{{ $accent }} reveal">
  <span class="cc-need-icon"><i class="fas {{ $icon }}"></i></span>
  <div class="cc-need-body">
    <h3>{{ $title }}</h3>
    <p>{{ $slot }}</p>
    <a href="{{ $href }}" class="cc-need-cta">
      <span>{{ $cta }}</span>
      <i class="fas fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
</article>
