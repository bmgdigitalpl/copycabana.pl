@props(['items' => []])

<div class="faq-list cc-faq">
  @foreach ($items as $item)
    <details {{ $loop->first ? 'open' : '' }}>
      <summary>{{ $item['q'] }}</summary>
      <p>{{ $item['a'] }}</p>
    </details>
  @endforeach
</div>
