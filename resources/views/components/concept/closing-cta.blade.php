@props(['heading' => 'Wybierz ścieżkę.', 'items' => [], 'subheading' => 'Wybierz, co drukujemy.'])

<section class="cc-closing">
  <div class="cc-container">
    <p class="cc-section-label cc-section-label--light reveal">Ostatni krok</p>
    <h2 class="cc-closing-title reveal">{{ $heading }}<br><span>{{ $subheading }}</span></h2>
    <div class="cc-closing-actions">
      @foreach ($items as $item)
        <a href="{{ $item['href'] }}" class="cc-closing-action reveal" style="transition-delay: {{ $loop->index * 80 }}ms">
          <span class="cc-closing-action-icon"><i class="fas {{ $item['icon'] ?? 'fa-arrow-right' }}" aria-hidden="true"></i></span>
          <span class="cc-closing-action-text">
            <strong>{{ $item['title'] }}</strong>
            <small>{{ $item['note'] ?? '' }}</small>
          </span>
          <i class="fas fa-arrow-right cc-closing-action-arrow" aria-hidden="true"></i>
        </a>
      @endforeach
    </div>
    <p class="cc-closing-note">Każda ścieżka to osobny konfigurator.</p>
  </div>
</section>
