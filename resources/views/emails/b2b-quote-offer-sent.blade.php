<h1>Oferta {{ $quoteRequest->reference }}</h1>
<p>Przygotowaliśmy indywidualną ofertę dla {{ $quoteRequest->company_name }}.</p>
<p>Kwota brutto: <strong>{{ number_format((float) $offer->total, 2, ',', ' ') }} {{ $offer->currency }}</strong></p>
<p>Oferta jest ważna do {{ $offer->valid_until->format('d.m.Y') }}.</p>
@if($offer->notes)<p>{{ $offer->notes }}</p>@endif
<p><a href="{{ route('quote-offers.show', ['token' => $token]) }}">Sprawdź ofertę i zaakceptuj</a></p>
